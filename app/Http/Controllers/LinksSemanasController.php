<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\EventController;
use Illuminate\Http\Request;
use stdClass;
use App\Models\LinkSemana;
use App\Qlib\Qlib;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LinksSemanasController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $tab;
    public function __construct(User $user)
    {
        $this->middleware('auth');
        $this->user = $user;
        $this->routa = 'links-semanas';
        $this->tab = 'links_semanas';
        $this->label = 'Link da Semana';
        $this->view = 'padrao';
    }
    public function queryLinkSemana($get=false,$config=false)
    {
        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        $campo_order = isset($get['campo_order']) ? $get['campo_order'] : 'data';

        $linkSemana = LinkSemana::orderBy($campo_order,$config['order']);

        $linkSemana_totais = new stdClass;
        $campos = isset($_SESSION['campos_links_semanas_exibe']) ? $_SESSION['campos_links_semanas_exibe'] : $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
            $titulo_tab = false;
            $i = 0;
            foreach ($get['filter'] as $key => $value) {
                if(!empty($value)){
                    if(!isset($campos[$key])){
                        continue;
                    }
                    if($key=='id'){
                        $linkSemana->where($key,'LIKE', $value);
                        $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                        $arr_titulo[$campos[$key]['label']] = $value;
                    }else{
                        $linkSemana->where($key,'LIKE','%'. $value. '%');
                        $arr_titulo[$campos[$key]['label']] = $value;
                        $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                    }
                    $i++;
                }
            }
            if($titulo_tab){
                $tituloTabela = 'Lista de: &'.$titulo_tab;
            }
            $fm = clone $linkSemana;
            if($config['limit']=='todos'){
                $linkSemana = $linkSemana->get();
            }else{
                $linkSemana = $linkSemana->paginate($config['limit']);
            }
        }else{
            $fm = clone $linkSemana;
            if($config['limit']=='todos'){
                $linkSemana = $linkSemana->get();
            }else{
                $linkSemana = $linkSemana->paginate($config['limit']);
            }
        }
        $linkSemana_totais->todos = (clone $fm)->count();
        $linkSemana_totais->esteMes = (clone $fm)->whereYear('data', '=', $ano)->whereMonth('data','=',$mes)->count();
        $linkSemana_totais->futuros = (clone $fm)->whereDate('data','>=',date('Y-m-d'))->count();

        $ret['linksemana'] = $linkSemana;
        $ret['linksemana_totais'] = $linkSemana_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$linkSemana_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Links do mês atual','value'=>$linkSemana_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_futuros'=>['label'=>'Semanas futuras','value'=>$linkSemana_totais->futuros,'icon'=>'fas fa-check'],
        ];
        return $ret;
    }
    public function campos(){
        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'data'=>['label'=>'Data da semana (segunda-feira)','active'=>true,'placeholder'=>'Ex.: 2026-01-05','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>'4'],
            'link'=>['label'=>'Link JW (wol.jw.org)','active'=>true,'placeholder'=>'Ex.: https://wol.jw.org/pt/wol/d/r5/lp-t/20260001','type'=>'url','exibe_busca'=>'d-block','event'=>'required','tam'=>'8'],
        ];
    }

    public function index(User $user)
    {
        $this->authorize('ler', $this->routa);
        $title = 'Links das Semanas (JW) Cadastrados';
        $titulo = $title;
        $queryLinkSemana = $this->queryLinkSemana($_GET);
        $queryLinkSemana['config']['exibe'] = 'html';
        $routa = $this->routa;
        return view($this->view.'.index',[
            'dados'=>$queryLinkSemana['linksemana'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryLinkSemana['campos'],
            'linksemana_totais'=>$queryLinkSemana['linksemana_totais'],
            'titulo_tabela'=>$queryLinkSemana['tituloTabela'],
            'arr_titulo'=>$queryLinkSemana['arr_titulo'],
            'config'=>$queryLinkSemana['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ]);
    }
    public function create(User $user)
    {
        $this->authorize('create', $this->routa);
        $title = 'Cadastrar link da semana';
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-links-semanas',
            'route'=>$this->routa,
        ];
        $value = [];
        $campos = $this->campos();
        return view($this->view.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);
    }
    protected function trataData($value){
        $value = trim((string)$value);
        if(strpos($value,'/')!==false){
            $conv = Qlib::dtBanco($value);
            if($conv){
                return $conv;
            }
        }
        return $value;
    }
    public function store(Request $request)
    {
        $this->authorize('create', $this->routa);
        $validatedData = $request->validate([
            'data' => ['required','date','unique:links_semanas,data'],
            'link' => ['required','url','max:500'],
        ]);

        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['data'] = $this->trataData($dados['data'] ?? '');

        $salvar = LinkSemana::create([
            'data'=>$dados['data'],
            'link'=>$dados['link'],
        ]);
        $route = $this->routa.'.index';
        $ret = [
            'mens'=>$this->label.' cadastrado com sucesso!',
            'color'=>'success',
            'idCad'=>$salvar->id,
            'exec'=>true,
            'dados'=>$dados
        ];
        (new EventController)->listarEvent(['tab'=>$this->tab,'id'=>$salvar->id,'this'=>$this]);

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$salvar->id;
            $ret['redirect'] = route($this->routa.'.edit',['id'=>$salvar->id]);
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($linksemana,User $user)
    {
        $id = $linksemana;
        $dados = LinkSemana::where('id',$id)->get();
        $routa = $this->routa;
        $this->authorize('ler', $this->routa);

        if($dados->isNotEmpty()){
            $title = 'Editar link da semana';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            $campos = $this->campos();
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-links-semanas',
                'route'=>$this->routa,
                'id'=>$id,
            ];

            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>false,
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

    public function update(Request $request, $id)
    {
        $this->authorize('update', $this->routa);
        $validatedData = $request->validate([
            'data' => ['required','date','unique:links_semanas,data,'.$id],
            'link' => ['required','url','max:500'],
        ]);
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $data = [
            'data'=>$this->trataData($dados['data'] ?? ''),
            'link'=>$dados['link'] ?? '',
        ];
        $atualizar=false;
        if(!empty($data['data']) && !empty($data['link'])){
            $atualizar=LinkSemana::where('id',$id)->update($data);
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                'return'=>$route,
            ];
        }else{
            $route = $this->routa.'.edit';
            $ret = [
                'exec'=>false,
                'id'=>$id,
                'mens'=>'Erro ao receber dados',
                'color'=>'danger',
            ];
        }
        if($atualizar){
            (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
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
        $this->authorize('delete', $this->routa);
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        $routa = $this->routa;
        if (!$post = LinkSemana::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }

        LinkSemana::where('id',$id)->delete();
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__('Registro '.$id.' deletado com sucesso!'),'color'=>'success','return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>'Registro deletado com sucesso!','color'=>'success']);
        }
        return $ret;
    }
}
