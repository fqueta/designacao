<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\wp\ApiWpController;
use App\Http\Requests\StorePostRequest;
use Illuminate\Http\Request;
use stdClass;
use App\Models\Post;
use App\Qlib\Qlib;
use App\Models\User;
use App\Models\_upload;
use App\Models\designation;
use App\Models\Publicador;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class PostController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $post_type;
    public $sec;
    public $i_wp;//integração com wp
    public $wp_api;//integração com wp
    public function __construct(User $user)
    {
        $this->middleware('auth');
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $type = false;
        if($seg1){
            $el = substr($seg1,-1,1);
            if($el=='/'){
                $type = substr($seg1,0,-1);
            }else{
                $type = $seg1;
            }
            if($seg1=='meio-semana' || $seg1=='fim-semana'){
                $type = $seg1;
            }
            // dd($type);
        }
        $this->post_type = $type;
        $this->sec = $seg1;
        $this->user = $user;
        $this->routa = $this->sec;
        $this->label = 'Posts';
        $this->tab = 'posts';
        $this->view = 'admin.posts';
        $this->i_wp = Qlib::qoption('i_wp');//indegração com Wp s para sim
        //$this->wp_api = new ApiWpController();
        $this->wp_api = false;

    }
    public function queryPost($get=false,$config=false)
    {

        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        //$todasFamilias = Post::where('excluido','=','n')->where('deletado','=','n');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        if($this->post_type){
            $post =  Post::where('post_status','!=','inherit')->where('post_type','=',$this->post_type)->orderBy('id',$config['order']);
        }else{
            $post =  Post::where('post_status','!=','inherit')->orderBy('id',$config['order']);
        }
        //$post =  DB::table('posts')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);

        $post_totais = new stdClass;
        $campos = isset($_SESSION['campos_posts_exibe']) ? $_SESSION['campos_posts_exibe'] : $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                if(isset($get['filter']['post_status'])){
                    $get['filter']['post_status'] = 'publish';
                }else{
                    $get['filter']['post_status'] = 'pending';
                }
                //dd($get['filter']);
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $post->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            if(is_array($value)){
                                foreach ($value as $kb => $vb) {
                                    if(!empty($vb)){
                                        if($key=='tags'){
                                            $post->where($key,'LIKE', '%"'.$vb.'"%' );
                                        }else{
                                            $post->where($key,'LIKE', '%"'.$kb.'":"'.$vb.'"%' );
                                        }
                                    }
                                }
                            }else{
                                $post->where($key,'LIKE','%'. $value. '%');
                                if($campos[$key]['type']=='select'){
                                    $value = $campos[$key]['arr_opc'][$value];
                                }
                                $arr_titulo[$campos[$key]['label']] = $value;
                                $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            }
                        }
                        $i++;
                    }
                }
                if($titulo_tab){
                    $tituloTabela = 'Lista de: &'.$titulo_tab;
                                //$arr_titulo = explode('&',$tituloTabela);
                }
                $fm = $post;
                if($config['limit']=='todos'){
                    $post = $post->get();
                }else{
                    $post = $post->paginate($config['limit']);
                }
        }else{
            $fm = $post;
            if($config['limit']=='todos'){
                $post = $post->get();
            }else{
                $post = $post->paginate($config['limit']);
            }
        }
        $post_totais->todos = $fm->count();
        $post_totais->esteMes = $fm->whereYear('post_date', '=', $ano)->whereMonth('post_date','=',$mes)->count();
        $post_totais->ativos = $fm->where('post_status','=','publish')->count();
        $post_totais->inativos = $fm->where('post_status','!=','publish')->count();
        $ret['post'] = $post;
        $ret['post_totais'] = $post_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['post_type'] = $this->post_type;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$post_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$post_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$post_totais->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$post_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }

    public function campos_programa($dados=false){
        $sec = $this->sec;
        $route_name = request()->route()->getName();
        $hidden_editor = '';
        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        if($route_name=="programa.create" || $route_name=="meio-semana.create" || $route_name=="fim-semana.create"){
            $fd = Qlib::arr_month(date('Y'));
            $fd2 = Qlib::arr_month2(date('Y'));
            // dd($fd,$fd2);
            $arr_datas = [];
            $arr_datas2 = [];
            if($fd2 && is_array($fd)){
                foreach($fd2 as $k0=>$v0){
                    foreach($v0 as $k1=>$v1){
                        if(isset($v1[0])){
                            $arr_datas[$v1[0]]=Qlib::dataExtensso($v1[0]);
                        }
                    }
                }
            }
            // if($fd && is_array($fd)){
            //     foreach($fd as $k0=>$v0){
            //         foreach($v0['meses'] as $k1=>$v1){
            //             if(isset($v1[0])){
            //                $arr_datas[$v1[0]]=Qlib::dataExtensso($v1[0]);
            //             }

            //         }
            //     }
            // }
            // dd($arr_datas,$arr_datas2);
            $ret = [
                'ID'=>['label'=>'Id','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
                'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                // 'post_date_gmt'=>['label'=>'Data do decreto','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
                'post_date_gmt'=>[
                    'label'=>'Data de começo para o novo programa',
                    'active'=>true,
                    'type'=>'select',
                    'arr_opc'=>$arr_datas,
                    'exibe_busca'=>'d-block',
                    'event'=>'',
                    'tam'=>'12',
                    'id'=>'categoria_pendencia',
                    'cp_busca'=>'categoria_pendencia][',
                    'class'=>'',
                    'exibe_busca'=>true,
                    'option_select'=>false,
                ],
                'post_title'=>['label'=>'Título','active'=>true,'placeholder'=>'Ex.: Título do decreto','type'=>'hidden','value'=>'Programa Mensal','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                // 'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','value'=>'programa-mensal','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
                //'post_excerpt'=>['label'=>'Resumo (Opcional)','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                //'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
                // 'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Em vigor','pending'=>'Cancelado']],
                // 'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
            ];
        }else{
            // $fd = Qlib::arr_month2(date('Y'));
            // if(isset($dados[0]['post_date_gmt']) && !empty($dados[0]['post_date_gmt'])){
            //     $dt = explode('-', $dados[0]['post_date_gmt']);
            //     $ddo[1] = $fd[$dt[0]][(int)$dt[1]];
            //     $ddo[2] = $fd[$dt[0]][(int)$dt[1]+1];
            //     // dd($ddo);
            //     if(is_array($ddo)){
            //         foreach($ddo as $k=>$v){
            //             // echo $v."<br>"; //exibição de todas semanas
            //         }
            //     }
            // }else{

            // }
            $etp = isset($_GET['etp']) ? $_GET['etp'] : 1;
            if($etp == 1){
                $ret = [
                    'ID'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2'],
                    'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
                    'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                    'post_date_gmt'=>['label'=>'Início do programa','active'=>true,'placeholder'=>'','type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
                    'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
                    'semanas'=>['label'=>'Edição das semanas','active'=>false,'type'=>'html','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'programa.edit_programas_semanais','script_show'=>''],

                ];
            }else{

                $ret = [
                    'ID'=>['label'=>'Id','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                    'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
                    'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                    'post_date_gmt'=>['label'=>'Periodo da designação','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
                    'post_title'=>['label'=>'Título','active'=>true,'placeholder'=>'Ex.: Título do decreto','type'=>'text','exibe_busca'=>'d-block','event'=>'onkeyup=lib_typeSlug(this)','tam'=>'7'],
                    'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
                    //'post_excerpt'=>['label'=>'Resumo (Opcional)','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                    //'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
                    'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Ativado','pending'=>'Desativado']],
                    'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
                ];
            }
        }
        return $ret;
    }
    public function campos($dados=false){
        $sec = $this->sec;
        $hidden_editor = false;
        if($sec=='programa' || $sec=='meio-semana' || $sec=='fim-semana'){
            $ret = $this->campos_programa($dados);
        }else{
            $ret = [
                'ID'=>['label'=>'Id','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
                'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'config[numero]'=>['label'=>'Numero','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'','tam'=>'2','cp_busca'=>'config][numero'],
                'post_date_gmt'=>['label'=>'Data do decreto','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
                'post_title'=>['label'=>'Título','active'=>true,'placeholder'=>'Ex.: Título do decreto','type'=>'text','exibe_busca'=>'d-block','event'=>'onkeyup=lib_typeSlug(this)','tam'=>'7'],
                'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
                //'post_excerpt'=>['label'=>'Resumo (Opcional)','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                //'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
                'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Em vigor','pending'=>'Cancelado']],
                'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>@$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
            ];
        }
        return $ret;
    }
    public function index(User $user)
    {
        //$this->authorize('is_admin', $user);
        $this->authorize('ler', $this->routa);
        if($this->sec=='posts'){
            $title = 'Cadastro de postagens';
        }elseif($this->sec=='pages'){
            $title = 'Cadastro de paginas';
        }elseif($this->sec=='programa' || $this->sec=='meio-semana' || $this->sec=='fim-semana'){
            $title = 'Programação';
            $this->label='programa';
        }else{
            $title = 'Sem titulo';
        }
        $titulo = $title;
        $queryPost = $this->queryPost($_GET);
        $queryPost['config']['exibe'] = 'html';
        $routa = $this->routa;
        //if(isset($queryPost['post']));
        $ret = [
            'dados'=>$queryPost['post'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryPost['campos'],
            'post_totais'=>$queryPost['post_totais'],
            'titulo_tabela'=>$queryPost['tituloTabela'],
            'arr_titulo'=>$queryPost['arr_titulo'],
            'config'=>$queryPost['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ];
        //REGISTRAR EVENTOS
        // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);

        return view($this->view.'.index',$ret);
    }
    public function create(User $user)
    {
        $this->authorize('is_admin2', $user);
        if($this->sec=='posts'){
            $title = 'Cadastro de postagens';
        }elseif($this->sec=='programa' || $this->sec=='meio-semana' || $this->sec=='fim-semana'){
            $title = 'Programação';
        }elseif($this->sec=='pages'){
            $title = 'Cadastro de paginas';
        }
        if($this->sec=='programa' || $this->sec=='meio-semana' || $this->sec=='fim-semana'){
            $this->view = 'programa';
        }
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-posts',
            'route'=>$this->routa,
            'view'=>$this->view,
            'arquivos'=>'jpeg,jpg,png',
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $campos = $this->campos();
         //REGISTRAR EVENTO CADASTRO
        //  $regev = Qlib::regEvent(['action'=>'create','tab'=>$this->tab,'config'=>[
        //     'obs'=>'Abriu tela de cadastro',
        //     'link'=>$this->routa,
        //     ]
        // ]);
        return view($this->view.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);

    }
    public function salvarPostMeta($config = null)
    {
        $post_id = isset($config['post_id'])?$config['post_id']:false;
        $meta_key = isset($config['meta_key'])?$config['meta_key']:false;
        $meta_value = isset($config['meta_value'])?$config['meta_value']:false;
        $ret = false;
        if($post_id&&$meta_key&&$meta_value){
            $verf = Qlib::totalReg('wp_postmeta',"WHERE post_id='$post_id' AND meta_key='$meta_key'");
            if($verf){
                $ret=DB::table('wp_postmeta')->where('post_id',$post_id)->where('meta_key',$meta_key)->update([
                    'meta_value'=>$meta_value,
                ]);
            }else{
                $ret=DB::table('wp_postmeta')->insert([
                    'post_id'=>$post_id,
                    'meta_value'=>$meta_value,
                    'meta_key'=>$meta_key,
                ]);
            }
            //$ret = DB::table('wp_postmeta')->storeOrUpdate();
        }
        return $ret;
    }
    public function store(StorePostRequest $request)
    {
        //$this->authorize('create', $this->routa);
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        //$dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
        $userLogadon = Auth::id();
        $dados['post_author'] = $userLogadon;
        $dados['token'] = !empty($dados['token'])?$dados['token']:uniqid();
        if($this->i_wp=='s' && isset($dados['post_type'])){
            //$endPoint = isset($dados['endPoint'])?$dados['endPoint']:$dados['post_type'].'s';
            $endPoint = 'post';
            $params = $this->geraParmsWp($dados);

            if($params){
                $salvar = $this->wp_api->exec2([
                    'endPoint'=>$endPoint,
                    'method'=>'POST',
                    'params'=>$params
                ]);
                if(isset($salvar['arr']['id']) && $salvar['arr']['id']){
                    $mens = $this->label.' cadastrado com sucesso!';
                    $color = 'success';
                    $idCad = $salvar['arr']['id'];
                }else{
                    $mens = 'Erro ao salvar '.$this->label.'';
                    $color = 'danger';
                    $idCad = 0;
                    if(isset($salvar['arr']['status'])&&$salvar['arr']['status']==400 && isset($salvar['arr']['message']) && !empty($salvar['arr']['message'])){
                        $mens = $salvar['arr']['message'];
                    }
                }
            }else{
                $color = 'danger';
                $mens = 'Parametros invalidos!';
            }
        }else{
            $dados['post_status'] = isset($dados['post_status'])?$dados['post_status']:'publish';
            $salvar = Post::create($dados);
            if(isset($salvar->id) && $salvar->id){
                $mens = $this->label.' cadastrado com sucesso!';
                $color = 'success';
                $idCad = $salvar->id;
                //REGISTRAR EVENTO STORE
                if($salvar->id){
                    $regev = Qlib::regEvent(['action'=>'store','tab'=>$this->tab,'config'=>[
                        'obs'=>'Cadastro guia Id '.$salvar->id,
                        'link'=>$this->routa,
                        ]
                    ]);
                }
            }else{
                $mens = 'Erro ao salvar '.$this->label.'';
                $color = 'danger';
                $idCad = 0;
            }
        }
        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'id'=>@$salvar->id,'this'=>$this]);

        $route = $this->routa.'.index';
        $ret = [
            'mens'=>$mens,
            'color'=>$color,
            'idCad'=>$idCad,
            'exec'=>true,
            'dados'=>$dados
        ];

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$idCad;
            $ret['redirect'] = route($this->routa.'.edit',['id'=>$idCad]);
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function show($id)
    {
        $dados = Post::findOrFail($id);
        $this->authorize('ler', $this->routa);
        if(!empty($dados)){
            $title = 'Programação da Reunião Vida e Ministério';
            $titulo = $title;
            //dd($dados);
            $dados['ac'] = 'alt';
            if(isset($dados['config'])){
                $dados['config'] = Qlib::lib_json_array($dados['config']);
            }
            $listFiles = false;
            //$dados['renda_familiar'] = number_format($dados['renda_familiar'],2,',','.');
            $campos = $this->campos();
            if(isset($dados['token'])){
                $listFiles = _upload::where('token_produto','=',$dados['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-familias',
                'route'=>$this->routa,
                'view'=>$this->view,
                'id'=>$id,
                'class_card1'=>'col-md-8',
                'class_card2'=>'col-md-4',
            ];

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
            $subdomain = Qlib::get_subdominio();
            if(Gate::allows('is_admin2', [$this->routa]) && $subdomain !='cmd'){
                $config['eventos'] = (new EventController)->listEventsPost(['post_id'=>$id]);
            }else{
                $config['class_card1'] = 'col-md-12';
                $config['class_card2'] = 'd-none';
            }
            if($this->sec=='programa' || $this->sec=='meio-semana' || $this->sec=='fim-semana'){
                $this->view='programa';
            }
            //array de tipos de desiganações
            $ret = [
                'value'=>$dados,
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'routa'=>$this->routa,
                'exec'=>true,
            ];
            $json_sessoes = Qlib::qoption('sessoes_designacao');
            $ret['arr_sessoes'] = Qlib::lib_json_array($json_sessoes);
            // $ret['designations'] = (new designaController)->get_desiganations('2023-11-01','2023-12-30');
            // dd($ret['designations']['programa']['2023-11-20']);
            if(isset($dados['post_date_gmt']) && !empty($dados['post_date_gmt'])){
                $fd = Qlib::arr_month2(date('Y'));
                $ddo = [];
                $dt = explode('-', $dados['post_date_gmt']);
                $mes1 = (int)$dt[1];
                $mes2 = (int)$dt[1]+1;

                $ddo[$mes1] = $fd[$dt[0]][$mes1];
                $ddo[$mes2] = $fd[$dt[0]][$mes2];
                $datai = $ddo[$mes1][0];
                $dataf = end($ddo[$mes1]);
                $ret['designations'] = (new designaController)->get_desiganations($datai,$dataf);
                // dd($ret,$this->view);
            }
            //REGISTRAR EVENTOS
            // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            return view($this->view.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
    }
    public function geraParmsWp($dados=false)
    {
        $params=false;
        if($dados && is_array($dados)){

            $arr_parm = [
                'post_name'=>'post_name',
                'post_title'=>'post_title',
                'post_content'=>'post_content',
                'post_excerpt'=>'post_excerpt',
                'post_status'=>'post_status',
                'post_type'=>'post_type',
            ];
            foreach ($dados as $kp => $vp) {
                if(isset($arr_parm[$kp])){
                    $params[$kp] = $dados[$kp];
                }
            }
        }
        return $params;
    }

    public function edit($post,User $user)
    {
        $id = $post;
        $dados = Post::where('id',$id)->get();
        $routa = 'posts';
        $this->authorize('ler', $this->routa);
        if(!empty($dados)){
            $title = 'Editar Cadastro de posts';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            if(isset($dados[0]['post_date_gmt'])){
                $dExec = explode(' ',$dados[0]['post_date_gmt']);
                if(isset($dExec[0])){
                    $dados[0]['post_date_gmt'] = $dExec[0];
                }
            }
            //dd($dados[0]['config']['numero']);
            $listFiles = false;
            $campos = $this->campos($dados[0]);

            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }

            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-posts',
                'route'=>$this->routa,
                'view'=>$this->view,
                'sec'=>$this->sec,
                'id'=>$id,
                'arquivos'=>'jpeg,jpg,png,pdf,PDF',
            ];
            if($this->sec=='programa' || $this->sec=='meio-semana' || $this->sec=='fim-semana'){

                $fd = Qlib::arr_month2(date('Y'));
                $ddo = [];
                $_GET['etp'] = isset($_GET['etp'])?$_GET['etp']:1;
                $designacoes = Tag::where('ativo','=','s')->where('pai','=',1)->where('config','LIKE','%"t_p":"especial"%')->get();
                if($designacoes->count()){
                    $listDes = false;
                    foreach($designacoes As $kd=>$vd){
                        $listDes .= $vd['nome'].', ';
                    }
                    $config['designacoes']['dds'] = $designacoes;
                    $config['designacoes']['lista'] = $listDes;
                }
                $dsalv=[];
                if(isset($dados[0]['post_date_gmt']) && !empty($dados[0]['post_date_gmt'])){
                    $dt = explode('-', $dados[0]['post_date_gmt']);
                    $mes1 = (int)$dt[1];
                    $mes2 = (int)$dt[1]+1;
                    $ddo[$mes1] = $fd[$dt[0]][$mes1];
                    // $ddo[$mes2] = $fd[$dt[0]][$mes2];
                    $datai = $ddo[$mes1][0];
                    $dataf = end($ddo[$mes1]);
                    $config['desiganations'] = (new designaController)->get_desiganations($datai,$dataf);
                    if($config['desiganations']){

                    }else{
                        //  dd($datai, $dataf);
                        if(is_array($ddo)){
                            foreach($ddo as $k=>$v){
                                if(is_array($v)){
                                    foreach ($v as $k1 => $v1) {
                                        if($designacoes->count()){
                                            $listDes = false;
                                            $arr_con = isset($dados[0]['config']['des'])?$dados[0]['config']['des']:false;
                                            if(isset($arr_con[$v1])){
                                                // $designacoes = $arr_con[$v1];
                                                foreach($arr_con[$v1] As $kd=>$vd){
                                                    if(isset($vd['id'])){
                                                        $dds = designation::where('data','=',$v1)->where('id_designacao','=',$vd['id'])->get()->toArray();
                                                        if($dds){
                                                            $dsalv[$v1][$vd['id']] = $dds[0];
                                                        }
                                                    }
                                                }
                                            }else{
                                                foreach($designacoes As $kd=>$vd){
                                                    // $listDes .= $vd['nome'].', ';
                                                    $dds = designation::where('data','=',$v1)->where('id_designacao','=',$vd['id'])->get()->toArray();
                                                    if($dds){
                                                        $dsalv[$v1][$vd['id']] = $dds[0];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $config['semanas'] = $ddo;
                $config['dsalv'] = $dsalv;
                $config['co'] = $dados[0]['config'];  //dados de config
                $this->view = $this->sec;
                if($this->sec=='meio-semana' || $this->sec=='fim-semana'){
                    $this->view = 'programa';
                }
            }
            //REGISTRAR EVENTOS
            // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            $config['arr_desiganacao'] = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1' ORDER BY nome ASC",'nome','id');
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'exec'=>true,
            ];
            return view($this->view.'.createedit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }

    public function update(StorePostRequest $request, $id)
    {
        $this->authorize('update', $this->routa);

        $data = [];
        $mens=false;
        $color=false;
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $d_meta = false;
        // dd($dados);
        if(isset($dados['d_meta'])){
            $d_meta = $dados['d_meta'];
            if(isset($dados['ID'])){
                $d_meta['post_id'] = $dados['ID'];

            }
            unset($dados['d_meta']);
        }
        $desig = [];
        foreach ($dados as $key => $value) {
            if($key!='_method'&&$key!='_token'&&$key!='ac'&&$key!='ajax'){
                if($key=='prog' || $key=='des'){
                    $desig[$key] = $value;
                }else{
                    $data[$key] = $value;
                }
            }
        }
        // dd($dados);
        // if(isset($dados['config']['des']) && ($desig=$dados['config']['des'])){
        //     $sd = $this->salvar_designacao($desig);
        // }
        $sd = [];
        if(isset($dados['des2']) && ($desig=$dados['des2'])){
            $sd = $this->salvar_designacao2($desig);
        }
        $data['post_status'] = isset($data['post_status'])?$data['post_status']:'pending';
        $userLogadon = Auth::id();
        $data['post_author'] = $userLogadon;
        $data['token'] = !empty($data['token'])?$data['token']:uniqid();
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        if(!empty($data)){
            //remover o array das designações
            unset($data['des2']);
                $atualizar=Post::where('id',$id)->update($data);
                if($atualizar){
                    $mens = $this->label.' cadastrado com sucesso!';
                    $color = 'success';
                    $id = $id;
                    //REGISTRAR EVENTOS
                    (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);

                }else{
                    $mens = 'Erro ao salvar '.$this->label.'';
                    $color = 'danger';
                    $id = 0;
                }
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>$mens,
                'color'=>$color,
                'idCad'=>$id,
                'return'=>$route,
            ];
            $ret['sd'] = $sd;
            if($atualizar && $d_meta && $this->i_wp=='s'){
                $ret['salvarPostMeta'] = $this->salvarPostMeta($d_meta);
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
    public function salvar_designacao($config = null)
    {
        $ret['exec'] = false;
        $ret['dataSalv'] = false;
        if(is_array($config)){
            foreach ($config as $data => $des) {
                if(is_array($des)){
                    foreach ($des as $k => $ddta) {

                        $dataSalv[$data][$k]['numero'] = isset($ddta['numero'])?$ddta['numero']:null;
                        $dataSalv[$data][$k]['id_designacao'] = isset($ddta['id'])?$ddta['id']:false;
                        $dataSalv[$data][$k]['id_designado'] = isset($ddta['id_designado'])?$ddta['id_designado']:0;
                        $dataSalv[$data][$k]['orador_visitante'] = isset($ddta['orador_visitante'])?$ddta['orador_visitante']:0;
                        if(!$dataSalv[$data][$k]['id_designacao'])
                            $dataSalv[$data][$k]['id_designacao'] = (int)$dataSalv[$data][$k]['id_designacao'];
                        $dataSalv[$data][$k]['data'] = $data;
                        $dataSalv[$data][$k]['config'] = Qlib::lib_array_json($ddta);
                        $dsa = $dataSalv[$data][$k];
                        if($dsa['id_designado']=='{'){
                            $dsa['id_designado'] = 0;
                        }
                        $upn = designation::where('data','=',$data)->
                        where('numero','=',$dsa['numero'])->
                        where('id_designacao','=',$dsa['id_designacao'])->
                        where('id_designado','=',$dsa['id_designado'])->
                        update($dsa);
                        // dd($upn);
                        if($upn!=1){
                            $sv[$data][$k] = designation::create($dsa);
                            $ret['sv'] = $sv[$data][$k];
                        }

                    }
                }
            }
        }
        // $ret['config'] = $config;
        // $ret['dataSalv'] = $dataSalv;
        return $ret;
    }
    public function salvar_designacao2($config = null)
    {
        $ret['exec'] = false;
        $ret['dataSalv'] = false;
        // dd($config);
        if(is_array($config)){
            $post_type = request()->segment(1);
            $ordem = 0;
            foreach ($config as $data => $de) {
                if(is_array($de)){
                    foreach ($de['partes'] as $k => $ddta0) {
                        if(is_array($ddta0)){
                            foreach ($ddta0 as $k1 => $ddta) {
                                // dd($ddta);
                                $dataSalv[$data][$k]['numero'] = $ddta['numero'];
                                $dataSalv[$data][$k]['token'] = $ddta['token'];
                                if(empty($dataSalv[$data][$k]['numero'])){
                                    $dataSalv[$data][$k]['numero'] = 0;
                                }
                                $dataSalv[$data][$k]['id_designacao'] = isset($ddta['id_designacao'])?$ddta['id_designacao']:0;
                                $dataSalv[$data][$k]['id_designado'] = isset($ddta['id_designado'])?$ddta['id_designado']:0;
                                $dataSalv[$data][$k]['id_ajudante'] = isset($ddta['id_ajudante'])?$ddta['id_ajudante']:0;
                                $dataSalv[$data][$k]['orador_visitante'] = isset($ddta['orador_visitante'])?$ddta['orador_visitante']:0;
                                $dataSalv[$data][$k]['post_type'] = isset($ddta['post_type'])?$ddta['post_type']:$post_type;
                                $dataSalv[$data][$k]['data'] = $data;
                                $dataSalv[$data][$k]['ordem'] = $ordem;
                                $dataSalv[$data][$k]['obs'] = $ddta['obs'];
                                $ddta['sessao'] = $k;
                                $dataSalv[$data][$k]['sessao']  = @$ddta['sessao'];
                                $dataSalv[$data][$k]['config'] = Qlib::lib_array_json($ddta);
                                $dsa = $dataSalv[$data][$k];
                                if($dsa['id_designado']=='{'){
                                    $dsa['id_designado'] = 0;
                                }
                                if($dsa['id_designado']>0 || $dsa['id_designacao']>0){
                                    // dd($k,$dsa);
                                    if(isset($ddta['id']) && ($idReg=$ddta['id'])){
                                        $upn = designation::where('data','=',$data)->
                                        where('id','=',$idReg)->
                                        update($dsa);
                                    }else{
                                        // dd($dsa);
                                        $upn = designation::where('data','=',$data)->
                                        where('token','=',$dsa['token'])->
                                        update($dsa);

                                    }
                                    $ret['da'][$ordem] = $dsa;
                                    // Qlib::lib_print($dsa);
                                    if($upn==1){
                                        $ordem++;
                                        //salvar ultima desiganção para o designado
                                    }else{
                                        $sv[$data][$k] = designation::create($dsa);
                                        // dd($dsa,$sv[$data][$k]);
                                        $upn = $sv[$data][$k];
                                        $ret['sv'] = $upn;
                                        $ordem++;
                                    }
                                    if($upn && $dsa['id_designado']){
                                        $ret['sultma'][$k] = Publicador::where('id','=',$dsa['id_designado'])
                                        ->update([
                                            'data_ultima' => $data,
                                            'token_ultima' => $dsa['token'],
                                        ]);
                                    }

                                }

                            }
                        }
                    }
                }
            }
        }
        // dd($dataSalv);
        // $ret['config'] = $config;
        // $ret['dataSalv'] = $dataSalv;
        return $ret;
    }
    public function destroy($id,Request $request)
    {
        $this->authorize('delete', $this->routa);
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        $routa = 'posts';
        if (!$post = Post::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }
        // dd($id);
        $color = 'success';
        $mens = 'Registro deletado com sucesso!';
        // if($this->i_wp=='s'){
        //     $endPoint = 'post/'.$id;
        //     $delete = $this->wp_api->exec2([
        //         'endPoint'=>$endPoint,
        //         'method'=>'DELETE'
        //     ]);
        //     if($delete['exec']){
        //         $mens = 'Registro '.$id.' deletado com sucesso!';
        //         $color = 'success';
        //     }else{
        //         $color = 'danger';
        //         $mens = 'Erro ao excluir!';
        //     }
        // }else{
            Post::where('id',$id)->delete();
            $mens = 'Registro '.$id.' deletado com sucesso!';
            $color = 'success';
            //REGISTRAR EVENTO
            // $regev = Qlib::regEvent(['action'=>'destroy','tab'=>$this->tab,'config'=>[
            //     'obs'=>'Exclusão de cadastro Id '.$id,
            //     'link'=>$this->routa,
            //     ]
            // ]);

        // }
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__($mens),'color'=>$color,'return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>$mens,'color'=>$color]);
        }
        return $ret;
    }
}
