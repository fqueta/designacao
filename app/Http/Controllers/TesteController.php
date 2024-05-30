<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\CobrancaController;
use App\Http\Controllers\admin\designaController;
use App\Http\Controllers\admin\GetProgramController;
use App\Models\Familia;
use App\Models\User;
use App\Qlib\Qlib;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class TesteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // $stop_date = '2022-12-30 20:24:00';
        // echo 'date before day adding: ' . $stop_date;
        // $stop_date = date('Y-m-d H:i:s', strtotime($stop_date . ' +1 week'));
        // echo 'date after adding 1 day: ' . $stop_date;
        // PHP program to print default first
        // day of current week

        // l will display the name of the day

        // d, m, Y will display the day, month
        // and year respectively
        // $firstday = date('l - d/m/Y', strtotime("this week"));

        // echo "First day of this week: ", $firstday;
        // PHP program to display sunday as first day of a week

        // l will display the name of the day
        // d, m and Y will display the day, month and year respectively

        // For current week the time-string will be "sunday -1 week"
        // here -1 denotes last week
        // $dt = strtotime('2023-01-01');
        // $firstday = date('l - d/m/Y', strtotime("$dt+monday 0 week"));
        // echo "First day of this week: ", $firstday, "<br>";
        // exit();

        // $firstday = date('l - d/m/Y', strtotime("monday 0 week"));
        // echo "First day of this week: ", $firstday, "<br>";

        // $firstday = date('l - d/m/Y', strtotime("monday +1 week"));
        // echo "First day of this week: ", $firstday, "<br>";

        // // For previous week the time-string will be "monday -2 week"
        // // here -2 denotes week before last week
        // $firstday = date('l - d/m/Y', strtotime("monday +2 week"));
        // echo "First day of last week: ", $firstday, "<br>";

        // // For next week the time-string will be "monday 0 week"
        // // here 0 denotes this week
        // $firstday = date('l - d/m/Y', strtotime("monday +3 week"));
        // echo "First day of next week: ", $firstday, "<br>";

        // // For week after next week the time-string will be "monday 1 week"
        // // here 1 denotes next week
        // $firstday = date('l - d/m/Y', strtotime("monday +4 week"));
        // echo "First day of week after next week : ", $firstday;

        // $d=(new designaController)->save(5);

            // $host = request()->getHttpHost();
        // echo $host ."<br/>";
        // $getHost = request()->getHost();
        // echo $getHost ."<br/>";
        // $hostwithHttp = request()->getSchemeAndHttpHost();
        // echo $hostwithHttp ."<br/>";
        // $subdomain = $route->getParameter('subdomain');
        //dd($route);
        // return view('teste',$config);
        // $days = Qlib::getAllDaysInAMonth(2023, 01);

        // foreach ($days as $day) {
        //     echo $day->format('D Y-m-d').'<br />';
        // }

        // $parOuImpar  = array(2,3,4,56,5,42,98,100);

        // $ch = Qlib::getNumberRange(1,12);

        // $array  = array_map("ch", $parOuImpar);

        // $arr_month = Qlib::arr_month(date('Y'));
        // $ret = (new GetProgramController)->fileGet();
        // $ret = Qlib::getAllDaysInAMonth(2024,3);
        // Qlib::lib_print($ret);
        // $ret = (new designaController)->arr_historico(2,2,true);
        // $ret = (new designaController)->list_participants(2,2);
        // $ret = (new designaController)->arr_historico([
        //     'id_designacao'=>2,
        //     'id_designado'=>95,
        //     'ultima'=>true,
        //     'type'=>'id_designado',
        //     'post_type'=>'meio-semana',
        //     'limit'=>4,
        //     'operador'=>'!=',
        // ]);
        // $ret = Qlib::getSundays(2024,02);
        // $ret = (new designaController)->add_designacao('2024-05-13');
        // dd($ret);

        return $ret;
    }
    public function getNumberRange($inic,$fim,$r='impar'){
        $ret = array();
        if ($inic && $fim && $r) {
            foreach(range($inic,$fim) as $v){
                if($v % 2) {
                    if($r=='impar'){
                        $ret[] = $v;
                    }
                } else {
                    if($r=='par'){
                        $ret[] = $v;
                    }
                }
            }
        }
        return $ret;
    }


    // public function arrMon

    public function getLastWeekDates()
    {
        $lastWeek = array();

        $prevMon = abs(strtotime("previous monday"));
        $currentDate = abs(strtotime("today"));
        $seconds = 86400; //86400 seconds in a day

        $dayDiff = ceil( ($currentDate-$prevMon)/$seconds );

        if( $dayDiff < 7 )
        {
            $dayDiff += 1; //if it's monday the difference will be 0, thus add 1 to it
            $prevMon = strtotime( "previous monday", strtotime("-$dayDiff day") );
        }

        $prevMon = date("Y-m-d",$prevMon);

        // create the dates from Monday to Sunday
        for($i=0; $i<7; $i++)
        {
            $d = date("Y-m-d", strtotime( $prevMon." + $i day") );
            $lastWeek[]=$d;
        }

        return $lastWeek;
    }
    public function ajax(){
        $limit = isset($_GET['limit']) ?$_GET['limit'] : 50;
        $page = isset($_GET['page']) ?$_GET['page'] : 1;
        $site=false;

        $urlApi = $site?$site: 'https://po.presidenteolegario.mg.gov.br';
        $link = $urlApi.'/api/diaries?page='.$page.'&limit='.$limit;
        $link_html = dirname(__FILE__).'/html/front.html';
        $dir_img = $urlApi.'/uploads/posts/image_previews/{id}/thumbnail/{image_preview_file_name}';
        $dir_file = $urlApi.'/uploads/diaries/files/{id}/original/{file_file_name}';

        //$arquivo = $this->carregaArquivo($link_html);
        //$temaHTML = explode('<!--separa--->',$arquivo);
        $api = file_get_contents($link);
        $arr_api = Qlib::lib_json_array($api);
        /*
        $tema1 = '<ul id="conteudo" class="list-group">{tr}</ul>';
        $tema2 = '<li class="list-group-item" itemprop="headline"><a href="{link_file}" target="_blank">{file_file_name} – {date}</a></li>';
        $tr=false;
        if(isset($arr_api['data']) && !empty($arr_api['data'])){
          foreach ($arr_api['data'] as $key => $value) {
              $link = false;
              $link_file = str_replace('{id}',$value['id'],$dir_file);
              $link_file = str_replace('{file_file_name}',$value['file_file_name'],$link_file);


              $conteudoPost = isset($value['content'])?:false;
              $date = false;
              $time = false;
              $datetime = str_replace(' ','T',$value['date']);
              $d = explode(' ',$value['date']);

              if(isset($d[0])){
                $date = Qlib::dataExibe($d[0]);
              }
              if(isset($d[1])){
                $time = $d[1];
              }
              $file_name = str_replace('.pdf','',$value['file_file_name']);
              $file_name = str_replace('.PDF','',$file_name);
              $tr .= str_replace('{file_file_name}',$file_name,$tema2);
              $tr = str_replace('{link}',$link,$tr);
              $tr = str_replace('{link_file}',$link_file,$tr);
              $tr = str_replace('{time}',$time,$tr);
              $tr = str_replace('{date}',$date,$tr);
              $tr = str_replace('{description}',$value['description'],$tr);
              $tr = str_replace('{datetime}',$datetime,$tr);
          }
        }
        $link_veja_mais = '/diario-oficial';
        $ret = str_replace('{tr}',$tr,$tema1);
        //$ret = str_replace('{id_sec}',$id_sec,$ret);
        $ret = str_replace('{link_veja_mais}',$link_veja_mais,$ret);
        */
        return response()->json($arr_api);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
