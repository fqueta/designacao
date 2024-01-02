<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\EventController;
use App\Models\_upload;
use App\Models\User;
use stdClass;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use DataTables;

class UserController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $tab;

    public $access_token;
	public $url_plataforma;
	public $url;
	public $tk_conta;
	public $seg1;

    public function __construct()
    {
        $user = Auth::user();
        $this->middleware('auth');
        $this->user = $user;
        $this->routa = 'users';
        $this->label = 'Usuários';
        $this->view = 'padrao';
        $this->tab = 'users';
        $this->credenciais();
        $this->seg1 = request()->segment(1);
        //$seg2 = request()->segment(2);
    }
    public function credenciais(){
		$this->access_token = 'NWM5OGMyZGRiOTAzMS41ZmQwZGQyNTUzZGI0LjQx';
		$this->url 		 	= 'https://api.ctloja.com.br/v1';
		$this->tk_conta	 	= '624384509209d';
		//$this->tk_conta	 	= '60b77bc73e7c0';
	}

    public function queryUsers($get=false,$config=false)
    {
        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        $logado = Auth::user();
        $user =  User::where('id_permission','>=',$logado->id_permission)->orderBy('id',$config['order']);
        //$user =  DB::table('users')->where('ativo','s')->orderBy('id',$config['order']);

        $users = new stdClass;
        $campos = isset($_SESSION['campos_users_exibe']) ? $_SESSION['campos_users_exibe'] : $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $user->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            $user->where($key,'LIKE','%'. $value. '%');
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
                }
                $fm = $user;
                if($config['limit']=='todos'){
                    $user = $user->get();
                }else{
                    $user = $user->paginate($config['limit']);
                }
        }else{
            $fm = $user;
            if($config['limit']=='todos'){
                $user = $user->get();
            }else{
                $user = $user->paginate($config['limit']);
            }
        }
        $users->todos = $fm->count();
        $users->esteMes = $fm->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->get()->count();
        $users->ativos = $fm->where('ativo','=','s')->get()->count();
        $users->inativos = $fm->where('ativo','=','n')->get()->count();
        //dd($user);
        $ret['user'] = $user;
        $ret['user_totais'] = $users;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$users->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$users->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$users->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$users->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    public function campos($dados=false){
        $user = Auth::user();
        $permission = new admin\UserPermissions($user);

        $ret = [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_permission'=>[
                'label'=>'Permissão*',
                'active'=>true,
                'type'=>'select',
                'data_selector'=>[
                    'campos'=>$permission->campos(),
                    'route_index'=>route('permissions.index'),
                    'id_form'=>'frm-permission',
                    'action'=>route('permissions.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Permissão',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,name FROM permissions WHERE active='s' AND id >='".$user->id_permission."'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
            ],
            'name'=>['label'=>'Nome completo','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'6'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'email'=>['label'=>'Email','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'6'],
            'password'=>['label'=>'Senha','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'','tam'=>'4'],
            'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','checked'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'2','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            //'email'=>['label'=>'Observação','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
        ];
        return $ret;
    }
    public function index()
    {
        $user = $this->user;
        $this->authorize('is_admin', $user);
        $title = 'Usuários Cadastrados';
        $titulo = $title;
        $queryUsers = $this->queryUsers($_GET);
        $queryUsers['config']['exibe'] = 'html';
        $routa = $this->routa;
        $view = $this->view;

        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
        return view($routa.'.index',[
            'dados'=>$queryUsers['user'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryUsers['campos'],
            'user_totais'=>$queryUsers['user_totais'],
            'titulo_tabela'=>$queryUsers['tituloTabela'],
            'arr_titulo'=>$queryUsers['arr_titulo'],
            'config'=>$queryUsers['config'],
            'routa'=>$routa,
            'view'=>$view,
            'i'=>0,
        ]);
    }
    public function create(User $user)
    {
        $this->authorize('is_admin', $user);
        $title = __('Cadastrar usuário');
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-users',
            'route'=>$this->routa,
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $campos = $this->campos();
        //REGISTRAR EVENTO CADASTRO
        $regev = Qlib::regEvent(['action'=>'create','tab'=>$this->tab,'config'=>[
            'obs'=>'Abriu tela de cadastro',
            'link'=>route($this->routa.'.create'),
            ]
        ]);

        return view($this->routa.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required','string','unique:users'],
        ]);
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
        if(isset($dados['password']) && !empty($dados['password'])){
            $dados['password'] = Hash::make($dados['password']);
        }else{
            if(empty($dados['password'])){
                unset($dados['password']);
            }
        }

        $salvar = User::create($dados);
        $route = $this->routa.'.index';
         //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'id'=>$salvar->id,'this'=>$this]);


        $ret = [
            'mens'=>$this->label.' cadastrada com sucesso!',
            'color'=>'success',
            'idCad'=>$salvar->id,
            'exec'=>true,
            'dados'=>$dados
        ];

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$salvar->id;
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function perfilShow(){
        $id = Auth::id();
        return $this->show($id,'perfil');
    }
    public function show($id)
    {
        $local = request()->route()->getName();
        $dados = User::findOrFail($id);
        if($local=='sistema.perfil'){
            $rt = 'sistema';
        }else{
            $rt = $this->routa;
        }
        $this->authorize('ler', $rt);
        if(!empty($dados)){
            $title = 'Cadastro de usuários';
            $titulo = $title;
            //dd($dados);
            $dados['ac'] = 'alt';
            if(isset($dados['config'])){
                $dados['config'] = Qlib::lib_json_array($dados['config']);
            }
            $arr_escolaridade = Qlib::sql_array("SELECT id,nome FROM escolaridades ORDER BY nome ", 'nome', 'id');
            $arr_estadocivil = Qlib::sql_array("SELECT id,nome FROM estadocivils ORDER BY nome ", 'nome', 'id');
            $listFiles = false;
            //$dados['renda_familiar'] = number_format($dados['renda_familiar'],2,',','.');
            $campos = $this->campos($dados,'show');
            if(isset($dados['token'])){
                $listFiles = _upload::where('token_produto','=',$dados['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                'id'=>$id,
                'local'=>$local,
                'class_card1'=>'col-md-8',
                'class_card2'=>'col-md-4',
            ];
            if($local=='sistema.perfil'){
                $config['class_card1'] = 'col-md-12';
                $config['class_card2'] = 'd-none';
            }else{
                //REGISTRAR EVENTOS
                (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            }

            if(!$dados['matricula'])
                $config['display_matricula'] = 'd-none';
            if(isset($dados['config']) && is_array($dados['config'])){
                foreach ($dados['config'] as $key => $value) {
                    if(is_array($value)){

                    }else{
                        $dados['config['.$key.']'] = $value;
                    }
                }
            }
            $campos['ativo']['type']='hidden';
            $ret = [
                'value'=>$dados,
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'arr_escolaridade'=>$arr_escolaridade,
                'arr_estadocivil'=>$arr_estadocivil,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'routa'=>$this->routa,
                'eventos'=>(new EventController)->listEventsPost(['post_id'=>$id]),
                // 'eventos'=>(new EventController)->listEventsUser(['id_user'=>$id]),
                'exec'=>true,
            ];
            // return view($this->routa.'.show',$ret);
            return view($this->view.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
    }
    public function perfilEdit($user,$local=false)
    {
        $id = Auth::id();
        return $this->edit($id);
    }
    public function edit($user)
    {
        $id = $user;
        $dados = User::where('id',$id)->get();
        $local = request()->route()->getName();

        $routa = 'users';
        if($local=='sistema.perfil.edit'){
            $this->authorize('is_admin_logado', $user);
        }else{
            $this->authorize('is_admin', $user);
        }

        if(!empty($dados)){
            $title = 'Editar Cadastro de usuários';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                'id'=>$id,
            ];
            $campos = $this->campos();
            if($local=='sistema.perfil.edit'){
                $campos['ativo']['type']='hidden';
            }
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'exec'=>true,
            ];
            //REGISTRAR EVENTOS
            (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this,'id'=>$id]);


            return view($routa.'.createedit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => ['required'],
        ]);
        $data = [];
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        if(!$dados['password'] || empty($dados['password'])){
            unset($dados['password']);
        }
        if(!$dados['token'] || empty($dados['token'])){
            $dados['token'] = uniqid();
        }
        //dd($dados);
        foreach ($dados as $key => $value) {
            if($key!='_method'&&$key!='_token'&&$key!='ac'&&$key!='ajax'){
                if($key=='data_batismo' || $key=='data_nasci'){
                    if($value=='0000-00-00' || $value=='00/00/0000'){
                    }else{
                        $data[$key] = Qlib::dtBanco($value);
                    }
                }elseif($key=='password'){
                    $data[$key] = Hash::make($value);
                }else{
                    $data[$key] = $value;
                }
            }
        }
        $userLogadon = Auth::id();
        $data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        $data['autor'] = $userLogadon;
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        if(empty($data['passaword'])){
            unset($data['passaword']);
        }
        if(!empty($data)){
            $atualizar=User::where('id',$id)->update($data);
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                'return'=>$route,
            ];
            if($atualizar){
                //REGISTRAR EVENTOS
                (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            }
        }else{
            $route = $this->routa.'.edit';
            $ret = [
                'exec'=>false,
                'id'=>$id,
                'mens'=>'Erro ao receber dados',
                'color'=>'danger',
            ];
        }
        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$id;
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function destroy($id,Request $request)
    {
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        $routa = 'users';
        if (!$post = User::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            //REGISTRAR EVENTO
            $regev = Qlib::regEvent(['action'=>'destroy','tab'=>$this->tab,'config'=>[
                'obs'=>'Exclusão de cadastro Id '.$id,
                'link'=>'#',
                ]
            ]);

            return $ret;
        }

        User::where('id',$id)->delete();
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__('Registro '.$id.' deletado com sucesso!'),'color'=>'success','return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>'Registro deletado com sucesso!','color'=>'success']);
        }
        return $ret;
    }

    public function exec($token_conta = null)
    {
        $cont = false;
        //if($token_conta){
            $verifica_fatura = $this->verifica_faturas(array('token_conta'=>$token_conta));
            if(isset($_GET['teste'])){
                Qlib::lib_print($verifica_fatura);
            }
            $verifica_fatura['acao'] = isset($verifica_fatura['acao'])?$verifica_fatura['acao']:false;
            if(isset($verifica_fatura['acao'])&&$verifica_fatura['acao']=='alertar'){
                if(Qlib::isAdmin()){
                    $cont = @$verifica_fatura['mens'];
                    //echo $cont;
                }
            }elseif($verifica_fatura['acao']=='suspender' || $verifica_fatura['acao']=='desativar'){
                //Não terá acesso ao admin somente ao boleto e as faturas e o site estará desativado tbem
                if(Qlib::isAdmin(3)){
                    $cont = @$verifica_fatura['mens'];
                }else{
                    $cont = Qlib::formatMensagemInfo('Sistema temporariamente suspenso entre em contato com o administrador','danger');
                }
                $pagSusped = 'suspenso';
                if($this->seg1!=$pagSusped){
                    Qlib::redirect('/'.$pagSusped,0);
                    die();
                }
                //echo $cont;
            }
        //}
        return $cont;
    }
    public function verifica_faturas($config=false,$cache=true){
		$ret['exec'] = false;
		$ret['cache'] = false;
		//exemplo de uso
		/*
		$this = new apictloja;
		$ret = $this->verifica_faturas(array('token_conta'=>''));
		Qlib::lib_print($ret);
		*/
		$token = isset($config['token_conta'])?$config['token_conta']:$this->tk_conta;

		if($token){
            $ver_sess = session('verifica_faturas');
            //Qlib::lib_print($ver_sess);

			if(isset($ver_sess['exec'])&&$ver_sess['exec'] && $cache){
				$arr_response = $ver_sess;
				$ret['cache'] = true;
			}else{

				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => $this->url.'/verifica_faturas/'.$token,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'GET',
				  CURLOPT_HTTPHEADER => array(
					'Access-Token: '.$this->access_token
				  ),
				));
				$response = curl_exec($curl);
				curl_close($curl);
				$arr_response = json_decode($response,true);
				//$ret['arr_response'] = $arr_response;
			}
			if(isset($arr_response['exec'])){
				$ret['exec'] = $arr_response['exec'];
				$ret['acao'] = $arr_response['acao'];
                session(['verifica_faturas'=>$arr_response]);
				//$ver_sess=$arr_response;
			}else{
				$ret['acao'] = 'liberar';
            }
			if(isset($arr_response['mens'])){
				$ret['mens'] = $arr_response['mens'];
			}
			$ret['token'] = $token;
		}else{
			$ret['mens'] = Qlib::formatMensagemInfo('Configuração ou token inválido','danger');
		}
        //dd($ret);
        return $ret;
	}
    public function pararAlertaFaturaVencida(Request $request){
        $request->session()->put('verifica_faturas.acao','liberar');
        $ret['exec']=true;
		return $ret;
	}
    public function suspenso()
    {
        return view('admin.suspenso');
    }
}
