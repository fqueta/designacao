<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Api\VmpController;
use App\Http\Controllers\Controller;
use App\Models\designation;
use App\Models\Post;
use App\Models\Publicador;
use App\Models\Tag;
use App\Models\User;
use App\Qlib\Qlib;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class designaController extends Controller
{
    public function save($design_id = null)
    {
        $ret['exec'] = false;
        if($design_id){
            $d = Post::FindOrFail($design_id);
            if(isset($d['config']) && ($arr=Qlib::lib_json_array($d['config'])))
            if(isset($arr['des']) && is_array($arr['des'])){
                foreach ($arr['des'] as $k => $va) {
                    if(is_array($va)){
                        foreach ($va as $k1 => $val) {
                            if(isset($val['id'])){
                                //Vamos consultar a desiganção
                                $des = designation::where('data','=',$k)
                                ->where('post_type','=',$val['post_type'])
                                ->where('id_designacao','=',$val['id'])->get();
                                echo $k.'<br>';
                                echo $val['id'].'<br>';
                                if($des->count()){
                                    //se existir atualiza
                                    $ret['save'][$k][$val['id']] = designation::where('data','=',$k)->where('post_type','=',$val['post_type'])
                                    ->where('id_designacao','=',$val['id'])->update([
                                        'data' => $k,
                                        'id_designacao' => $val['id'],
                                        'ordem' => $k1,
                                    ]);
                                }else{
                                    //salva se não existir
                                    $ret['save'][$k][$val['id']] = designation::create([
                                        'data' => $k,
                                        'id_designacao' => $val['id'],
                                        'ordem' => $k1,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para retornar um array com as desiganções do periodo em ordem correta
     * @param string $dataI, string $dataF
     * @return array $ret
     *
     */
    public function get_desiganations($dataI,$dataF)
    {
        $ret = false;
        if($dataI && $dataF){
            $post_type = request()->segment(1);
            $d = designation::whereBetween('data', [$dataI, $dataF])->orderBy('data','ASC')->where('post_type','=',$post_type)
            ->orderBy('ordem', 'ASC')->get();
            $pr = [];
            $ret['config']['tipos_designacao'] = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1' AND config LIKE '%\"post_type\":\"$post_type\"%'",'nome','id');
            $ret['config']['participantes'] = Qlib::sql_array("SELECT id,nome FROM publicadores WHERE ativo='s' AND excluido='n' AND deletado='n' ORDER BY nome asc",'nome','id');
            if($d->count() > 0){
                $monthI = Carbon::createFromFormat('Y-m-d', $dataI)->month;
                $monthF = Carbon::createFromFormat('Y-m-d', $dataF)->month;
                $yearI = Carbon::createFromFormat('Y-m-d', $dataF)->year;
                $yearF = Carbon::createFromFormat('Y-m-d', $dataF)->year;
                $d = $d->toArray();

                // dd($d,$dataI,$dataF);
                $json_sessoes = Qlib::qoption('sessoes_designacao');
                $arr_sessoes = Qlib::lib_json_array($json_sessoes);
                $ret['config']['sessoes'] = $arr_sessoes;
                if($yearF && is_array($arr_sessoes)){
                    $fd = Qlib::arr_month2($yearI);
                    if(isset($fd[$yearI][$monthI])){
                        foreach ($fd[$yearI][$monthI] as $dat => $vd) {
                            foreach ($arr_sessoes as $ks => $vs) {
                                $dt = designation::whereDate('data', $vd)
                                ->where('post_type','=',$post_type)
                                ->where('sessao','=',$ks)->orderBy('ordem','asc')
                                ->get();
                                if($dt->count()){
                                    $pr[$vd][$ks] = $dt->toArray();
                                }
                            }
                            // Qlib::lib_print($pr);
                        }
                    }

                    // $mesFim =
                }
            }
            $ret['programa'] = $pr;
            $ret['all'] = $d;
        }
        // dd($ret);
        return $ret;
    }
    public function removeDesignacao(Request $request){
        $ret['exec'] = false;
        if($request->has('id')){
            $id = $request->get('id');
            $ret['exec'] = designation::where('id', $id)->delete();
            $ret['id'] = $id;
        }
        return $ret;
    }
    /**
     * Metodo para pegar o historico de um participante
     * @param integer $id_designado=id do participante,$id_designacao=id da desiganção,$type=id_designado|id_ajudante,$operador pode ser '=' ou '!='
     * @return array $ret
     */
    public function arr_historico($config=false){
        $ret['exec'] = false;
        $ret['d'] = [];
        $id_designado = isset($config['id_designado']) ? $config['id_designado'] : null; //id da parte
        $id_designacao = isset($config['id_designacao']) ? $config['id_designacao'] : null;
        $operador = isset($config['operador']) ? $config['operador'] : '=';
        $ultima = isset($config['ultima']) ? $config['ultima'] : false;
        $type = isset($config['type']) ? $config['type'] : 'id_designado';
        $post_type = isset($config['post_type']) ? $config['post_type'] : '';
        $limit = isset($config['limit']) ? $config['limit'] : 1;
        $sessao = isset($config['sessao']) ? $config['sessao'] : false;
        // dd($config,$id_designado,$id_designacao);

        if($id_designado && $id_designacao){
            if($ultima){
                // DB::getQueryLog();
                if($operador == '!=' && $type == 'id_ajudante'){

                    // dd($type);
                    $d = designation::select('designations.*','tags.nome','tags.config')
                    ->join('tags','tags.id','=','designations.id_designacao')
                    ->where(function($query) use ($id_designado,$type){
                        $query  ->where('designations.id_designado','=',$id_designado)
                                ->orWhere('designations.'.$type,'=',$id_designado);
                    })
                    // ->where('designations.'.$type,'=',$id_designado)
                    ->where('designations.id_designacao',$operador,$id_designacao)
                    ->where('designations.post_type','=',$post_type)
                    ->where('designations.excluido','=','n')
                    ->orderBy('designations.data','desc')
                    ->limit($limit)
                    ->get();
                    // dd($d);
                }else{
                    $d = designation::select('designations.*','tags.nome','tags.config')
                    ->join('tags','tags.id','=','designations.id_designacao')
                    ->where('designations.'.$type,'=',$id_designado)
                    ->where('designations.post_type','=',$post_type)
                    ->where('designations.id_designacao',$operador,$id_designacao)
                    ->where('designations.excluido','=','n')
                    ->orderBy('designations.data','desc')
                    ->limit($limit)
                    ->get();
                }
            }else{
                $d = designation::select('designations.*','tags.nome','tags.config')
                ->join('tags','tags.id','=','designations.id_designacao')
                ->where('designations.'.$type,'=',$id_designado)
                ->where('designations.post_type','=',$post_type)
                ->where('designations.id_designacao',$operador,$id_designacao)
                ->where('designations.excluido','=','n')
                ->orderBy('designations.data','desc')
                ->get();
            }
            if($d->count()){
                $ret['exec'] = true;
            }
            $ret['d'] = $d;
        }
        if(isset($ret['d'][0]['data'])){
            foreach ($ret['d'] as $kd => $vad) {
                $ret['d'][$kd]['data_ex']=Qlib::dataExtensso(@$ret['d'][$kd]['data']);
            }
        }
        return $ret;
    }
    /**
     * Metodos para listar participantes que podem participar de uma parte
     * @param integer $id_designado,string $tipo = tipos de campo de consulta do participante, string $sessao
     * @return array $ret
     */
    public function list_participants($id_designacao, $tipo,$post_type, $sessao=false) {
        //Listar dados da parte
        $dp = Tag::where('id', $id_designacao)->get();
        $ret['exec'] = false;
        if($dp->count() > 0) {
            $dp = $dp->toArray();
            $ret['dp'] = $dp;
            //campo de participante id_designado ou id_ajudante
            $tipo = $tipo ? $tipo : 'id_designado';
            $tip_parte = isset($dp[0]['config']['t_p'])?$dp[0]['config']['t_p']:false;
            //Listar dados dos participantes eligiveis para essa parte
            $ret['tip_parte'] = $tip_parte;
            $d = [];
            if($tip_parte=='especial'){
                //Somento varao ancião
                $d = Publicador::where('fun','=','anc')
                ->where('inativo','=','n')
                ->where('desassociado','=','n')
                ->where('genero','=','m')
                ->where('ativo','=','s')
                ->orderBy('data_ultima','asc')
                ->where('config','LIKE','%"'.$id_designacao.'"%')
                ->get();
                if($d->count() > 0){
                    $ret['exec'] = true;
                    $d = $d->toArray();
                    foreach ($d as $kd => $vd) {
                        $ultima_desta = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'id_designacao'=>$id_designacao,
                            'ultima'=>true,
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'operador'=>'!=',
                            'limit'=>4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                    }
                }
            }elseif($tip_parte == 'instrucao'){
                //Somento varao ancião e servos
                $d = Publicador::where(function($query){
                    $query->orWhere('fun','=','anc')
                    ->orWhere('fun','=','sm');
                })
                ->where('inativo','=','n')
                ->where('desassociado','=','n')
                ->where('genero','=','m')
                ->where('ativo','=','s')
                ->where('config','LIKE','%"'.$id_designacao.'"%')
                ->orderBy('data_ultima','asc')
                ->get();
                if($d->count() > 0){
                    $ret['exec'] = true;
                    $d = $d->toArray();
                    foreach ($d as $kd => $vd) {
                        $ultima_desta = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'operador'=>'!=',
                            'limit'=>4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                    }

                }
            }elseif($tip_parte == 'mecanica'){
                //Somento varao ancião e servos
                $d = Publicador::where('inativo','=','n')
                ->where('desassociado','=','n')
                ->where('genero','=','m')
                ->where('ativo','=','s')
                ->where('config','LIKE','%"'.$id_designacao.'"%')
                ->orderBy('data_ultima','asc')
                ->get();
                if($d->count() > 0){
                    $ret['exec'] = true;
                    $d = $d->toArray();
                    foreach ($d as $kd => $vd) {
                        $ultima_desta = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'operador'=>'='
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'operador'=>'!=',
                            'limit'=>4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                    }

                }
            }else{
                if($tipo=='id_ajudante'){
                    //id da desiganção de ajudante
                    $id_designacao = 28;
                }
                //Somento varao ancião e servos
                $d = Publicador::where('inativo','=','n')
                ->where('desassociado','=','n')
                // ->where('genero','=','m')
                ->orderBy('data_ultima','asc')
                ->where('ativo','=','s')
                ->where('config','LIKE','%"'.$id_designacao.'"%')
                ->get();
                if($id_designacao==6){
                    //Leitura
                    $d = Publicador::where('inativo','=','n')
                    ->where('desassociado','=','n')
                    ->where('genero','=','m')
                    ->where('ativo','=','s')
                    ->where('config','LIKE','%"'.$id_designacao.'"%')
                    ->orderBy('data_ultima','asc')
                    ->get();
                }else{
                    $d = Publicador::where('inativo','=','n')
                    ->where('desassociado','=','n')
                    // ->where('genero','=','m')
                    ->where('config','LIKE','%"'.$id_designacao.'"%')  //desiganções que podem fazer
                    ->where('ativo','=','s')
                    ->orderBy('data_ultima','asc')
                    ->get();
                    // dd($d->toArray());

                }
                if($d->count() > 0){
                    $ret['exec'] = true;
                    $d = $d->toArray();
                    foreach ($d as $kd => $vd) {
                        $ultima_desta = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'operador'=>'='
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'operador'=>'!=',
                            'limit'=>4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                    }

                }
            }
            $ret['data'] = $d;
        }
        return $ret;
    }
    /**
     * Metodos para consultar rotua get_participantes via ajax
     * @return string $json
     */
    public function get_participantes(Request $request){
        $dr = $request->all();
        $ret['exec'] = false;
        $id_designacao = isset($dr['id_designacao']) ? $dr['id_designacao'] : false;
        $tipo = isset($dr['tipo']) ? $dr['tipo'] : false;
        $post_type = isset($dr['post_type']) ? $dr['post_type'] : request()->segment(1);
        $sessao = isset($dr['sessao']) ? $dr['sessao'] : false;
        //Verificar se na requesição tem um id da parte
        if($id_designacao){
            $ret = $this->list_participants($id_designacao,$tipo,$post_type,$sessao);
        }
        //trazer arry das partes
        return response()->json($ret);
    }
    /**
     * Metodo para cadastrar designações automaticas.
     */
    public function add_designacao($config,$type) {
        // $arr_partes = [
        //     ['numero'=>0,'data'=>$data,'token'=>uniqid(),'id_designacao'=>2,'sessao'=>'inicio','post_type'=>'meio-semana'], //presidencia
        // ];
        $ret['exec'] = false;
        $arr_sessoes = ['tesouros','ministerio','vida'];
        $arr_inicio =[
            ['sessao'=>'inicio','tema'=>'','numero'=>'0','id_designacao'=>2,'tempo'=>'','obs'=>''],
            ['sessao'=>'inicio','tema'=>'','numero'=>'0','id_designacao'=>3,'tempo'=>'','obs'=>''],
        ];
        $arr_fim = [
            ['sessao'=>'vida','tema'=>'','numero'=>'0','id_designacao'=>18,'tempo'=>'','obs'=>''],
            ['sessao'=>'vida','tema'=>'','numero'=>'0','id_designacao'=>19,'tempo'=>'','obs'=>''],
            ['sessao'=>'final','tema'=>'','numero'=>'0','id_designacao'=>20,'tempo'=>'','obs'=>''],
            ['sessao'=>'final','tema'=>'','numero'=>'0','id_designacao'=>22,'tempo'=>'','obs'=>''],
            ['sessao'=>'final','tema'=>'','numero'=>'0','id_designacao'=>23,'tempo'=>'','obs'=>''],
            ['sessao'=>'final','tema'=>'','numero'=>'0','id_designacao'=>24,'tempo'=>'','obs'=>''],
        ];

        try {
            //code...
            if(is_array($config)){
                foreach ($config as $k => $data) {
                    if($type=='inic_fim'){
                        //partes que não são da apostila do mes
                        foreach ($arr_inicio as $ki => $vp) {
                            $vp['data'] = $data;
                            $vp['token'] = uniqid();
                            // $vp['sessao'] = 'inicio';
                            $vp['post_type'] = 'meio-semana';
                            $vp['ativo'] = 's';
                            $vp['ordem'] = ($ki+1);
                            // dump($vp);
                            $salv = $this->inserir_parte($vp,$type);
                            $ret['exec'] = @$salv['exec'];
                            $ret['salv_'.$vp['id_designacao'].'_'.$vp['data']][$data] = $salv;
                        }
                        $ki = 30;
                        foreach ($arr_fim as $kf => $vp) {
                            $vp['data'] = $data;
                            $vp['token'] = uniqid();
                            // $vp['sessao'] = 'final';
                            $vp['post_type'] = 'meio-semana';
                            $vp['ativo'] = 's';
                            $vp['ordem'] = ($ki+1);
                            // dump($vp);
                            $salv = $this->inserir_parte($vp,$type);
                            $ret['exec'] = @$salv['exec'];
                            $ret['salv_'.$vp['id_designacao'].'_'.$vp['data']][$data] = $salv;
                        }
                    }else{
                        //partes da apostila do mes
                        $link = Qlib::link_programacao_woljw($data);
                        $arr_partes = (new VmpController)->gera_api($link,$data);
                        // dd($arr_partes);
                        if(is_array($arr_partes) && isset($arr_partes['partes']) && is_array($arr_partes['partes'])){
                            foreach ($arr_sessoes as $ks => $vs) {
                                if(isset($arr_partes['partes'][$vs])){
                                    foreach ($arr_partes['partes'][$vs] as $kp => $vp) {
                                        // dd($vp);
                                        $vp['data'] = $data;
                                        $vp['token'] = uniqid();
                                        $vp['sessao'] = $vs;
                                        $vp['post_type'] = 'meio-semana';
                                        $vp['ativo'] = 's';
                                        $vp['ordem'] = ($kp+1);
                                        $salv = $this->inserir_parte($vp);
                                        $ret['exec'] = @$salv['exec'];
                                        $ret['salv_'.$vp['numero'].'_'.$vp['data']][$data] = $salv;
                                    }
                                }

                            }
                        }
                        sleep(2);
                    }
                }
            }elseif(is_string($config) && ($data = $config)){
                $link = Qlib::link_programacao_woljw($data);
                $arr_partes = (new VmpController)->gera_api($link,$data);
                if(is_array($arr_partes) && isset($arr_partes['partes']) && is_array($arr_partes['partes'])){
                    // $arr_sessoes = ['tesouros','ministerio','vida'];
                    foreach ($arr_sessoes as $ks => $vs) {
                        foreach ($arr_partes['partes'][$vs] as $kp => $vp) {
                            //verifica se ja existe
                            $vp['data'] = $data;
                            $vp['token'] = uniqid();
                            $vp['sessao'] = $vs;
                            $vp['post_type'] = 'meio-semana';
                            $vp['ativo'] = 's';
                            // dump($vp);
                            $vp['ordem'] = ($kp+1);
                            $salv = $this->inserir_parte($vp);
                            $ret['exec'] = @$salv['exec'];
                            $ret['salv_'.$vp['numero'].'_'.$vp['data']] = $salv;
                        }

                    }
                }

            }
            $ret['exec'] = true;
        } catch (\Throwable $th) {
            //throw $th;
            $ret['exec'] = true;
        }
        return $ret;
    }
    /**
     * Metodo para inserir uma parte
     * @param Array $dados
     * @return boolean true | false
     */
    public function inserir_parte($dados,$type='jw') {
        $ret['exec'] = false;
        if($type=='jw'){
            if(isset($dados['data']) && isset($dados['numero']) && $dados['numero'] > 0) {
                //se não encontrar salva
                $ver = designation::where('data', '=', $dados['data'])->where('numero','=',$dados['numero'])->get();
                // dump($dados,$ver);
                if($ver->count() == 0){
                    $salv = designation::create($dados);
                    $ret['salv'] = $salv;
                }else{
                    unset($dados['tema'],$dados['tempo']);
                    $salv = designation::where('data', '=', $dados['data'])->where('numero','=',$dados['numero'])->update($dados);
                    if($salv==1){
                        $ret['salv'] = $salv;
                        $ret['exec'] = true;
                    }
                }
            }
        }else{
            if(isset($dados['data']) && isset($dados['id_designacao']) && $dados['id_designacao'] > 0) {
                //se não encontrar salva
                $ver = designation::where('data', '=', $dados['data'])->where('id_designacao','=',$dados['id_designacao'])->get();
                // dump($dados,$ver);
                if($ver->count() == 0){
                    $salv = designation::create($dados);
                    $ret['salv'] = $salv;
                }else{
                    unset($dados['tema'],$dados['tempo']);
                    $salv = designation::where('data', '=', $dados['data'])->where('id_designacao','=',$dados['id_designacao'])->update($dados);
                    if($salv==1){
                        $ret['salv'] = $salv;
                        $ret['exec'] = true;
                    }
                }
            }

        }
        if($ret['exec']==true){
            $ret['mes'] = Qlib::formatMensagemInfo('Atualizado com sucesso');
        }
        return $ret;
    }
    /**
     * Metodo para sincronizar partes da api do jw
     * @param array $arr_datas
     * @return boolean true|false
     */
    public function sinc_partes(Request $request){
        // $ret = (new designaController)->add_designacao('2024-05-13');
        $dados = $request->all();
        $arr_datas=[];
        $sinc = [];
        if(isset($dados['dados']) && is_string($dados['dados'])){

            $arr_datas = Qlib::decodeArray($dados['dados']);
            if(is_array($arr_datas)){
                $type = false;
                if($request->has('type')){
                    $type = $request->get('type');
                }
                $sinc = $this->add_designacao($arr_datas,$type);
            }
        }
        // if(isset($ret['sinc']['exec']) && $ret['sinc']['exec']){
        //     $ret['exec'] = true;
        //     $ret['mes'] = true;
        // }else{
        //     $ret['exec'] = false;
        // }
        $ret['arr_datas'] = $arr_datas;
        $ret = $sinc;
        return $ret;
    }
}
