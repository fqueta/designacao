<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use GuzzleHttp\Client;
class VmpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Assuming you installed from Composer:


        if($request->has('link')){
            $link = $request->get('link');
        }else{
            $link = 'https://wol.jw.org/pt/wol/meetings/r5/lp-t/2024/19';
        }
        $ret = $this->gera_api($link);
        return response()->json($ret);
    }
    /**
     * Metodo para gerar a api
     * @param string $link
     * @return array $ret
     */
    public function gera_api($link,$data=false){
        $dom = new Dom;
        $dom->loadFromUrl($link);
        // $html = $dom->outerHtml;
        dd($link,$dom);
        $ret['exec'] = false;
        if($dom){
            $ret['exec'] = true;
        }
        $ret['semana'] = $dom->find('#p1')->text;
        $ret['total'] = 0;
        $link_semana_atual = $dom->find('#navigationDailyTextToday a')->getAttribute('href');
        $ret['link_semana_atual'] = $link_semana_atual;
        $ret['data_programa'] = $data;
        $arr_partes = [
            'tesouros'=>['seletor'=>'h3.du-color--teal-700'],
            'ministerio'=>['seletor'=>'h3.du-color--gold-700'],
            'vida'=>['seletor'=>'h3.du-color--maroon-600'],
        ];
        foreach ($arr_partes as $kp => $vp) {
            $pt = $this->partes_desiganacao($dom,$vp['seletor'],$kp);
            if(isset($pt['sec'])){
                $ret['partes'][$kp] = $pt['sec'];
            }
            if(isset($pt['total'])){
                $ret['total'] += $pt['total'];
            }
        }
        return $ret;
    }
    /**
     * Metodo para retornar o proximo elemeno de acomdo com o dom e o paragrado informado
     */
    public function proximo_elemento_tempo($dom,$pid=false,$type='text'){
        $np = (int)str_replace('p', '',$pid);
        $nidp = ($np+1);
        $nidp = '#p'.$nidp;
        if($type=='html'){
            $tempo = trim(str_replace('()','',$dom->find($nidp)->innerHtml));
        }else{
            $tempo = strip_tags(trim(str_replace('()','',$dom->find($nidp)->innerHtml)));
        }
        return $tempo;
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
                    // $ret = trim(str_replace($el_t[0].'.','',$tema));
                    $ret = trim(substr($tema, 2));
                }
            }
            return $ret;
        }
    }
    /**
     * Metodo para exibir as partes
     *
     */
    public function partes_desiganacao($dom,$seletor,$sessaoP=false){
        $ret['exec'] = false;
        $ret['total'] = 0;
        // $ret = [];
        if($dom && $seletor && $sessaoP){
            $sec_sessao = $dom->find($seletor);
            if(is_object($sec_sessao)){
                // dd($sec_sessao);
                $ret['exec'] = true;
                foreach ($sec_sessao as $key => $content) {
                    if($sessaoP=='vida'){
                        $tema = $content->find('strong')->innerHtml;
                    }else{
                        $tema = $content->find('strong')->text;
                    }
                    $ret['sec'][$key]['tema'] = $this->get_partes_tema($tema,'texto');
                    $ret['sec'][$key]['numero'] = $this->get_partes_tema($tema,'numero');
                    $idp = $this->descobreIdParte($tema);
                    $temaP = $ret['sec'][$key]['tema'];
                    if($idp==0){
                        if($sessaoP=='tesouros'){
                            $idp = 4;
                        }elseif($sessaoP=='ministerio' || $sessaoP=='vida'){
                            $idp = 8;
                        }
                    }
                    if($sessaoP=='ministerio' || $sessaoP=='vida' && ($idp!=8)){
                        $temaP = false;
                    }

                    $ret['total']++;
                    $ret['sec'][$key]['id_designacao'] = $idp;
                    $pid = $content->getAttribute('id');
                    $ret['sec'][$key]['tempo'] = $this->proximo_elemento_tempo($dom,$pid);
                    $ret['sec'][$key]['obs'] = $temaP.' '.$ret['sec'][$key]['tempo'];
                }
            }
        }
        if($sessaoP=='tesouros' && isset($ret['sec']) && is_array($ret['sec'])){
            //na api a ordem dos tesous está invertida
            foreach ($ret['sec'] as $key => $value) {
                if(isset($value['numero'])){
                    $ret['sec'][$value['numero']-1] = $value;
                }
            };
        }
        return $ret;
    }
}
