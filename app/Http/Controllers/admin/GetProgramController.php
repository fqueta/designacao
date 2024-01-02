<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Qlib\Qlib;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

class GetProgramController extends Controller
{
    public function fileGet($config = null)
    {
        $ret = false;
        $dia = isset($config['dia']) ? $config['dia'] : date('d');
        $mes = isset($config['mes']) ? $config['mes'] : date('m');
        $ano = isset($config['ano']) ? $config['ano'] : date('Y');
        $semana = Qlib::semana_do_ano($dia,$mes,$ano);
        $semana = Qlib::zerofill($semana,2);
        $link = 'https://wol.jw.org/pt/wol/meetings/r5/lp-t/'.$ano.'/'.$semana;
        $prog_html = file_get_contents($link);
        if($prog_html){
            $prog_html;
            $dom = new domDocument();
            @$dom->loadHTML((string)$prog_html);
            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query('//div[@class="todayItems"]');
            foreach ($nodes as $node) {
                $div1  = $xpath->query('div', $node)->item(0);
                $div2  = $xpath->query('div', $div1)->item(0);
                // $email = $xpath->query('div/div[@class="c the_email"]', $node)->item(0);
dd($div2);
                // echo $nome->nodeValue  . PHP_EOL;
                // echo $email->nodeValue . PHP_EOL;
            }
            //$li1 = $div->item(0);
            // $li2 = $li1->query('//div[@class="linkCard"]');
            // $tables = $xpath->query("//table[@class=\"table-general\"]");
            // $values = $xpath->query(".//tbody/tr", $tables->item(0));
            // $nodes = $xpath->query('//div[@class="todayItems"]');
            dd($li1);
            $ret = $div;

        }
        return $ret;
    }
}
