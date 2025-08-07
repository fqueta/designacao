<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ApostilaController extends Controller
{
    public function extract($meses='setembro-outubro',$ano='2025')
    {
        $client = new Client([
            'base_uri' => 'https://www.jw.org',
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; LaravelBot/1.0)',
            ],
            'version' => 1.1,
        ]);

        $response = $client->get('/pt/biblioteca/jw-apostila-do-mes/'.$meses.'-'.$ano.'-mwb/');
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);

        $resultados = $crawler->filter('div.synopsis .syn-body a')->each(function (Crawler $node) {
            if($node->attr('href')){
                return [
                    'nome' => trim($node->text()),
                    'link' => $node->attr('href'),
                    'link_completo' => $node->link()->getUri(),
                ];
            }
        });

        return response()->json($resultados);
    }
    /**
     * Metodo para salvar os links extraidos das apostilas
     * @param $post_id = id da desigação bimestral
     */
    public function save_stract_link($post_id){
        $dd = $this->extract();
        // $json = Qlib::lib_array_json($dd);

        if($dados = $dd->getData()){
            $sav = Qlib::update_postmeta($post_id,'links_apostila',Qlib::lib_array_json($dados));
        }
        return $sav;
    }
    /**
     * Metodo para recuperar o links armazendos duranta a execução do metodo extract
     *
     */
    public function get_links($post_id){
        $link = Qlib::get_postmeta($post_id,'links_apostila',true);
        if($link){
            $link = Qlib::lib_json_array($link);
        }
        return $link;
    }
    /**
     * Metodos para extrair o programa de uma semana expessifica
     *
     */
    public function extract_programa($link='',$link_uri='https://www.jw.org'){
        $ret = ['exec'=>false];
        if(!$link){
            return $ret;
        }
        $client = new Client([
            'base_uri' => $link_uri,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; LaravelBot/1.0)',
            ],
            'version' => 1.1,
        ]);

        $response = $client->get($link);
        $html = (string) $response->getBody();
        // dd($html);
        $arr_partes = [
            'tesouros'=>['seletor'=>'h3.du-fontSize--base.du-color--teal-700'],
            'ministerio'=>['seletor'=>'h3.du-fontSize--base.du-color--gold-700'],
            'vida'=>['seletor'=>'h3.du-fontSize--base.du-color--maroon-600'],
        ];
        $crawler = new Crawler($html);
        $ret['total'] = 0;
        foreach ($arr_partes as $kp => $vp) {
            $pt = $this->partes_desiganacao($crawler,$vp['seletor'],$kp);
            if(isset($pt['sec'])){
                $ret['partes'][$kp] = $pt['partes'];
            }
            if(isset($pt['total'])){
                $ret['total'] += $pt['total'];
            }
        }
        return $ret;
    }
    /**
     * Metodo para exibir as partes
     *
     */
    public function partes_desiganacao($crawler,$seletor,$sessaoP=false){
        $ret['exec'] = false;
        $ret['sec'] = $sessaoP;
        // $ret = [];
        // $crawler = new Crawler($html);
        //campo obs e tempo não teremos por equanto
        $resultados = $crawler->filter($seletor)->each(function (Crawler $node) {
            $tema = trim($node->text());
            $idp = $this->descobreIdParte($tema);
            $rt['tema'] = $this->get_partes_tema($tema,'texto');
            $rt['numero'] = $this->get_partes_tema($tema,'numero');
            $rt['id_designacao'] = $idp;
            $rt['obs'] = $rt['tema'];
            return $rt;
        });
        $ret['total'] = (int)count($resultados);
        if($sessaoP=='tesouros'){
            if(isset($resultados[0]['id_designacao']) && $resultados[0]['id_designacao']==0){
                $resultados[0]['id_designacao'] = 4;
            }
        }elseif($sessaoP=='ministerio' || $sessaoP=='vida'){
            foreach ($resultados as $k => $v) {
                if($v['id_designacao']==0){
                    $resultados[$k]['id_designacao'] = 8;

                }
            }
        }
        $ret['partes'] = $resultados;

        return $ret;
    }
    /**
     * Metodo para exibir elementos dos tema
     *
     */
    public function get_partes_tema($tema,$parte){
        if($tema && $parte){
            $el_t = explode(".",$tema);
            $ret = false;
            if($parte=='numero'){
                return (int)$el_t[0];
            }else if($parte=='texto'){
                //remober o numero do tema
                if(isset($el_t[1]) && !empty($el_t[1])){
                   $ret = trim(substr($tema, 2));
                }
            }
            return $ret;
        }
    }
    /**
     * Metodo para descobrir qual é o Id de uma parte
     */
    public function descobreIdParte($text){
        $idp=0;
        if($text){
            $texto = trim(@explode('.',$text)[1]);
            if($texto){
                $dp = Tag::where('nome','=',$texto)->get();
                if($dp->count() > 0){
                    $idp = $dp[0]['id'];
                }
            }
        }
        return $idp;
    }
}
