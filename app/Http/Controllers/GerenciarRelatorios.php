<?php

namespace App\Http\Controllers;

use App\Models\grupo;
use App\Models\Publicador;
use App\Models\relatorio;
use App\Models\User;
use App\Models\usuario;
use App\Qlib\Qlib;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
class GerenciarRelatorios extends Controller
{
    protected $user;
    public function __construct(User $user)
    {
        $this->middleware('auth');
        $this->user = $user;
    }
    public function index()
    {
        $relatorios = grupo::all();
        $title = 'Todos os relatorios';
        $titulo = $title;
        return view('relatorios.index',['relatorios'=>$relatorios,'title'=>$title,'titulo'=>$titulo]);
    }
    public function create($id=false)
    {
        $meses = Qlib::Meses();
        $title = 'RELATÓRIO DE SERVIÇO DE CAMPO';
        $titulo = $title;
        $mes = isset($_GET['m'])?$_GET['m']:(date('m')-1);
        if($mes==0){
          $mes = 12;
        }
        $mesExt = $meses[Qlib::zerofill($mes,2)];
        $ano = isset($_GET['ano'])?$_GET['ano']:date('Y');
        $dadosPub = Publicador::find($id);
        $dados = [
          ['type'=>'hidden','campo'=>'id_publicador','label'=>'id_publicador','valor'=>$id],
          ['type'=>'hidden','campo'=>'mes','label'=>'Mes','valor'=>$mes],
          ['type'=>'hidden','campo'=>'ano','label'=>'ano','valor'=>$ano],
          ['type'=>'hidden','campo'=>'id_grupo','label'=>'id grupo','valor'=>$dadosPub['id_grupo']],
          ['type'=>'number','campo'=>'publicacao','label'=>'Publicações(Impressas e eletrônicas)','valor'=>''],
          ['type'=>'number','campo'=>'video','label'=>'Videos mostrados','valor'=>''],
          ['type'=>'tel','campo'=>'hora','label'=>'Horas','valor'=>''],
          ['type'=>'number','campo'=>'revisita','label'=>'Revisitas','valor'=>''],
          ['type'=>'number','campo'=>'estudo','label'=>'Estudos biblícos','valor'=>''],
          ['type'=>'text','campo'=>'obs','label'=>'Observações','valor'=>''],
        ];
        $relatorio_cad = relatorio::where('id_publicador','=',$id)->where('mes','=',$mes)->where('ano','=',$ano)->get();
        if($dsal = $relatorio_cad->all()){
            foreach ($dados as $key => $value) {
              if(isset($dsal[0][$value['campo']])){
                $dados[$key]['valor'] = $dsal[0][$value['campo']];
              }
            }
            if(isset($dsal[0]['id'])){
              array_push($dados,['type'=>'hidden','campo'=>'id','label'=>'id','valor'=>$dsal[0]['id']]);
            }
        }
        //dd($dados);
        return view('relatorios.create',['dados'=>$dados,'dadosPub'=>$dadosPub,'mesExt'=>$mesExt,'mes'=>$mes,'ano'=>$ano,'title'=>$title,'titulo'=>$titulo]);
    }
    public function store(Request $request,User $user)
    {
        //return redirect()->route('relatorios-index');
        $dados = $request->all();
        $ac = 'cad';
        $relatorios_gravados = 0;
        if(isset($dados['id_publicador']) && isset($dados['mes']) && isset($dados['ano'])){
          $ac = 'alt';
          $relatorios_gravados = relatorio::where('id_publicador','=',$dados['id_publicador'])->where('mes','=',$dados['mes'])->where('ano','=',$dados['ano'])->count();
          //dd($relatorios_gravados);
        }
        //$dados['enviado_por'] = '{"user_id":"4","nome":"Waldir Bertges","ip":"177.104.65.201"}';
        $arr_obs = ['p'=>'','pa'=>'Pioneiro Auxiliar','pr'=>'Pioneiro Regular'];
        if(isset($dados['id_publicador'])){
          $dadosPub = DB::select("SELECT pioneiro FROM publicadores WHERE id='".$dados['id_publicador']."'");
          if($dadosPub){
            if(empty($dadosPub[0]->pioneiro)){
              $privilegio = 'p';
            }else{
              $privilegio = trim($dadosPub[0]->pioneiro);
            }
          }else{
            $privilegio = 'p';
          }
        }
        //dd($dadosPub);
        $dados['privilegio'] = $privilegio;
        if($privilegio!='p'){
          $dados['obs'] = $arr_obs[$privilegio].' '.$dados['obs'];
        }
        if($relatorios_gravados==0){
          $salvarRelatorios = relatorio::create($dados);
        }else{
          $salvarRelatorios = $this->update($request,$user);
        }

        $ret['exec'] = false;
        if($salvarRelatorios){
          $GerenciarUsuarios = new GerenciarUsuarios($user);
          $ret['exec'] = true;
          $ret['salvarRelatorios'] = $salvarRelatorios;
          //dd($salvarRelatorios);
          if(isset($salvarRelatorios['id_publicador'])){
            $rb = new RoboController;
            $ret['lerRelatorios'] = $rb->lerRelatorios($salvarRelatorios['id_publicador']);
          }
          $ret['mens'] = 'Registro gravado com sucesso!';
          $ret['cartao']=$GerenciarUsuarios->cardData($dados['id_publicador']);
        }else{
          $ret['mens'] = 'Erro ao gravar!';
        }

        if(isset($_GET['redirect'])){
          $ret['redirect'] = $_GET['redirect'];
        }
        return json_encode($ret);
        //echo json_encode($dados);
    }
    public function edit($id)
    {
        $relatorios = grupo::where('id',$id)->first();
        if(!empty($relatorios)){
          $title = 'Editar um grupo';
          $titulo = $title;
          return view('relatorios.edit',['relatorios'=>$relatorios,'title'=>$title,'titulo'=>$titulo]);
        }else{
          return redirect()->route('relatorios-index');
        }
    }
    public function update(Request $request,User $user,$id=false){
      //if($request)
      $data = [];
      foreach ($request->all() as $key => $value) {
        if($key!='var_cartao'&&$key!='ac'){
          $data[$key] = $value;
        }
      }
      $arr_obs = ['p'=>'','pa'=>'Pioneiro Auxiliar','pr'=>'Pioneiro Regular'];
      if(isset($data['id_publicador'])){
        $dadosPub = DB::select("SELECT pioneiro FROM publicadores WHERE id='".$data['id_publicador']."'");
        if($dadosPub){
          if(empty($dadosPub[0]->pioneiro)){
            $privilegio = 'p';
          }else{
            $privilegio = trim($dadosPub[0]->pioneiro);
          }
        }else{
          $privilegio = 'p';
        }
      }
      $data['privilegio'] = $privilegio;
      if($privilegio=='p'){
        $data['obs'] = str_replace($arr_obs['pa'],'',$data['obs']);
        $data['obs'] = str_replace($arr_obs['pr'],'',$data['obs']);
      }else{
        $data['obs'] = str_replace($arr_obs[$privilegio],'',$data['obs']);
        $data['obs'] = $arr_obs[$privilegio].' '.$data['obs'];

      }
      $salvarRelatorios=false;
      //dd($data);
      unset($data['_token']);
      if(!empty($data) && isset($data['id'])){
        $salvarRelatorios=relatorio::where('id',$data['id'])->update($data);
      }
      if($salvarRelatorios){
        $GerenciarUsuarios = new GerenciarUsuarios($user);
        $ret['exec'] = true;
        $ret['salvarRelatorios'] = $data;
        $ret['mens'] = 'Registro gravado com sucesso!';
        $ret['cartao']=$GerenciarUsuarios->cardData($data['id_publicador']);
        if(isset($data['id_publicador'])){
            $rb = new RoboController;
            $ret['lerRelatorios'] = $rb->lerRelatorios($data['id_publicador']);
        }
      }else{
        $ret['exec'] = false;
        $ret['mens'] = 'Erro ao gravar!';
      }
      if(isset($_GET['redirect'])){
        $ret['redirect'] = $_GET['redirect'];
      }
      return json_encode($ret);
        /*
        $data = [
           'grupo'=>$request->grupo,
           'obs'=>$request->obs,
           'ativo'=>$request->ativo
        ];
        grupo::where('id',$id)->update($data);
        return redirect()->route('relatorios-index');
        */
    }
    public function destroy(request $request,User $user)
    {

      $deletar = false;
      $dados = $request->all();
      if(isset($dados['id_publicador']) && isset($dados['mes']) && isset($dados['ano'])){
          $deletar = relatorio::where('id_publicador','=',$dados['id_publicador'])->where('mes','=',$dados['mes'])->where('ano','=',$dados['ano'])->delete();
      }
      if($deletar){
        $GerenciarUsuarios = new GerenciarUsuarios($user);
        $ret['exec'] = true;
        $ret['mens'] = 'Excluido com sucesso!';
        $ret['cartao']=$GerenciarUsuarios->cardData($dados['id_publicador']);
        $ret['salvarRelatorios'] = $dados;
      }else{
        $ret['exec'] = false;
        $ret['mens'] = 'Erro ao gravar entre em contato com o suporte!';
      }
      return json_encode($ret);
        //return redirect()->route('relatorios-index');
    }
    public function verificarRelatorioMensal($config=false)
    {
        $ret = false;
        $id_publicador = isset($config['id_publicador'])?$config['id_publicador']:false;
        $tipo = isset($config['tipo'])?$config['tipo']:false;
        if(isset($id_publicador)){
            $dt = Qlib::anoTeocratico();
            $mes = isset($config['mes'])?$config['mes']:$dt['mes'];
            $ano = isset($config['ano'])?$config['ano']:$dt['ano'];
            if($mes==0){
              $mes = 12;
            }
            if($tipo=='compilado'){
              $ret = relatorio::where('id_publicador','=',$id_publicador)->where('mes','=',$mes)->where('ano','=',$ano)->where('compilado','=','s')->count();
            }else{
              $ret = relatorio::where('id_publicador','=',$id_publicador)->where('mes','=',$mes)->where('ano','=',$ano)->count();
            }
        }
        return $ret;
    }
    //Para o secretário registrar ou compilar os relatorios
    public function registrar($id=false)
    {
      $ret['exec'] = false;
      $mes = isset($_GET['m'])?$_GET['m']:false;
      $ano = isset($_GET['y'])?$_GET['y']:false;
      $mens = false;
      $registrar = false;
      $dados = [
        'compilado'=>'s',
      ];

      if($mes && $ano){
        $registrar = relatorio::where('id_publicador','=',$id)->
          where('compilado','=','n')->
          where('mes','=',$mes)->
          where('ano','=',$ano)->
          update($dados);
      }else{
        $registrar = relatorio::where('id_publicador','=',$id)->where('compilado','=','n')->update($dados);
      }
      if($registrar){
        $ret['exec'] = true;
        $mens = 'Foram compilados ('.$registrar.') relatorios';
      }

      $ret['mens'] = $mens;
      return $ret;
    }
    public function estatisticas($mes=false,$ano=false,$id_grupo=false)
    {
      $dt = Qlib::anoTeocratico();
      if(isset($_GET['mes'])){
        $_GET['m'] = $_GET['mes'];
      }
      $mes = isset($_GET['m']) ? $_GET['m'] : $dt['mes'];
      $ano = isset($_GET['ano']) ? $_GET['ano'] : $dt['ano'];
    //   if($mes == '01'){
    //     $mes = '12';
    //     $ano = (date('Y') - 1);
    //   }else{
    //     $mes--;
    //   }
      $ret = false;
      $totalPublicadores['todos'] = Publicador::count();
      $totalPublicadores['inativos'] = Publicador::where('inativo','=','s')->count();
      $totalRelatorios['enviados'] = relatorio::where('hora','>','0')->where('mes','=',$mes)->where('ano','=',$ano)->count();
      if($id_grupo){
        $totalRelatorios['enviados'] = relatorio::where('id_grupo','=',$id_grupo)->where('hora','>','0')->where('mes','=',$mes)->where('ano','=',$ano)->count();
        $totalPublicadores['ativos'] = Publicador::where('inativo','=','n')->where('id_grupo','=',$id_grupo)->count();
      }else{
        $totalPublicadores['ativos'] = Publicador::where('inativo','=','n')->count();
      }
      $progressBar['enviado'] = round(($totalRelatorios['enviados'] * 100)/$totalPublicadores['ativos'],0);
      $ret['totalPublicadores'] = $totalPublicadores;
      $ret['totalRelatorios'] = $totalRelatorios;
      $ret['progressBar'] = $progressBar;
      return $ret;
    }
}
