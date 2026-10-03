<?php

namespace App\Services;

use App\Models\LinkSemana;
use App\Qlib\Qlib;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Resolve o link WOL de uma semana, gravando em links_semanas de forma automática.
 *
 * Estratégia:
 *  1. Se já existe link /d/ em links_semanas para a data, usa ele (manual tem prioridade;
 *     links /meetings/ antigos são ignorados e regravados).
 *  2. Se a data é passada, não tenta descobrir (retorna o que houver no banco).
 *  3. Se é hoje/futura, descobre o link do artigo /d/r5/lp-t/{docId} raspando a página
 *     da apostila mensal na biblioteca WOL, valida e salva via updateOrCreate.
 *     O formato /meetings/{ano}/{semana} NÃO é usado: ele abre uma página de índice
 *     que o VmpController não consegue extrair partes.
 */
class LinkSemanaResolver
{
    public static function resolve(string $data): ?string
    {
        $data = trim($data);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return null;
        }

        // 1) Banco primeiro — só links /d/ valem como cache (manual tem prioridade)
        try {
            $saved = LinkSemana::where('data', $data)->value('link');
        } catch (\Throwable $e) {
            $saved = Qlib::buscaValorDb0('links_semanas', 'data', $data, 'link');
        }
        if (!empty($saved) && str_contains($saved, '/wol/d/')) {
            return $saved;
        }

        // 2) Só automatiza hoje/futuras
        if ($data < date('Y-m-d')) {
            return (!empty($saved) ? $saved : null) ?: (Qlib::link_programacao_woljw($data) ?: null);
        }

        // 3) Descobre o /d/ na biblioteca WOL + valida + salva
        $found = self::discoverViaLibrary($data);
        if ($found && self::validate($found)) {
            try {
                LinkSemana::updateOrCreate(['data' => $data], ['link' => $found]);
            } catch (\Throwable $e) {
                Log::warning('LinkSemanaResolver: falha ao salvar link automático', [
                    'data' => $data, 'link' => $found, 'erro' => $e->getMessage(),
                ]);
            }
            return $found;
        }

        if (!$found) {
            Log::warning('LinkSemanaResolver: semana não encontrada na biblioteca WOL', ['data' => $data]);
        }

        return null;
    }

    /**
     * Raspa a página mensal da apostila na biblioteca WOL e retorna o link /d/ da semana.
     * Tenta o mês da data e, se não achar, o mês anterior (ex.: semanas de dezembro
     * listadas também na página de novembro).
     */
    public static function discoverViaLibrary(string $data): ?string
    {
        $ts = strtotime($data);
        if (!$ts) {
            return null;
        }
        $ano = (int) date('Y', $ts);
        $mes = (int) date('n', $ts);

        $tentativas = [[$ano, $mes]];
        $prevTs = strtotime('-1 month', $ts);
        $tentativas[] = [(int) date('Y', $prevTs), (int) date('n', $prevTs)];

        foreach ($tentativas as [$a, $m]) {
            $link = self::findInMonthPage($data, $a, $m);
            if ($link) {
                return $link;
            }
            sleep(1);
        }

        return null;
    }

    protected static function findInMonthPage(string $data, int $ano, int $mes): ?string
    {
        $slug = self::monthSlug($mes);
        if (!$slug) {
            return null;
        }
        $url = "https://wol.jw.org/pt/wol/library/r5/lp-t/todas-as-publicações/apostilas/apostila-vida-e-ministério-{$ano}/{$slug}";

        try {
            $response = self::http()->get($url);
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $crawler = new Crawler((string) $response->getBody(), $url);

            $achados = $crawler->filter('a')->each(function (Crawler $node) {
                $href = $node->attr('href');
                if (!$href || !preg_match('#^/pt/wol/d/r5/lp-t/\d+$#', $href)) {
                    return null;
                }
                $texto = trim($node->text());
                // Ignora a capa da apostila (link /d/ do mês inteiro)
                if ($texto === '' || stripos($texto, 'apostila') !== false) {
                    return null;
                }
                return ['href' => $href, 'texto' => $texto];
            });

            foreach (array_filter($achados) as $item) {
                $segunda = self::parseWeekText($item['texto'], $ano, $mes);
                if ($segunda === $data) {
                    return 'https://wol.jw.org' . $item['href'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('LinkSemanaResolver: erro ao raspar biblioteca WOL', [
                'url' => $url, 'erro' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Converte "2-8 de novembro" / "30 de novembro–6 de dezembro" /
     * "28 de dezembro de 2026–3 de janeiro de 2027" na segunda-feira Y-m-d.
     * A primeira data do intervalo é sempre a segunda da semana.
     */
    public static function parseWeekText(string $texto, int $anoRef, int $mesRef): ?string
    {
        $t = mb_strtolower(trim($texto), 'UTF-8');
        $t = str_replace(['–', '—', '−'], '-', $t);
        $t = (string) preg_replace('/\s+/', ' ', $t);

        if (!preg_match('/^(\d{1,2})\s*(?:[-–—]\s*\d{1,2})?\s+de\s+([a-zãõçéêíû]+)(?:\s+de\s+(\d{4}))?/u', $t, $m)) {
            return null;
        }
        $dia = (int) $m[1];
        $mesNum = self::monthNumber($m[2]);
        if (!$mesNum) {
            return null;
        }
        $ano = isset($m[3]) && $m[3] !== '' ? (int) $m[3] : $anoRef;
        // Ex.: data de janeiro listada como "29 de dezembro–4 de janeiro" (sem ano explícito)
        if ((!isset($m[3]) || $m[3] === '') && $mesNum >= 11 && $mesRef <= 2) {
            $ano = $anoRef - 1;
        }

        if (!checkdate($mesNum, $dia, $ano)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $ano, $mesNum, $dia);
    }

    public static function monthSlug(int $mes): ?string
    {
        return [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ][$mes] ?? null;
    }

    protected static function monthNumber(string $nome): ?int
    {
        $map = [
            'janeiro' => 1, 'fevereiro' => 2, 'marco' => 3, 'março' => 3, 'abril' => 4,
            'maio' => 5, 'junho' => 6, 'julho' => 7, 'agosto' => 8,
            'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12,
        ];
        $nome = trim($nome);
        return $map[$nome] ?? null;
    }

    protected static function http(): Client
    {
        return new Client([
            'timeout' => 20,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
                'Accept-Language' => 'pt-BR,pt;q=0.9',
            ],
            'allow_redirects' => true,
            'http_errors' => false,
        ]);
    }

    /**
     * Valida que a URL realmente abre a página do artigo da semana na WOL.
     */
    protected static function validate(string $url): bool
    {
        try {
            $response = self::http()->get($url);
            if ($response->getStatusCode() !== 200) {
                return false;
            }
            $html = mb_strtolower((string) $response->getBody(), 'UTF-8');
            // Marca típica da página do artigo semanal (vale para /d/).
            foreach (['vida e minist', 'tesouros', 'nossa vida', 'apostila'] as $mark) {
                if (str_contains($html, $mark)) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::warning('LinkSemanaResolver: erro HTTP ao validar WOL', [
                'url' => $url, 'erro' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
