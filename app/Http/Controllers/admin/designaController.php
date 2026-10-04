<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Api\VmpController;
use App\Http\Controllers\Controller;
use App\Models\designation;
use App\Models\Post;
use App\Models\Publicador;
use App\Models\Tag;
use App\Models\User;
use App\Services\LinkSemanaResolver;
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
            $id_ajudante = Qlib::qoption('id_ajudante')?Qlib::qoption('id_ajudante') : 28;
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
                                    if($val['id'] != $id_ajudante){
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
            $tipos_designacao = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1' AND config LIKE '%\"post_type\":\"$post_type\"%'",'nome','id');
            if(isset($tipos_designacao['17']) && $tipos_designacao['17']=='Estudo bíblico de congregação'){
                $tipos_designacao['17'] = 'Estudo bíblico';
            }
            $ret['config']['tipos_designacao'] = $tipos_designacao;
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
                                    $dpa = $dt->toArray();
                                    $pr[$vd][$ks] = $dpa;
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
                    if($id_designacao==28){
                        $type = 'id_designado';
                        // dd($id_designado,$type);
                    }
                    //verifica qual a ultima parte de todas

                    //nesse momento conferimos se os dados da ultima parte estão sincronizados corretamento do a cadastro do publicador
                    // (pulamos no modo resumido do designar_auto: sync já feito nas listagens e pelo atualizaUltima)
                    if(empty($config['sem_confere'])){
                        $conference = $this->confere_ultima_parte($config);
                    }
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
    public function list_participants($id_designacao, $tipo,$post_type, $sessao=false, $data_ref=null, $resumido=false) {
        //Listar dados da parte
        $dp = Tag::where('id', $id_designacao)->get();
        // Normaliza tipo cedo para a chave do cache
        $tipo = $tipo ? $tipo : 'id_designado';
        // Cache por request: designar_auto chama 1x por parte + Nx para ajudante (28);
        // sem cache, cada chamada refaz centenas de queries (timeout no auto).
        // Quando tipo=id_ajudante fora dos ramos especial/instrucao/mecanica, o id
        // efetivo é 28 (mesma regra do ramo else abaixo) — chave usa o efetivo.
        static $cache_lista = [];
        $tip_pre = isset($dp[0]['config']['t_p']) ? $dp[0]['config']['t_p'] : false;
        $id_efetivo = ($tipo == 'id_ajudante' && $tip_pre != 'especial' && $tip_pre != 'instrucao' && $tip_pre != 'mecanica') ? 28 : $id_designacao;
        $cache_key = $id_efetivo . '|' . $tipo . '|' . $post_type . '|' . $data_ref . '|' . ($resumido ? 'R' : 'C');
        if(isset($cache_lista[$cache_key])){
            return $cache_lista[$cache_key];
        }
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
            // Mapas bulk (1 query cada): contagem p/ variedade + ocupação da data
            $mapVezes = $this->contaVezesParte($id_efetivo, $tipo, $post_type, $data_ref);
            $mapData = ($data_ref && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$data_ref)) ? $this->mapOcupacaoData($data_ref, $post_type) : [];
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
                    // dd($d);
                    foreach ($d as $kd => $vd) {
                        $ultima_desta = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'id_designacao'=>$id_designacao,
                            'ultima'=>true,
                            'sem_confere'=>$resumido,
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'sem_confere'=>$resumido,
                            'operador'=>'!=',
                            'limit'=>$resumido?1:4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                        // Regra de intervalo mínimo entre partes (config do publicador)
                        $infoInt = $this->verificaIntervalo(isset($vd['config']) ? $vd['config'] : [], isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref, $id_designacao);
                        $d[$kd]['em_intervalo'] = $infoInt['em_intervalo'];
                        $d[$kd]['intervalo_ate'] = $infoInt['intervalo_ate'];
                        // Alternância: repetição recente da mesma parte
                        $repInfo = $this->verificaRepeticao(isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref);
                        $d[$kd]['repetiu_recente'] = $repInfo['repetiu_recente'];
                        $d[$kd]['repetiu_ha'] = $repInfo['repetiu_ha'];
                        // Variedade (vezes nesta parte em 6m) e ocupação na data
                        $d[$kd]['vezes_6m'] = isset($mapVezes[$vd['id']]) ? (int)$mapVezes[$vd['id']] : 0;
                        $d[$kd]['ja_nesta_data'] = isset($mapData[$vd['id']]) ? $mapData[$vd['id']] : false;
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
                            'sem_confere'=>$resumido,
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'sem_confere'=>$resumido,
                            'operador'=>'!=',
                            'limit'=>$resumido?1:4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                        // Regra de intervalo mínimo entre partes (config do publicador)
                        $infoInt = $this->verificaIntervalo(isset($vd['config']) ? $vd['config'] : [], isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref, $id_designacao);
                        $d[$kd]['em_intervalo'] = $infoInt['em_intervalo'];
                        $d[$kd]['intervalo_ate'] = $infoInt['intervalo_ate'];
                        // Alternância: repetição recente da mesma parte
                        $repInfo = $this->verificaRepeticao(isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref);
                        $d[$kd]['repetiu_recente'] = $repInfo['repetiu_recente'];
                        $d[$kd]['repetiu_ha'] = $repInfo['repetiu_ha'];
                        // Variedade (vezes nesta parte em 6m) e ocupação na data
                        $d[$kd]['vezes_6m'] = isset($mapVezes[$vd['id']]) ? (int)$mapVezes[$vd['id']] : 0;
                        $d[$kd]['ja_nesta_data'] = isset($mapData[$vd['id']]) ? $mapData[$vd['id']] : false;
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
                            'sem_confere'=>$resumido,
                            'operador'=>'='
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'sem_confere'=>$resumido,
                            'operador'=>'!=',
                            'limit'=>$resumido?1:4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                        // Regra de intervalo mínimo entre partes (config do publicador)
                        $infoInt = $this->verificaIntervalo(isset($vd['config']) ? $vd['config'] : [], isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref, $id_designacao);
                        $d[$kd]['em_intervalo'] = $infoInt['em_intervalo'];
                        $d[$kd]['intervalo_ate'] = $infoInt['intervalo_ate'];
                        // Alternância: repetição recente da mesma parte
                        $repInfo = $this->verificaRepeticao(isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref);
                        $d[$kd]['repetiu_recente'] = $repInfo['repetiu_recente'];
                        $d[$kd]['repetiu_ha'] = $repInfo['repetiu_ha'];
                        // Variedade (vezes nesta parte em 6m) e ocupação na data
                        $d[$kd]['vezes_6m'] = isset($mapVezes[$vd['id']]) ? (int)$mapVezes[$vd['id']] : 0;
                        $d[$kd]['ja_nesta_data'] = isset($mapData[$vd['id']]) ? $mapData[$vd['id']] : false;
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
                    // dump($tipo);
                    foreach ($d as $kd => $vd) {
                        $ultima_desta = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'sem_confere'=>$resumido,
                            'operador'=>'='
                        ]);
                        $ultima_outra = $this->arr_historico([
                            'post_type'=>$post_type,
                            'id_designacao'=>$id_designacao,
                            'id_designado'=>$vd['id'],
                            'type'=>$tipo,
                            'ultima'=>true,
                            'sem_confere'=>$resumido,
                            'operador'=>'!=',
                            'limit'=>$resumido?1:4,
                        ]);
                        //Adicionar historico desta parte
                        $d[$kd]['ultima_desta'] = isset($ultima_desta['d'][0])?$ultima_desta['d'][0]:[];
                        //Adicionar historico de outras partes
                        $d[$kd]['ultima_outra'] = isset($ultima_outra['d'][0])?$ultima_outra['d'][0]:[];
                        $d[$kd]['ultimas_quatro'] = isset($ultima_outra['d'])?$ultima_outra['d']:[];
                        // Regra de intervalo mínimo entre partes (config do publicador)
                        $infoInt = $this->verificaIntervalo(isset($vd['config']) ? $vd['config'] : [], isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref, $id_designacao);
                        $d[$kd]['em_intervalo'] = $infoInt['em_intervalo'];
                        $d[$kd]['intervalo_ate'] = $infoInt['intervalo_ate'];
                        // Alternância: repetição recente da mesma parte
                        $repInfo = $this->verificaRepeticao(isset($ultima_desta['d'][0]) ? $ultima_desta['d'][0] : null, $data_ref);
                        $d[$kd]['repetiu_recente'] = $repInfo['repetiu_recente'];
                        $d[$kd]['repetiu_ha'] = $repInfo['repetiu_ha'];
                        // Variedade (vezes nesta parte em 6m) e ocupação na data
                        $d[$kd]['vezes_6m'] = isset($mapVezes[$vd['id']]) ? (int)$mapVezes[$vd['id']] : 0;
                        $d[$kd]['ja_nesta_data'] = isset($mapData[$vd['id']]) ? $mapData[$vd['id']] : false;
                    }

                }
            }
            // Ordena pelo mais antigo NESTA parte (nunca fez primeiro).
            // Elegibilidade (aceita/fun/genero) já foi filtrada nas queries acima.
            $d = $this->ordenarPorAntiguidade($d);
            $ret['data'] = $d;
        }
        $cache_lista[$cache_key] = $ret;
        return $ret;
    }
    /**
     * Verifica se o participante está dentro do intervalo mínimo desta parte.
     * Lê config.designacao.intervalo_{id} (0-4 meses) e compara
     * ultima_desta.data + N meses com a data de referência (semana preenchida).
     * @return array ['em_intervalo'=>bool,'intervalo_ate'=>d/m/Y|null]
     */
    public function verificaIntervalo($config_pub, $ultima_desta, $data_ref, $id_designacao){
        $ret = ['em_intervalo' => false, 'intervalo_ate' => null];
        $meses = isset($config_pub['designacao']['intervalo_' . $id_designacao]) ? (int)$config_pub['designacao']['intervalo_' . $id_designacao] : 0;
        if($meses <= 0 || empty($data_ref) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$data_ref)){
            return $ret;
        }
        $ultima = null;
        if(is_array($ultima_desta) && isset($ultima_desta['data'])){
            $ultima = $ultima_desta['data'];
        }elseif($ultima_desta instanceof \ArrayAccess && isset($ultima_desta['data'])){
            $ultima = $ultima_desta['data'];
        }elseif(is_object($ultima_desta) && isset($ultima_desta->data)){
            $ultima = $ultima_desta->data;
        }
        if(!$ultima || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$ultima)){
            return $ret; // nunca fez: sempre livre
        }
        $limite = date('Y-m-d', strtotime($ultima . ' +' . $meses . ' months'));
        if($data_ref < $limite){
            $ret['em_intervalo'] = true;
            $ret['intervalo_ate'] = Qlib::dataExibe($limite);
        }
        return $ret;
    }
    /**
     * Verifica repetição recente da mesma parte (alternância).
     * Janela configurável via qoption('alternancia_semanas'), padrão 4 semanas.
     * @return array ['repetiu_recente'=>bool,'repetiu_ha'=>string|null ex.: "2 sem"]
     */
    public function verificaRepeticao($ultima_desta, $data_ref){
        $ret = ['repetiu_recente' => false, 'repetiu_ha' => null];
        $semanas = (int)(Qlib::qoption('alternancia_semanas') ?: 4);
        if($semanas <= 0 || empty($data_ref) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$data_ref)){
            return $ret;
        }
        $ultima = null;
        if(is_array($ultima_desta) && isset($ultima_desta['data'])){
            $ultima = $ultima_desta['data'];
        }elseif($ultima_desta instanceof \ArrayAccess && isset($ultima_desta['data'])){
            $ultima = $ultima_desta['data'];
        }elseif(is_object($ultima_desta) && isset($ultima_desta->data)){
            $ultima = $ultima_desta->data;
        }
        if(!$ultima || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$ultima)){
            return $ret; // nunca fez: sempre livre
        }
        $diff = (strtotime($data_ref) - strtotime($ultima)) / 86400;
        if($diff >= 0 && $diff < $semanas * 7){
            $ret['repetiu_recente'] = true;
            $s = (int)floor($diff / 7);
            $ret['repetiu_ha'] = $s <= 0 ? 'esta semana' : ($s == 1 ? '1 sem' : $s . ' sem');
        }
        return $ret;
    }
    /**
     * Conta execuções desta parte por participante nos últimos 6 meses (variedade).
     * 1 query bulk; retorna [id_publicador => total].
     */
    protected function contaVezesParte($id_designacao, $tipo, $post_type, $data_ref){
        $map = [];
        try {
            // Espelha arr_historico: espelho 28 guarda a pessoa em id_designado
            $col = ($tipo == 'id_ajudante' && (int)$id_designacao !== 28) ? 'id_ajudante' : 'id_designado';
            $desde = ($data_ref && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$data_ref))
                ? date('Y-m-d', strtotime($data_ref . ' -6 months'))
                : date('Y-m-d', strtotime('-6 months'));
            $rows = \Illuminate\Support\Facades\DB::table('designations')
                ->select($col . ' as pid', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                ->where('post_type', '=', $post_type)
                ->where('excluido', '=', 'n')
                ->where('id_designacao', '=', (int)$id_designacao)
                ->where($col, '>', 0)
                ->where('data', '>=', $desde)
                ->groupBy($col)
                ->get();
            foreach($rows as $r){
                $map[(int)$r->pid] = (int)$r->total;
            }
        } catch (\Throwable $e) {
            // Sem contagem = sem viés de variedade
        }
        return $map;
    }
    /**
     * Mapeia quem já tem parte na data (ambos os papéis, inclusive espelhos 28).
     * 1 query bulk; retorna [id_publicador => ['numero'=>N,'parte'=>Nome]].
     */
    protected function mapOcupacaoData($data, $post_type){
        $map = [];
        try {
            $rows = \Illuminate\Support\Facades\DB::table('designations')
                ->select('id_designacao', 'numero', 'id_designado', 'id_ajudante')
                ->where('data', '=', $data)
                ->where('post_type', '=', $post_type)
                ->where('excluido', '=', 'n')
                ->get();
            $nomes = [];
            foreach($rows as $r){
                foreach(['id_designado', 'id_ajudante'] as $campo){
                    $pid = (int)$r->$campo;
                    if($pid > 0 && !isset($map[$pid])){
                        $tid = (int)$r->id_designacao;
                        if(!isset($nomes[$tid])){
                            $nomes[$tid] = Qlib::buscaValorDb0('tags', 'id', $tid, 'nome') ?: ('parte ' . $tid);
                        }
                        $map[$pid] = ['numero' => $r->numero, 'parte' => $nomes[$tid]];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Sem mapa = sem selo
        }
        return $map;
    }
    /**
     * Ordena participantes pelo mais antigo nesta parte.
     * Critério: 1) livres primeiro (em_intervalo, repetiu_recente e ja_nesta_data
     * vão para o fim, continuando clicáveis no manual);
     * 2) nunca fez esta parte (ultima_desta vazia) primeiro;
     * 3) ultima_desta.data asc; 4) desempate vezes_6m asc (variedade);
     * 5) desempate ultima_outra.data asc; 6) data_ultima asc; 7) nome asc.
     * @param array $d lista de participantes com ultima_desta/ultima_outra
     * @return array $d ordenado
     */
    public function ordenarPorAntiguidade($d){
        if(!is_array($d) || empty($d)){
            return $d;
        }
        // ultima_desta/outra podem vir como array ou Model (ArrayAccess)
        $getData = function($v){
            if(is_array($v) && isset($v['data'])){
                return $v['data'];
            }
            if($v instanceof \ArrayAccess && isset($v['data'])){
                return $v['data'];
            }
            if(is_object($v) && isset($v->data)){
                return $v->data;
            }
            return null;
        };
        usort($d, function($a, $b) use ($getData){
            // Intervalo, repetição recente ou já ocupado na data: fim da lista
            $low = function($x){
                return !empty(@$x['em_intervalo']) || !empty(@$x['repetiu_recente']) || !empty(@$x['ja_nesta_data']);
            };
            $la = $low($a);
            $lb = $low($b);
            if($la && !$lb){
                return 1;
            }
            if($lb && !$la){
                return -1;
            }
            $da = $getData(@$a['ultima_desta']);
            $db = $getData(@$b['ultima_desta']);
            // Nunca fez esta parte vai para o topo
            if($da === null && $db !== null){
                return -1;
            }
            if($db === null && $da !== null){
                return 1;
            }
            if($da !== null && $db !== null && $da !== $db){
                return $da < $db ? -1 : 1;
            }
            // Desempate: variedade — quem fez menos vezes esta parte (6m) primeiro
            $va = isset($a['vezes_6m']) ? (int)$a['vezes_6m'] : 0;
            $vb = isset($b['vezes_6m']) ? (int)$b['vezes_6m'] : 0;
            if($va !== $vb){
                return $va < $vb ? -1 : 1;
            }
            // Desempate: última de outra parte (mais antiga primeiro)
            $oa = $getData(@$a['ultima_outra']);
            $ob = $getData(@$b['ultima_outra']);
            if($oa === null && $ob !== null){
                return -1;
            }
            if($ob === null && $oa !== null){
                return 1;
            }
            if($oa !== null && $ob !== null && $oa !== $ob){
                return $oa < $ob ? -1 : 1;
            }
            // Desempate final: data_ultima e nome
            $ua = isset($a['data_ultima']) ? $a['data_ultima'] : null;
            $ub = isset($b['data_ultima']) ? $b['data_ultima'] : null;
            if($ua !== $ub){
                if($ua === null){
                    return -1;
                }
                if($ub === null){
                    return 1;
                }
                return $ua < $ub ? -1 : 1;
            }
            $na = isset($a['nome']) ? mb_strtolower($a['nome']) : '';
            $nb = isset($b['nome']) ? mb_strtolower($b['nome']) : '';
            return $na <=> $nb;
        });
        return $d;
    }
    /**
     * Para conferir e acertar a ultima parte de um participante
     * @param array $config dados da designação
     * @return array $rey
     */
    public function confere_ultima_parte($config){
        $ret['exec'] = false;
        $ret['d'] = [];
        $id_designado = isset($config['id_designado']) ? $config['id_designado'] : null; //id da parte
        $id_designacao = isset($config['id_designacao']) ? $config['id_designacao'] : null;
        $operador = isset($config['operador']) ? $config['operador'] : '=';
        $ultima = isset($config['ultima']) ? $config['ultima'] : false;
        $type = isset($config['type']) ? $config['type'] : 'id_designado';
        $post_type = isset($config['post_type']) ? $config['post_type'] : '';
        $limit = isset($config['limit']) ? $config['limit'] : 1;
        // Trava por request: arr_historico chama 2x por participante por parte;
        // sem isso o designar_auto refaz o mesmo sync centenas de vezes.
        static $conf_sync = [];
        $ck_sync = $post_type . '|' . $id_designado;
        if($id_designado && isset($conf_sync[$ck_sync])){
            $ret['exec'] = true;
            return $ret;
        }
        $conf_sync[$ck_sync] = true;
        $du = designation::select('designations.*','tags.nome','tags.config')
        ->join('tags','tags.id','=','designations.id_designacao')
        ->where('designations.id_designado','=',$id_designado)
        ->where('designations.post_type','=',$post_type)
        // ->where('designations.id_designacao','=',$id_designacao)
        ->where('designations.excluido','=','n')
        ->orderBy('designations.data','desc')
        ->limit(1)
        ->get();
        if($du->count()>0){
            $dta = $du->toArray();

            // dd($operador,$limit,$dta);
            $ultima_data_gravad = Qlib::buscaValorDb0('publicadores','id',$id_designado,'data_ultima');
            if($ultima_data_gravad!=$dta[0]['data']){
                $salv = Publicador::where('id','=',$id_designado)->update([
                    'data_ultima' => $dta[0]['data'],
                    'token_ultima' => $dta[0]['token'],
                ]);
                // if($salv){

                // }
            }
        }else{
            $salv = Publicador::where('id','=',$id_designado)->update([
                'data_ultima' => '1971-01-01',
                'token_ultima' => '',
            ]);
        }
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
        $data_ref = isset($dr['data']) && is_string($dr['data']) ? trim($dr['data']) : null;
        //Verificar se na requesição tem um id da parte
        if($id_designacao){
            $ret = $this->list_participants($id_designacao,$tipo,$post_type,$sessao,$data_ref);
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
            // dd($config);
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
                        //Auto-resolve: usa links_semanas se existir, senão gera+valida+salva automaticamente (só futuras)
                        $link = LinkSemanaResolver::resolve($data) ?: Qlib::link_programacao_woljw($data);
                        if (empty($link)) {
                            $ret['sem_link_'.$data][$data] = ['exec' => false, 'mens' => 'Sem link JW para a data '.$data.' (cadastre em Links JW ou tente novamente)'];
                            sleep(2);
                            continue;
                        }
                        // dd($data,$link);
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
                //Auto-resolve: usa links_semanas se existir, senão gera+valida+salva automaticamente (só futuras)
                $link = LinkSemanaResolver::resolve($data) ?: Qlib::link_programacao_woljw($data);
                if (empty($link)) {
                    $ret['exec'] = false;
                    $ret['mens'] = 'Sem link JW para a data '.$data.' (cadastre em Links JW ou tente novamente)';
                    return $ret;
                }
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
            $ret['exec'] = false;
            $ret['mens'] = $th->getMessage();
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
        //id padrão da designação de ajudante
        $id_ajudante = Qlib::qoption('id_ajudante')?Qlib::qoption('id_ajudante') : 28;
        if($type=='jw'){
            if(isset($dados['data']) && isset($dados['numero']) && $dados['numero'] > 0) {
                //não pode gravar em uma desiganão de ajudante
                //se não encontrar salva
                $ver = designation::where('data', '=', $dados['data'])->where('numero','=',$dados['numero'])->where('id_designacao','!=',$id_ajudante)->get();
                // dump($dados,$ver);
                if($ver->isEmpty() && $dados['id_designacao']!=$id_ajudante){
                    $salv = designation::create($dados);
                    $ret['salv'] = $salv;
                }else{
                    unset($dados['tema'],$dados['tempo']);
                    $salv = designation::where('data', '=', $dados['data'])->where('numero','=',$dados['numero'])->where('id_designacao','!=',$id_ajudante)->update($dados);
                    if($salv==1){
                        $ret['salv'] = $salv;
                        $ret['exec'] = true;
                    }
                }
            }
        }else{
            if(isset($dados['data']) && isset($dados['id_designacao']) && $dados['id_designacao'] > 0) {
                //se não encontrar salva
                $ver = designation::where('data', '=', $dados['data'])->where('id_designacao','=',$dados['id_designacao'])->where('id_designacao','!=',$id_ajudante)->get();
                if($ver->count() == 0 && $dados['id_designacao']!=$id_ajudante){
                    // dump($dados,$ver);

                    $salv = designation::create($dados);
                    $ret['salv'] = $salv;
                }else{
                    unset($dados['tema'],$dados['tempo']);
                    $salv = designation::where('data', '=', $dados['data'])->where('id_designacao','=',$dados['id_designacao'])->where('id_designacao','!=',$id_ajudante)->update($dados);
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
            // dd($arr_datas);
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
    /**
     * Designa automaticamente participantes para as partes VAZIAS de uma semana.
     * Regras: elegibilidade idêntica ao modal (list_participants: aceita + fun/genero),
     * ordem por antiguidade (nunca fez primeiro), sem repetir pessoa na semana,
     * ajudante com preferência de mesmo sexo. Nunca troca escolha manual.
     * @param Request $request dados=base64(json([Y-m-d])), post_type=meio-semana|fim-semana
     * @return array resumo {exec, mens, designadas, puladas, avisos}
     */
    public function designar_auto(Request $request){
        if(function_exists('set_time_limit')){
            @set_time_limit(180);
        }
        $ret = ['exec' => false, 'designadas' => 0, 'puladas' => 0, 'avisos' => []];
        $dados = $request->all();
        $arr_datas = isset($dados['dados']) && is_string($dados['dados']) ? Qlib::decodeArray($dados['dados']) : false;
        if(!is_array($arr_datas)){
            // Aceita data única Y-m-d também
            $uma = isset($dados['dados']) && is_string($dados['dados']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($dados['dados'])) ? [trim($dados['dados'])] : [];
            $arr_datas = $uma;
        }
        $post_type = isset($dados['post_type']) && in_array($dados['post_type'], ['meio-semana', 'fim-semana']) ? $dados['post_type'] : 'meio-semana';
        if(empty($arr_datas)){
            $ret['mens'] = 'Nenhuma data informada';
            return $ret;
        }
        $id_ajudante_tag = Qlib::qoption('id_ajudante') ? Qlib::qoption('id_ajudante') : 28;
        // Sessoes que pedem ajudante (mesma regra da view li_partes_meio)
        $sessao_sem_ajudante = ['tesouros', 'inicio', 'vida'];
        // Partes que nunca pedem ajudante (ex.: Discurso) — resolve por nome
        $partes_sem_ajudante = self::idsSemAjudante();
        try {
            foreach($arr_datas as $data){
                $data = trim((string)$data);
                if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)){
                    continue;
                }
                $partes = designation::where('data', '=', $data)
                    ->where('post_type', '=', $post_type)
                    ->where('excluido', '=', 'n')
                    ->orderBy('ordem', 'asc')
                    ->get();
                // Normaliza Discurso (e demais partes sem ajudante): remove ajudante
                // indevido mesmo em partes já preenchidas manualmente. Vale na
                // próxima execução do automático, sem apagar o designado.
                foreach($partes as $parte){
                    if((int)$parte->id_designacao === (int)$id_ajudante_tag){
                        continue;
                    }
                    if(empty($parte->id_ajudante)){
                        continue;
                    }
                    if(!in_array((int)$parte->id_designacao, $partes_sem_ajudante, true)){
                        continue;
                    }
                    $oldAj = (int)$parte->id_ajudante;
                    designation::where('id', $parte->id)->update(['id_ajudante' => 0]);
                    $parte->id_ajudante = 0;
                    // Remove linha-espelho do ajudante (id_designacao 28) do mesmo número/data
                    try {
                        designation::where('data', '=', $data)
                            ->where('post_type', '=', $post_type)
                            ->where('id_designacao', '=', (int)$id_ajudante_tag)
                            ->where('numero', '=', $parte->numero)
                            ->where('id_designado', '=', $oldAj)
                            ->delete();
                    } catch (\Throwable $e) {
                        // Limpeza do espelho é best-effort
                    }
                    $ret['avisos'][] = $data . ': ajudante removido de ' . $this->nomeParte($parte->id_designacao) . ' (parte ' . $parte->numero . ')';
                }
                // Pessoas já ocupadas na semana (manuais ou não): não repetir
                // (montado após a limpeza acima, já sem o ajudante do Discurso)
                $usados = [];
                foreach($partes as $p){
                    if(!empty($p->id_designado)){
                        $usados[(int)$p->id_designado] = true;
                    }
                    if(!empty($p->id_ajudante)){
                        $usados[(int)$p->id_ajudante] = true;
                    }
                }
                foreach($partes as $parte){
                    // Pula linha-espelho de ajudante (gerada no save) e partes já preenchidas
                    if((int)$parte->id_designacao === (int)$id_ajudante_tag){
                        continue;
                    }
                    if(!empty($parte->id_designado)){
                        continue;
                    }
                    if(empty($parte->id_designacao)){
                        $ret['puladas']++;
                        $ret['avisos'][] = $data . ': parte sem tipo de designação';
                        continue;
                    }
                    $lista = $this->list_participants($parte->id_designacao, 'id_designado', $post_type, false, $data, true);
                    $cands = isset($lista['data']) && is_array($lista['data']) ? $lista['data'] : [];
                    $escolhido = $this->primeiroLivre($cands, $usados);
                    if(!$escolhido){
                        $ret['puladas']++;
                        $ret['avisos'][] = $data . ': sem elegível livre para ' . $this->nomeParte($parte->id_designacao) . $this->textoMotivos($this->motivosBloqueio($cands, $usados));
                        continue;
                    }
                    $upd = ['id_designado' => (int)$escolhido['id']];
                    $usados[(int)$escolhido['id']] = true;
                    $eh_sem_ajudante = in_array((int)$parte->id_designacao, $partes_sem_ajudante, true);
                    if($eh_sem_ajudante){
                        // Parte sem ajudante (Discurso/mecânicas): garante zerado
                        $upd['id_ajudante'] = 0;
                    }
                    // Ajudante: só onde a tela pede (meio-semana/ministerio) e se vazio
                    // e nunca para partes sem ajudante (ex.: Discurso, Palco)
                    if(!$eh_sem_ajudante && empty($parte->id_ajudante) && !in_array($parte->sessao, $sessao_sem_ajudante)){
                        $ajud = $this->escolheAjudante($parte->id_designacao, $post_type, $usados, @$escolhido['genero'], $data, true);
                        if($ajud){
                            $upd['id_ajudante'] = (int)$ajud['id'];
                            $usados[(int)$ajud['id']] = true;
                        }else{
                            $ret['avisos'][] = $data . ': estudante ' . @$escolhido['nome'] . ' sem ajudante livre';
                        }
                    }
                    designation::where('id', $parte->id)->update($upd);
                    $this->atualizaUltima($upd, $data, $parte->token);
                    $ret['designadas']++;
                }
            }
            $ret['exec'] = true;
            $ret['mens'] = $ret['designadas'] . ' parte(s) preenchida(s)' . ($ret['puladas'] ? ', ' . $ret['puladas'] . ' pulada(s)' : '');
        } catch (\Throwable $th) {
            $ret['exec'] = false;
            $ret['mens'] = $th->getMessage();
        }
        return $ret;
    }
    /**
     * Primeiro da lista (já ordenada) livre para a semana: pula ocupados na data,
     * em intervalo, com repetição recente ou que já têm parte nesta reunião.
     */
    protected function primeiroLivre($lista, $usados){
        if(!is_array($lista)){
            return false;
        }
        foreach($lista as $c){
            $id = is_array($c) ? @$c['id'] : (@$c->id);
            $bloq = is_array($c)
                ? (!empty(@$c['em_intervalo']) || !empty(@$c['repetiu_recente']) || !empty(@$c['ja_nesta_data']))
                : (!empty(@$c->em_intervalo) || !empty(@$c->repetiu_recente) || !empty(@$c->ja_nesta_data));
            if($id && !isset($usados[(int)$id]) && !$bloq){
                return is_array($c) ? $c : (array)$c;
            }
        }
        return false;
    }
    /**
     * Conta bloqueios da lista para aviso específico (intervalo/repetição/ocupados).
     */
    protected function motivosBloqueio($lista, $usados){
        $m = ['ocupados' => 0, 'intervalo' => 0, 'repetidos' => 0, 'ja_data' => 0];
        if(!is_array($lista)){
            return $m;
        }
        foreach($lista as $c){
            $id = is_array($c) ? @$c['id'] : (@$c->id);
            if(!$id){
                continue;
            }
            if(isset($usados[(int)$id])){
                $m['ocupados']++;
                continue;
            }
            $g = function($k) use ($c){
                return is_array($c) ? !empty(@$c[$k]) : (!empty(@$c->$k));
            };
            if($g('ja_nesta_data')){
                $m['ja_data']++;
            }elseif($g('em_intervalo')){
                $m['intervalo']++;
            }elseif($g('repetiu_recente')){
                $m['repetidos']++;
            }
        }
        return $m;
    }
    /**
     * Escolhe ajudante: elegíveis da parte 28, prefere mesmo sexo do estudante.
     */
    protected function escolheAjudante($id_designacao, $post_type, $usados, $genero_estudante = null, $data = null, $resumido = false){
        $lista = $this->list_participants($id_designacao, 'id_ajudante', $post_type, false, $data, $resumido);
        $candidatos = isset($lista['data']) && is_array($lista['data']) ? $lista['data'] : [];
        if($genero_estudante){
            $mesmo = $this->primeiroLivre(array_values(array_filter($candidatos, function($c) use ($genero_estudante){
                $g = is_array($c) ? @$c['genero'] : (@$c->genero);
                return $g === $genero_estudante;
            })), $usados);
            if($mesmo){
                return $mesmo;
            }
        }
        return $this->primeiroLivre($candidatos, $usados);
    }
    protected function textoMotivos($m){
        $t = [];
        if(!empty($m['ja_data'])){
            $t[] = $m['ja_data'] . ' já com parte na reunião';
        }
        if(!empty($m['intervalo'])){
            $t[] = $m['intervalo'] . ' em intervalo';
        }
        if(!empty($m['repetidos'])){
            $t[] = $m['repetidos'] . ' repetiram há menos de 4 sem';
        }
        if(!empty($m['ocupados'])){
            $t[] = $m['ocupados'] . ' ocupados na semana';
        }
        return $t ? ' (' . implode(', ', $t) . ')' : '';
    }
    protected function nomeParte($id_designacao){
        $n = Qlib::buscaValorDb0('tags', 'id', (int)$id_designacao, 'nome');
        return $n ? $n : ('parte ' . $id_designacao);
    }
    /**
     * Nomes das partes que nunca pedem ajudante (comparação sem acento).
     * @return array<string>
     */
    public static function nomesSemAjudante(){
        return ['discurso', 'indicador de auditorio', 'indicador externo', 'palco'];
    }
    /**
     * Normaliza nome p/ comparação (minúsculo + sem acento).
     */
    protected static function normalizaNomeParte($nome){
        $n = mb_strtolower(trim((string)$nome), 'UTF-8');
        $map = ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c'];
        $n = strtr($n, $map);
        $n = preg_replace('/\s+/', ' ', $n);
        return trim($n);
    }
    /**
     * IDs das partes que nunca pedem ajudante (ex.: Discurso, Indicador de
     * auditório/externo, Palco).
     * Resolve por nome da Tag para não depender do ID numérico (que varia
     * por ambiente/seed). Mantém 13 como fallback (ID histórico do Discurso).
     * @return array<int>
     */
    public static function idsSemAjudante(){
        static $cache = null;
        if(is_array($cache)){
            return $cache;
        }
        $ids = [13];
        try {
            $alvos = self::nomesSemAjudante();
            $rows = Tag::select('id', 'nome')->get();
            foreach($rows as $r){
                $nid = (int)(is_array($r) ? @$r['id'] : @$r->id);
                $nnome = is_array($r) ? @$r['nome'] : @$r->nome;
                if($nid > 0 && in_array(self::normalizaNomeParte($nnome), $alvos, true) && !in_array($nid, $ids, true)){
                    $ids[] = $nid;
                }
            }
        } catch (\Throwable $e) {
            // Mantém fallback [13]
        }
        $cache = $ids;
        return $cache;
    }
    /**
     * Verifica se a parte não pode ter ajudante (ex.: Discurso, mecânicas).
     * @param int|string $id_designacao
     */
    public static function parteSemAjudante($id_designacao){
        return in_array((int)$id_designacao, self::idsSemAjudante(), true);
    }
    /**
     * Sincroniza data_ultima/token_ultima dos designados (igual ao save manual).
     */
    protected function atualizaUltima($upd, $data, $token){
        foreach(['id_designado', 'id_ajudante'] as $campo){
            if(!empty($upd[$campo])){
                $atual = Qlib::buscaValorDb0('publicadores', 'id', (int)$upd[$campo], 'data_ultima');
                if(!$atual || $data > $atual){
                    \App\Models\Publicador::where('id', '=', (int)$upd[$campo])->update([
                        'data_ultima' => $data,
                        'token_ultima' => $token,
                    ]);
                }
            }
        }
    }
    /**
     * Valida troca rápida entre dois participantes: cada um precisa aceitar
     * a parte do outro (config.designacao.aceita). Leve: 2 reads + 2 nomes.
     */
    public function validar_troca(Request $request){
        $ret = ['exec' => false, 'mens' => ''];
        $a_pub = (int)$request->get('a_pub');
        $a_parte = (int)$request->get('a_parte');
        $b_pub = (int)$request->get('b_pub');
        $b_parte = (int)$request->get('b_parte');
        if(!$a_pub || !$a_parte || !$b_pub || !$b_parte){
            $ret['mens'] = 'Dados incompletos para validar a troca';
            return $ret;
        }
        if($a_pub === $b_pub){
            $ret['mens'] = 'Selecione outro participante';
            return $ret;
        }
        try {
            $pa = Publicador::find($a_pub);
            $pb = Publicador::find($b_pub);
            if(!$pa || !$pb){
                $ret['mens'] = 'Participante não encontrado';
                return $ret;
            }
            $cfgA = is_array(@$pa->config) ? $pa->config : [];
            $cfgB = is_array(@$pb->config) ? $pb->config : [];
            $aa = isset($cfgA['designacao']['aceita']) && is_array($cfgA['designacao']['aceita']) ? array_map('strval', $cfgA['designacao']['aceita']) : [];
            $ab = isset($cfgB['designacao']['aceita']) && is_array($cfgB['designacao']['aceita']) ? array_map('strval', $cfgB['designacao']['aceita']) : [];
            $falta = [];
            if(!in_array((string)$b_parte, $aa)){
                $falta[] = $pa->nome . ' não aceita ' . $this->nomeParte($b_parte);
            }
            if(!in_array((string)$a_parte, $ab)){
                $falta[] = $pb->nome . ' não aceita ' . $this->nomeParte($a_parte);
            }
            if($falta){
                $ret['mens'] = implode('; ', $falta);
                return $ret;
            }
            $ret['exec'] = true;
            $ret['mens'] = 'Troca liberada';
        } catch (\Throwable $th) {
            $ret['mens'] = $th->getMessage();
        }
        return $ret;
    }
    /**
     * Metodo para gerar um link whatsapp da desiganção para ser colocado na tag a
     * @param int $id da parte
     */
    public function link_whatsapp($id){
        $ret = false;
        if($id){
            $dp = designation::select(
                'designations.data',
                'designations.numero',
                'designations.id_designado',
                'designations.id_ajudante',
                'designations.obs',
                'publicadores.nome','publicadores.config'
            )->join('publicadores', 'publicadores.id', '=','designations.id_designado')
            ->where('designations.id','=',$id)
            ->get();
            if($dp->count() > 0){
                $dp = $dp[0]->toArray();
                $arr_config = Qlib::lib_json_array($dp['config']);
                $nome = $dp['nome'];
                $numero = $dp['numero'];
                $obs = $dp['obs'];
                $obs1 = '*observação para o estudante* A lição e a fonte de matéria para a sua designação estão na Apostila da Reunião vida e Ministério. Veja as instruções para a parte que estão nas Instruções para a Reunão Nossa Vida e Ministério Cristão (S-38)';
                $semana = Qlib::dataExtensso($dp['data']);
                $local = 'Salão principal';
                $ajudante = Qlib::buscaValorDb0('publicadores','id',$dp['id_ajudante'],'nome');
                $celular_zap = '';
                if(isset($arr_config['ddi']) && !empty($arr_config['ddi']) && isset($arr_config['telefonezap']) && !empty($arr_config['telefonezap'])){
                    $celular_zap = $arr_config['ddi'].$arr_config['telefonezap'];
                    $celular_zap = str_replace('(', '', $celular_zap);
                    $celular_zap = str_replace(')', '', $celular_zap);
                    $celular_zap = str_replace('-', '', $celular_zap);
                }
                $espa = '%20';
                $enter = '%0A';
                $traco = '-----------';
                $titulo = '*'.__('DESIGNAÇÃO PARA A REUNIÃO NOSSA VIDA E MINISTÉRIO CRISTÃO').'*'.$enter.$traco;
                $tema ="{link_zap}&text=$titulo$enter*Nome:*$espa$nome$enter";
                if($ajudante){
                    $tema .="*Ajudante:*$espa$ajudante$enter";
                }
                $tema .="*Semana:*$espa$semana$enter";
                $tema .="*Número:*$espa$numero$enter";
                $tema .=$obs.$enter;
                $tema .=$obs1;

                $link_zap = 'https://api.whatsapp.com/send?phone='.$celular_zap;
				$ret = str_replace('{link_zap}',$link_zap,$tema);
                // dd($dp);
            }
        }
        return $ret;
    }
    /**Metogo para gerar um link ajax do link_whatsapp
     * @param int $id da parte
     */
    public function link_zap(Request $request){
        $ret['exec'] = false;
        $ret['link'] = false;
        if($request->has('id')){
            $ret['link'] = $this->link_whatsapp($request->get('id'));
            if($ret['link']){
                $ret['exec'] = true;
            }
        }
        return response()->json($ret);;
    }
    /**
     * metodo para pegar desiganções de fim de semana
     *
     */
    public function get_partes_fim_semana(){
        $tipos_designacao_fim = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1' AND config LIKE '%\"post_type\":\"fim-semana\"%'",'nome','id');
        return $tipos_designacao_fim;
    }
    /**
     * Metodo para exibir opçoes d
     */
    public function arr_dias_fim_semana($val=false){
        $arr = ['s'=>'Sábado','d'=>'Domingo'];
        if($val){
            return $arr[$val];
        }
        return $arr;

    }
}
