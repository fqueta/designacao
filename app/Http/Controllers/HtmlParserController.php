<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class HtmlParserController extends Controller
{
    public function parse()
    {
        $url = 'https://www.jw.org/pt/biblioteca/jw-apostila-do-mes/setembro-outubro-2025-mwb/';

        try {
            // 1. Requisição com user-agent customizado e forçando HTTP/1.1
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get($url);

            if ($response->failed()) {
                return response()->json(['error' => 'Falha ao acessar o site'], 500);
            }

            $html = (string) $response->body();

            // 2. Parse com DomCrawler
            $crawler = new Crawler($html);

            // 3. Exemplo: pegar o título da página
            $title = $crawler->filter('title')->text();

            // 4. Exemplo: pegar os links da página
            $links = $crawler->filter('a')->each(function (Crawler $node) {
                return $node->attr('href');
            });

            return response()->json([
                'title' => $title,
                'links' => $links,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
