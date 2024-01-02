<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RelatoriosController;
use App\Models\Event;
use App\Models\Tag;
use App\Qlib\Qlib;
use Carbon\Carbon as CarbonCarbon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class EventController extends Controller
{
    /**
     * Registra eventos no sistema
     * @return bool;
     */
    protected $user;
    public $label;
    public $view;
    public $tab;
    public function __construct()
    {
        $this->middleware('auth');
        $this->user = Auth::user();
        // $this->routa = request()->route()->getName();
        // $this->routa = '';
        $this->label = 'Relatorios';
        $this->view = 'padrao';
        $this->tab = 'events';
    }
    public function regEvent($config=false)
    {
        //return true;
        //$ev = new EventController;
        //$user = $this->user;
        $ret =false;
        if($config){
            if(isset($config['action']) && isset($config['action'])){
                $action = isset($config['action'])?$config['action']:false;
                $tab = isset($config['tab'])?$config['tab']:false;
                $conf = isset($config['config'])?$config['config']:[];
                $conf['IP'] = Qlib::get_client_ip();
                $user_id = isset($config['user_id'])?$config['user_id']:Auth::id();
                $post_id = isset($config['post_id'])?$config['post_id']:NULL;
                $ds = [
                    'token'=>uniqid(),
                    'user_id'=>$user_id,
                    'post_id'=>$post_id,
                    'action'=>$action,
                    'tab'=>$tab,
                    'config'=>Qlib::lib_array_json($conf),
                ];
                // dd($ds);
                $ret = Event::create($ds);
            }
        }
        return $ret;
    }
    public function listEventsUser($config = null)
    {
        $id_user = isset($config['id_user'])?$config['id_user'] : Auth::id();
        $d = Event::where('user_id','=',$id_user)->orderBy('id','DESC')->get();
        return $d;

    }
    public function listEventsPost($config = null)
    {
        //$id_user = isset($config['id_user'])?$config['id_user'] : 0;
        $post_id = isset($config['post_id'])?$config['post_id'] : 0;
        $d = Event::where('post_id','=',$post_id)->orderBy('id','DESC')->get();
        return $d;
    }
    public function listarEvent($config=false){
        $request = request();
        $regev = false;
        if(isset($config['tab'])){
            $label = isset($config['this']->label)?$config['this']->label:$config['tab'];
            $user = auth()->user();
            $routeName = $request->route()->getName();
            $pRoute = explode('.',$routeName);
            $action = @$pRoute[1];
            $acaoObs = false;
            $link = false;
            $id = $request->route()->parameter('id');
            if($action=='index'){
                $acaoObs = __('Listou cadastros de ').$label;
                $link = Qlib::UrlAtual();
            }elseif($action=='create'){
                $acaoObs = __('Abriu tela de cadastro de ').$label;
                $link = Qlib::UrlAtual();
            }elseif($action=='store'){
                $acaoObs = __('Criou cadastros de ').$label;
                if(isset($config['id'])){
                    $link = route($pRoute[0].'.show',['id'=>$config['id']]);
                    $id = $config['id'];
                }
            }elseif($action=='show'){
                $acaoObs = __('Visualizou cadastros de ').$label;
                $link = route($routeName,['id'=>$id]);
            }elseif($action=='edit'){
                $link = route($routeName,['id'=>$id]);
                $acaoObs = __('Abriu tela de Edição de ').$label;
            }elseif($action=='perfil'){
                $action = 'edit';
                $id = isset($config['id'])?$config['id']:$id;
                $link = route($routeName,['id'=>$id]);
                $acaoObs = __('Abriu tela de Edição de ').$label;
            }elseif($action=='update'){
                $acaoObs = __('Atualizou cadastro de ').$label;
                $link = route($pRoute[0].'.show',['id'=>$id]);
            }
            //dd($request->route()->parameter('id'));
            //REGISTRAR EVENTO DE LISTA
            $cfe = [
                'action'=>$action,
                'tab'=>$config['tab'],
                'user_id'=>$user['id'],
                'post_id'=>$id,
                'config'=>[
                    'obs'=>$acaoObs,
                    'label'=>$label,
                    'link'=>$link,
                ],
            ];
            // dd($cfe);
            $regev = $this->regEvent($cfe);
        }
        return $regev;
    }
    public function queryEvents($get=false,$config=false)
    {
        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $mes = isset($_GET['m'])?$_GET['m'] : date('m');
        $ano = isset($_GET['y'])?$_GET['y'] : date('Y');
        //$todasFamilias = Familia::where('excluido','=','n')->where('deletado','=','n');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];

        // $event =  Event::select('events.*')->join('users','users.id','events.user_id')->where('events.excluido','=','n')->whereYear('events.created_at', '=', $ano)->whereMonth('events.created_at','=',$mes)->where('events.deletado','=','n')->orderBy('events.id',$config['order']);
        $event =  Event::where('excluido','=','n')->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->where('deletado','=','n')->orderBy('id',$config['order']);
        //dd($event);
        $event_totais = new stdClass;
        $campos = isset($_SESSION['campos_bairros_exibe']) ? $_SESSION['campos_bairros_exibe'] : $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $event->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            $event->where($key,'LIKE','%'. $value. '%');
                            if($campos[$key]['type']=='select'){
                                $value = $campos[$key]['arr_opc'][$value];
                            }
                            $arr_titulo[$campos[$key]['label']] = $value;
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                        }
                        $i++;
                    }
                }
                if($titulo_tab){
                    $tituloTabela = 'Lista de: &'.$titulo_tab;
                                //$arr_titulo = explode('&',$tituloTabela);
                }
                $fm = $event;
                if($config['limit']=='todos'){
                    $event = $event->get();
                }else{
                    $event = $event->paginate($config['limit']);
                }

        }else{
            $fm = $event;
            if($config['limit']=='todos'){
                $event = $event->get();
            }else{
                $event = $event->paginate($config['limit']);
            }
        }
        $event_totais->todos = $fm->count();
        $event_totais->esteMes = $fm->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->count();
        // $event_totais->ativos = $fm->where('ativo','=','s')->count();
        // $event_totais->inativos = $fm->where('ativo','=','n')->count();
        $ret['dados'] = $event;
        $ret['dados_totais'] = $event_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$event_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$event_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            // 'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$event_totais->ativos,'icon'=>'fas fa-check'],
            // 'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$event_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    public function campos(){
        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'user_id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'config'=>['label'=>'config','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12','placeholder'=>'Informe o nome do bairro ou do loteamento'],
            // 'cidade'=>['label'=>'Cidade (Opcional)','active'=>true,'type'=>'text','exibe_busca'=>'d-block','placeholder'=>'opcional','event'=>'','tam'=>'12'],
            // 'matricula'=>['label'=>'Matrícula','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'3','placeholder'=>'opcional'],
            // 'total_quadras'=>['label'=>'Total Quadras','active'=>true,'type'=>'number','placeholder'=>'opcional','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
            // 'total_lotes'=>['label'=>'Total de Lote','active'=>true,'type'=>'number','placeholder'=>'opcional','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
            // 'ativo'=>['label'=>'Ativado','active'=>true,'type'=>'chave_checkbox','valor_padrao'=>'s','value'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            // 'obs'=>['label'=>'Observação','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
        ];
    }
    public function listAcessos(Request $request)
    {
        $d = $this->queryEvents($request);
        $title = 'Relatório de Acessos';
        $titulo = $title;
        $config = [];
        $ret = [
            'dados'=>$d,
            'title'=>$title.' | '.config('app.name'),
            'titulo'=>$titulo,
            'routa'=>$this->routa,
            'view'=>$this->view,
            'titulo_tabela'=>$title,
            'config'=>$config,
        ];
        return view('relatorios.acessos.index',$ret);
    }
}
