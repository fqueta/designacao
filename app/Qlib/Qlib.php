<?php
namespace App\Qlib;

use App\Http\Controllers\admin\EventController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;
use App\Models\Qoption;
use Illuminate\Support\Facades\Config;
use DateTime;

class Qlib
{
    static public function lib_print($data){
      if(is_array($data) || is_object($data)){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
      }else{
        echo $data;
      }
    }
    /**
     * Verifica se o usuario logado tem permissao de admin ou alguma expessífica
     */
    static function isAdmin($perm_admin = 2)
    {
        $user = Auth::user();

        if($user->id_permission<=$perm_admin){
            return true;
        }else{
            return false;
        }
    }
    static function dataBanco(){
        global $dtBanco;
        $dtBanco = date('Y-m-d H:i:s', time());
        return $dtBanco;
    }
    static public function qoption($valor = false, $type = false){
        //type é o tipo de respsta
		$ret = false;
		if($valor){
			//if($valor=='dominio_site'){
			//	$ret = dominio();
			//}elseif($valor==''){
			//	$ret = dominio().'/admin';
			//}else{
				//$sql = "SELECT valor FROM qoptions WHERE url = '$valor' AND ativo='s' AND excluido='n' AND deletado='n'";

                //$result = Qlib::dados_tab('qoptions',['sql'=>$sql]);
                $result = Qoption::where('url','=',$valor)->
                where('ativo','=','s')->
                where('excluido','=','n')->
                where('deletado','=','n')->
                select('valor')->
                get();

				if(isset($result[0]->valor)) {
						// output data of each row
						$ret = $result[0]->valor;
						if($valor=='urlroot'){
							$ret = str_replace('/home/ctloja/public_html/lojas/','/home/ctdelive/lojas/',$ret);
						}
                        if($type=='array'){
                            $ret = Qlib::lib_json_array($ret);
                        }
                        if($type=='json'){
                            $ret = Qlib::lib_array_json($ret);
                        }
				}
			//}
		}
		return $ret;
	}
    /**
     * Metodo para atualizar uma configuração
     * @param string $f é campo de configuração e $v = valor assumido pelo campo
     * @return array $ret
     */
    static function update_option($f,$v){
        $ret['exec'] = false;
        $ret['mens'] = false;
        if($f & $v){
            try {
                if(is_array($v)){
                    $v = self::lib_array_json($v);
                }
                $tab = 'qoptions';
                //verificar se essa configuração ja existe
                $verf = Qlib::totalReg($tab,"WHERE url='$f' AND excluido='n' AND deletado='n'");
                if($verf){
                    //atualizar se existe
                    $exec=DB::table($tab)->where('url',$f)->update([
                        'valor'=>$v,
                        'updated_at'=>self::dataBanco(),
                    ]);
                }else{
                    //criar se não existe
                    $exec=DB::table($tab)->insert([
                        'url'=>$f,
                        'valor'=>$v,
                        'created_at'=>self::dataBanco(),
                        'token'=>uniqid(),
                    ]);
                }
                $ret['exec'] = $exec;
                $ret['mens'] = 'Salvo com sucesso';
            } catch (\Throwable $e) {
                $ret['mens'] = $e->getMessage();
            }
        }
        return $ret;
    }
    static function dtBanco($data) {
			$data = trim($data);
			if (strlen($data) != 10)
			{
				$rs = false;
			}
			else
			{
				$arr_data = explode("/",$data);
				$data_banco = $arr_data[2]."-".$arr_data[1]."-".$arr_data[0];
				$rs = $data_banco;
			}
			return $rs;
	}
  static function dataExibe($data=false) {
        $rs=false;
        if($data){
           $val = trim(strlen($data));
			$data = trim($data);$rs = false;
			if($val == 10){
					$arr_data = explode("-",$data);
					$data_banco = @$arr_data[2]."/".@$arr_data[1]."/".@$arr_data[0];
					$rs = $data_banco;
			}
			if($val == 19){
					$arr_inic = explode(" ",$data);
					$arr_data = explode("-",$arr_inic[0]);
					$data_banco = $arr_data[2]."/".$arr_data[1]."/".$arr_data[0];
					$rs = $data_banco."-".$arr_inic[1] ;
			}
        }

			return $rs;
	}
  static function lib_json_array($json=''){
		$ret = false;
		if(is_array($json)){
			$ret = $json;
		}elseif(!empty($json) && Qlib::isJson($json)&&!is_array($json)){
			$ret = json_decode($json,true);
		}
		return $ret;
	}
	public static function lib_array_json($json=''){
		$ret = false;
		if(is_array($json)){
			$ret = json_encode($json,JSON_UNESCAPED_UNICODE);
		}
		return $ret;
	}
    static function precoBanco($preco){
            $sp = substr($preco,-3,-2);
            if($sp=='.'){
                $preco_venda1 = $preco;
            }else{
                $preco_venda1 = str_replace(".", "", $preco);
                $preco_venda1 = str_replace(",", ".", $preco_venda1);
            }
            return $preco_venda1;
    }
    static function isJson($string) {
		$ret=false;
		if (is_object(json_decode($string)) || is_array(json_decode($string)))
		{
			$ret=true;
		}
		return $ret;
	}
  static function Meses($val=false){
  		$mese = array('01'=>'JANEIRO','02'=>'FEVEREIRO','03'=>'MARÇO','04'=>'ABRIL','05'=>'MAIO','06'=>'JUNHO','07'=>'JULHO','08'=>'AGOSTO','09'=>'SETEMBRO','10'=>'OUTUBRO','11'=>'NOVEMBRO','12'=>'DEZEMBRO');
  		if($val){
  			return $mese[$val];
  		}else{
  			return $mese;
  		}
	}
  static function totalReg($tabela, $condicao = false,$debug=false){
			//necessario
			$sql = "SELECT COUNT(*) AS totalreg FROM {$tabela} $condicao";
			if($debug)
				 echo $sql.'<br>';
			//return $sql;
			$td_registros = DB::select($sql);
			if(isset($td_registros[0]->totalreg) && $td_registros[0]->totalreg > 0){
				return $td_registros[0]->totalreg;
			}else
				return 0;
	}
  static function zerofill( $number ,$nroDigo=6, $zeros = null ){
		$string = sprintf( '%%0%ds' , is_null( $zeros ) ?  $nroDigo : $zeros );
		return sprintf( $string , $number );
	}
  static function encodeArray($arr){
			$ret = false;
			if(is_array($arr)){
				$ret = base64_encode(json_encode($arr));
			}
			return $ret;
	}
  static function decodeArray($arr){
			$ret = false;
			if($arr){
				//$ret = base64_encode(json_encode($arr));
                $ret = base64_decode((string)$arr);
                $ret = json_decode($ret,true);

			}
			return $ret;
	}
    static function qForm($config=false){
        if(isset($config['type'])){
            $config['campo'] = isset($config['campo'])?$config['campo']:'teste';
            $config['label'] = isset($config['label'])?$config['label']:false;
            $config['placeholder'] = isset($config['placeholder'])?$config['placeholder']:false;
            $config['selected'] = isset($config['selected']) ? $config['selected']:false;
            $config['tam'] = isset($config['tam']) ? $config['tam']:'12';
            $config['col'] = isset($config['col']) ? $config['col']:'md';
            $config['event'] = isset($config['event']) ? $config['event']:false;
            $config['ac'] = isset($config['ac']) ? $config['ac']:'cad';
            $config['option_select'] = isset($config['option_select']) ? $config['option_select']:true;
            $config['label_option_select'] = isset($config['label_option_select']) ? $config['label_option_select']:'Selecione';
            $config['option_gerente'] = isset($config['option_gerente']) ? $config['option_gerente']:false;
            $config['class'] = isset($config['class']) ? $config['class'] : false;
            $config['style'] = isset($config['style']) ? $config['style'] : false;
            $config['class_div'] = isset($config['class_div']) ? $config['class_div'] : false;
            if(@$config['type']=='chave_checkbox' && @$config['ac']=='cad'){
                if(@$config['checked'] == null && isset($config['valor_padrao']))
                $config['checked'] = $config['valor_padrao'];
            }
            //if($config['type']=='select_multiple'){
                //dd($config);
            //}
            if(@$config['type']=='html_vinculo' && @$config['ac']=='alt'){
                $tab = $config['data_selector']['tab'];
                $config['data_selector']['placeholder'] = isset($config['data_selector']['placeholder'])?$config['data_selector']['placeholder']:'Digite para iniciar a consulta...';
                $dsel = $config['data_selector'];
                $id = $config['value'];
                if(@$dsel['tipo']=='array'){
                    if(is_array($id)){
                        foreach ($id as $ki => $vi) {
                            $config['data_selector']['list'][$ki] = Qlib::dados_tab($tab,['id'=>$vi]);
                            if($config['data_selector']['list'][$ki] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                                foreach ($config['data_selector']['table'] as $key => $v) {
                                    if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$ki][$key]) && isset($v['conf_sql'])){
                                        $config['data_selector']['list'][$ki][$key.'_valor'] = Qlib::buscaValorDb([
                                            'tab'=>$v['conf_sql']['tab'],
                                            'campo_bus'=>$v['conf_sql']['campo_bus'],
                                            'select'=>$v['conf_sql']['select'],
                                            'valor'=>$config['data_selector']['list'][$ki][$key],
                                        ]);
                                    }
                                }
                            }
                        }
                        //dd($config['data_selector']);
                    }
                }else{
                    $config['data_selector']['list'] = Qlib::dados_tab($tab,['id'=>$id]);
                    if($config['data_selector']['list'] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                        foreach ($config['data_selector']['table'] as $key => $v) {
                            if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$key]) && isset($v['conf_sql'])){
                                $config['data_selector']['list'][$key.'_valor'] = Qlib::buscaValorDb([
                                    'tab'=>$v['conf_sql']['tab'],
                                    'campo_bus'=>$v['conf_sql']['campo_bus'],
                                    'select'=>$v['conf_sql']['select'],
                                    'valor'=>$config['data_selector']['list'][$key],
                                ]);
                            }
                        }
                        //dd($config);
                    }
                }
            }
            return view('qlib.campos_form',['config'=>$config]);
        }else{
            return false;
        }
    }
    static function qShow($config=false){
        if(isset($config['type'])){
            $config['campo'] = isset($config['campo'])?$config['campo']:'teste';
            $config['label'] = isset($config['label'])?$config['label']:false;
            $config['placeholder'] = isset($config['placeholder'])?$config['placeholder']:false;
            $config['selected'] = isset($config['selected']) ? $config['selected']:false;
            $config['tam'] = isset($config['tam']) ? $config['tam']:'12';
            $config['col'] = isset($config['col']) ? $config['col']:'md';
            $config['event'] = isset($config['event']) ? $config['event']:false;
            $config['ac'] = isset($config['ac']) ? $config['ac']:'cad';
            $config['option_select'] = isset($config['option_select']) ? $config['option_select']:true;
            $config['label_option_select'] = isset($config['label_option_select']) ? $config['label_option_select']:'Selecione';
            $config['option_gerente'] = isset($config['option_gerente']) ? $config['option_gerente']:false;
            $config['class'] = isset($config['class']) ? $config['class'] : false;
            $config['style'] = isset($config['style']) ? $config['style'] : false;
            $config['class_div'] = isset($config['class_div']) ? $config['class_div'] : false;
            if(@$config['type']=='chave_checkbox' && @$config['ac']=='cad'){
                if(@$config['checked'] == null && isset($config['valor_padrao']))
                $config['checked'] = $config['valor_padrao'];
            }
            if(@$config['type']=='html_vinculo' && @$config['ac']=='alt'){
                $tab = $config['data_selector']['tab'];
                $config['data_selector']['placeholder'] = isset($config['data_selector']['placeholder'])?$config['data_selector']['placeholder']:'Digite para iniciar a consulta...';
                $dsel = $config['data_selector'];
                $id = $config['value'];
                if(@$dsel['tipo']=='array'){
                    if(is_array($id)){
                        foreach ($id as $ki => $vi) {
                            $config['data_selector']['list'][$ki] = Qlib::dados_tab($tab,['id'=>$vi]);
                            if($config['data_selector']['list'][$ki] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                                foreach ($config['data_selector']['table'] as $key => $v) {
                                    if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$ki][$key]) && isset($v['conf_sql'])){
                                        $value = $config['data_selector']['list'][$ki][$key];
                                        $config['data_selector']['list'][$ki][$key.'_valor'] = Qlib::buscaValorDb([
                                            'tab'=>$v['conf_sql']['tab'],
                                            'campo_bus'=>$v['conf_sql']['campo_bus'],
                                            'select'=>$v['conf_sql']['select'],
                                            'valor'=>$value,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }else{
                    $config['data_selector']['list'] = Qlib::dados_tab($tab,['id'=>$id]);
                    if($config['data_selector']['list'] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                        foreach ($config['data_selector']['table'] as $key => $v) {
                            if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$key]) && isset($v['conf_sql'])){
                                $config['data_selector']['list'][$key.'_valor'] = Qlib::buscaValorDb([
                                    'tab'=>$v['conf_sql']['tab'],
                                    'campo_bus'=>$v['conf_sql']['campo_bus'],
                                    'select'=>$v['conf_sql']['select'],
                                    'valor'=>$config['data_selector']['list'][$key],
                                ]);
                            }
                        }
                        //dd($config);
                    }
                }
            }
            return view('qlib.campos_show',['config'=>$config]);
        }else{
            return false;
        }
    }
    static function sql_array($sql, $ind, $ind_2, $ind_3 = '', $leg = '',$type=false){
        $table = DB::select($sql);
        $userinfo = array();
        if($table){
            //dd($table);
            for($i = 0;$i < count($table);$i++){
                $table[$i] = (array)$table[$i];
                if($ind_3 == ''){
                    $userinfo[$table[$i][$ind_2]] =  $table[$i][$ind];
                }elseif(is_array($ind_3) && isset($ind_3['tab'])){
                    /*É sinal que o valor vira de banco de dados*/
                    $sql = "SELECT ".$ind_3['campo_enc']." FROM `".$ind_3['tab']."` WHERE ".$ind_3['campo_bus']." = '".$table[$i][$ind_2]."'";
                    $userinfo[$table[$i][$ind_2]] = $sql;
                }else{
                    if($type){
                        if($type == 'data'){
                            /*Tipo de campo exibe*/
                            $userinfo[$table[$i][$ind_2]] = $table[$i][$ind] . '' . $leg . '' . Qlib::dataExibe($table[$i][$ind_3]);
                        }
                    }else{
                        $userinfo[$table[$i][$ind_2]] = $table[$i][$ind] . '' . $leg . '' . $table[$i][$ind_3];
                    }
                }
            }
        }

        return $userinfo;
    }
    static function sql_distinct($tab='familias',$campo='YEAR(`data_exec`)',$order='ORDER BY data_exec ASC'){
        $ret = DB::select("SELECT DISTINCT $campo As vl  FROM $tab $order");
        return $ret;
    }
    static function formatMensagem($config=false){
        if($config){
            $config['mens'] = isset($config['mens']) ? $config['mens'] : false;
            $config['color'] = isset($config['color']) ? $config['color'] : false;
            $config['time'] = isset($config['time']) ? $config['time'] : 4000;
            return view('qlib.format_mensagem', ['config'=>$config]);
        }else{
            return false;
        }
	}
    static function formatMensagemInfo($mess='',$cssMes='',$event=false){
		$mensagem = "<div class=\"alert alert-$cssMes alert-dismissable\" role=\"alert\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" $event aria-hidden=\"true\">×</button><i class=\"fa fa-info-circle\"></i>&nbsp;".__($mess)."</div>";
		return $mensagem;
	}
    static function gerUploadAquivos($config=false){
        if($config){
            $config['parte'] = isset($config['parte']) ? $config['parte'] : 'painel';
            $config['token_produto'] = isset($config['token_produto']) ? $config['token_produto'] : false;
            $config['listFiles'] = isset($config['listFiles']) ? $config['listFiles'] : false; // array com a lista
            $config['time'] = isset($config['time']) ? $config['time'] : 4000;
            $config['arquivos'] = isset($config['arquivos']) ? $config['arquivos'] : false;
            if($config['listFiles']){
                $tipo = false;
                foreach ($config['listFiles'] as $key => $value) {
                    if(isset($value['config'])){
                        $arr_conf = Qlib::lib_json_array($value['config']);
                        if(isset($arr_conf['extenssao']) && !empty($arr_conf['extenssao']))
                        {
                            if($arr_conf['extenssao'] == 'jpg' || $arr_conf['extenssao']=='png' || $arr_conf['extenssao'] == 'jpeg'){
                                $tipo = 'image';
                            }elseif($arr_conf['extenssao'] == 'doc' || $arr_conf['extenssao'] == 'docx') {
                                $tipo = 'word';
                            }elseif($arr_conf['extenssao'] == 'xls' || $arr_conf['extenssao'] == 'xlsx') {
                                $tipo = 'excel';
                            }else{
                                $tipo = 'download';
                            }
                        }
                        $config['listFiles'][$key]['tipo_icon'] = $tipo;
                    }
                }
            }
            if(isset($config['parte'])){
                $view = 'qlib.uploads.painel';
                return view($view, ['config'=>$config]);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function formulario($config=false){
        if($config['campos']){
            $view = 'qlib.formulario';
            return view($view, ['conf'=>$config]);
        }else{
            return false;
        }
    }
    static function show($config=false){
        if($config['campos']){
            $view = 'qlib.show';
            return view($view, ['conf'=>$config]);
        }else{
            return false;
        }
    }
    static function listaTabela($config=false){
        if($config['campos_tabela']){
            $view = 'qlib.listaTabela';
            return view($view, ['conf'=>$config]);
        }else{
            return false;
        }
    }
    static function UrlAtual(){
        return URL::full();
    }
    static function get_subdominio(){
        $ret = false;
        // $url = explode('?',self::UrlAtual());
        $url = request()->getHost();
        // $partesUrl = explode('.',$url[0]);
        $partesUrl = explode('.',$url);
        // $total = count($partesUrl);
        if(isset($partesUrl[0])){
            //$partHost = explode('.',$_SERVER["HTTP_HOST"]);
            $ret = $partesUrl[0];
        }
        return $ret;
    }
    static function ver_PermAdmin($perm=false,$url=false){
        $ret = false;
        // dump(DB::getDefaultConnection());
        // dump(Auth::check(),Auth::user());

        if(!$url){
            $url = URL::current();
            $arr_url = explode('/',$url);
        }
        if($url && $perm){
            $arr_permissions = [];
            $logado = Auth::user();
            $id_permission = isset($logado->id_permission)?$logado->id_permission:null;
            $dPermission = Permission::findOrFail($id_permission);
            if($dPermission && $dPermission->active=='s'){
                $arr_permissions = Qlib::lib_json_array($dPermission->id_menu);
                if(isset($arr_permissions[$perm][$url])){
                    $ret = true;
                }
            }
        }
        return $ret;
    }
    static public function html_vinculo($config = null)
    {
        /**
        Qlib::html_vinculo([
            'campos'=>'',
            'type'=>'html_vinculo',
            'dados'=>'',
        ]);
         */

        $ret = false;
        $campos = isset($config['campos'])?$config['campos']:false;
        $type = isset($config['type'])?$config['type']:false;
        $dados = isset($config['dados'])?$config['dados']:false;
        if(!$campos)
            return $ret;
        if(is_array($campos) && $dados){
            foreach ($campos as $key => $value) {
                if($value['type']==$type){
                    $id = $dados[$key];
                    $tab = $value['data_selector']['tab'];
                    $d_tab = DB::table($tab)->find($id);
                    if($d_tab){
                        $ret[$key] = (array)$d_tab;
                    }
                }
            }
        }
        return $ret;
    }
    static public function dados_tab($tab = null,$config)
    {
        $ret = false;
        if($tab){
            $id = isset($config['id']) ? $config['id']:false;
            $sql = isset($config['sql']) ? $config['sql']:false;
            if($sql){
                $d = DB::select($sql);
                $arr_list = $d;
                $list = false;
                foreach ($arr_list as $k => $v) {
                    if(is_object($v)){
                        $list[$k] = (array)$v;
                        foreach ($list[$k] as $k1 => $v1) {
                            if(Qlib::isJson($v1)){
                                $list[$k][$k1] = Qlib::lib_json_array($v1);
                            }
                        }
                    }
                }
                $ret = $list;
                return $ret;
            }else{
                $obj_list = DB::table($tab)->find($id);
            }
            if($list=(array)$obj_list){
                //dd($obj_list);
                    if(is_array($list)){
                        foreach ($list as $k => $v) {
                            if(Qlib::isJson($v)){
                                $list[$k] = Qlib::lib_json_array($v);
                            }
                        }
                    }
                    $ret = $list;
            }
        }
        return $ret;
    }
    static public function buscaValorDb($config = false)
    {
        /*Qlib::buscaValorDd([
            'tab'=>'',
            'campo_bus'=>'',
            'valor'=>'',
            'select'=>'',
            'compleSql'=>'',
        ]);
        */
        $ret=false;
        $tab = isset($config['tab'])?$config['tab']:false;
        $campo_bus = isset($config['campo_bus'])?$config['campo_bus']:'id';//campo select
        $valor = isset($config['valor'])?$config['valor']:false;
        $select = isset($config['select'])?$config['select']:false; //
        $compleSql = isset($config['compleSql'])?$config['compleSql']:false; //
        if($tab && $campo_bus && $valor && $select){
            $sql = "SELECT $select FROM $tab WHERE $campo_bus='$valor' $compleSql";
            if(isset($config['debug'])&&$config['debug']){
                echo $sql;
            }
            $d = DB::select($sql);
            if($d)
                $ret = $d[0]->$select;
        }
        return $ret;
    }
    static public function buscaValorDb0($tab,$campo_bus,$valor,$select,$compleSql=false,$debug=false)
    {
        $ret = false;
        if($tab && $campo_bus && $valor && $select){
            $sql = "SELECT $select FROM $tab WHERE $campo_bus='$valor' $compleSql";
            if(isset($debug)&&$debug){
                echo $sql;
            }
            $d = DB::select($sql);
            if($d)
                $ret = $d[0]->$select;
        }
        return $ret;
    }
    static public function valorTabDb($tab = false,$campo_bus,$valor,$select,$compleSql=false)
    {

        $ret=false;
        /*
        $tab = isset($config['tab'])?$config['tab']:false;
        $campo_bus = isset($config['campo_bus'])?$config['campo_bus']:'id';//campo select
        $valor = isset($config['valor'])?$config['valor']:false;
        $select = isset($config['select'])?$config['select']:false; //
        $compleSql = isset($config['compleSql'])?$config['compleSql']:false; //
        */
        if($tab && $campo_bus && $valor && $select){
            $sql = "SELECT $select FROM $tab WHERE $campo_bus='$valor' $compleSql";
            if(isset($config['debug'])&&$config['debug']){
                echo $sql;
            }
            $d = DB::select($sql);
            if($d)
                $ret = $d[0]->$select;
        }
        return $ret;
    }
    static function lib_valorPorExtenso($valor=0) {
		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");

		$c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
		$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
		$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
		$u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");

		$z=0;

		$valor = @number_format($valor, 2, ".", ".");
		$inteiro = explode(".", $valor);
		for($i=0;$i<count($inteiro);$i++)
			for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
				$inteiro[$i] = "0".$inteiro[$i];

		// $fim identifica onde que deve se dar junção de centenas por "e" ou por "," 😉
		$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
		$rt=false;
		for ($i=0;$i<count($inteiro);$i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
			if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
		}
		return($rt ? $rt : "zero");
	}
	static function convert_number_to_words($number) {

		$hyphen      = '-';
		$conjunction = ' e ';
		$separator   = ', ';
		$negative    = 'menos ';
		$decimal     = ' ponto ';
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'um',
			2                   => 'dois',
			3                   => 'três',
			4                   => 'quatro',
			5                   => 'cinco',
			6                   => 'seis',
			7                   => 'sete',
			8                   => 'oito',
			9                   => 'nove',
			10                  => 'dez',
			11                  => 'onze',
			12                  => 'doze',
			13                  => 'treze',
			14                  => 'quatorze',
			15                  => 'quinze',
			16                  => 'dezesseis',
			17                  => 'dezessete',
			18                  => 'dezoito',
			19                  => 'dezenove',
			20                  => 'vinte',
			30                  => 'trinta',
			40                  => 'quarenta',
			50                  => 'cinquenta',
			60                  => 'sessenta',
			70                  => 'setenta',
			80                  => 'oitenta',
			90                  => 'noventa',
			100                 => 'cento',
			200                 => 'duzentos',
			300                 => 'trezentos',
			400                 => 'quatrocentos',
			500                 => 'quinhentos',
			600                 => 'seiscentos',
			700                 => 'setecentos',
			800                 => 'oitocentos',
			900                 => 'novecentos',
			1000                => 'mil',
			1000000             => array('milhão', 'milhões'),
			1000000000          => array('bilhão', 'bilhões'),
			1000000000000       => array('trilhão', 'trilhões'),
			1000000000000000    => array('quatrilhão', 'quatrilhões'),
			1000000000000000000 => array('quinquilhão', 'quinquilhões')
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'convert_number_to_words só aceita números entre ' . PHP_INT_MAX . ' à ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . Qlib::convert_number_to_words(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}
        $number = (int)$number;
		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $conjunction . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = floor($number / 100)*100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds];
				if ($remainder) {
					$string .= $conjunction . Qlib::convert_number_to_words($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				if ($baseUnit == 1000) {
					$string = Qlib::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[1000];
				} elseif ($numBaseUnits == 1) {
					$string = Qlib::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit][0];
				} else {
					$string = Qlib::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit][1];
				}
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= Qlib::convert_number_to_words($remainder);
				}
				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}

		return $string;
	}
    static function limpar_texto($str){
        return preg_replace("/[^0-9]/", "", $str);
    }
    static function compleDelete($var = null)
    {
        if($var){
            return "$var.excluido='n' AND $var.deletado='n'";
        }else{
            return "excluido='n' AND deletado='n'";
        }
    }
    static public function show_files(Array $config = null)
    {
        $ret = Qlib::formatMensagemInfo('Nenhum Arquivo','info');

        if($config['token']){
            $files = DB::table('_uploads')->where('token_produto',$config['token'])->get();
            if($files){
                if(isset($files[0]))
                    return view('qlib.show_file',['files'=>$files,'config'=>$config]);
            }
        }
        return $ret;
    }
    /***
     * Busca um tipo de routa padrão do sistema
     * Ex.: routa que será aberta ao logar
     *
     */
    static function redirectLogin($ambiente='back')
    {
        $ret = '/';
        if(!Auth::check()){
            return $ret;
        }
        $id_permission = auth()->user()->id_permission;
        $dPermission = Permission::FindOrFail($id_permission);
        $ret = isset($dPermission['redirect_login']) ? $dPermission['redirect_login']:'/';
        return $ret;
    }
    static function redirect($url,$time=10){
        echo '<meta http-equiv="refresh" content="'.$time.'; url='.$url.'">';
    }
    static function verificaCobranca(){
        //$f = new CobrancaController;
        $user = Auth::user();
        $f = new UserController($user);
        $ret = $f->exec();
        return $ret;
    }
    static public function is_base64($str){
        try
        {
            $decoded = base64_decode($str, true);

            if ( base64_encode($decoded) === $str ) {
                return true;
            }
            else {
                return false;
            }
        }
        catch(Exception $e)
        {
            // If exception is caught, then it is not a base64 encoded string
            return false;
        }
    }
    static function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    /**
     * Registra eventos no sistema
     * @return bool;
     */
    static function regEvent($config=false)
    {
        //return true;
        $ret = (new EventController)->regEvent($config);
        return $ret;
    }
    static public function anoTeocratico(){
        $ano = date('Y');
        $mes = date('m');
        $mes = (int)$mes;
        if($mes == 1){
            $mes = 12;
            $ano = date('Y') - 1;
        }else{
            $mes--;
        }
        if($mes>8){
            $ano = date('Y')+1;
        }
        return ['ano'=>$ano,'mes'=>$mes];
    }
    public static function semana_do_ano($dia,$mes,$ano){
        $var=intval( date('z', mktime(0,0,0,$mes,$dia,$ano) ) / 7 ) + 1;
        //$var = self::zerofill($var,2);
        return $var;
    }
    public static function carregaArquivo($arquivo=false){
        $string = false;
        if($arquivo){
            $filename = $arquivo;
            //if (file_exists($filename)) {
                $string = @file_get_contents($filename);
            //} else {
                //$string = formatMensagem("O arquivo $filename não existe",'danger',60000);
            //}
        }
        return $string;
    }
    public static function getMondaysInRange($dateFromString, $dateToString,$format='Y-m-d')
    {
        $dateFrom = new \DateTime($dateFromString);
        $dateTo = new \DateTime($dateToString);
        $dates = [];

        if ($dateFrom > $dateTo) {
            return $dates;
        }

        if (1 != $dateFrom->format('N')) {
            $dateFrom->modify('next monday');
        }

        while ($dateFrom <= $dateTo) {
            $dates[] = $dateFrom->format($format);
            $dateFrom->modify('+1 week');
        }

        return $dates;
    }
    static function getSundays($y,$m){
        $date = "$y-$m-01";
        $first_day = date('N',strtotime($date));
        $first_day = 7 - $first_day + 1;
        $last_day =  date('t',strtotime($date));
        $days = array();
        for($i=$first_day; $i<=$last_day; $i=$i+7 ){
            //$days[] = $i;  //this will give days of sundays
            $days[] = "$y-".self::zerofill($m,2)."-".self::zerofill($i,2);  //dates of sundays
        }
        return  $days;
    }
    static function getSaturdays($year,$month){
        // Cria uma data no primeiro dia do mês
        $startDate = new \DateTime("$year-$month-01");

        // Define o último dia do mês
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        // Cria um intervalo de 1 dia
        $interval = new \DateInterval('P1D');

        // Cria o período de datas
        $datePeriod = new \DatePeriod($startDate, $interval, $endDate);

        // Array para armazenar os sábados
        $saturdays = [];

        // Itera sobre cada data no período
        foreach ($datePeriod as $date) {
            // Verifica se é sábado (6 é o índice do sábado)
            if ($date->format('N') == 6) {
                $saturdays[] = $date->format('Y-m-d');
            }
        }
        // dd($saturdays);
        return $saturdays;
    }
    public static function getAllDaysInAMonth($year, $month, $day = 'Monday', $daysError = 3) {
        $month = (int)$month;
        $dateString = 'first '.$day.' of '.$year.'-'.$month;

        if (!strtotime($dateString)) {
            throw new \Exception('"'.$dateString.'" is not a valid strtotime');
        }

        $startDay = new \DateTime($dateString);

        if ($startDay->format('j') > $daysError) {
            $startDay->modify('- 7 days');
        }

        $days = array();
        $ret = [];
        $p = [];
        $i=0;
        while ($startDay->format('Y-m') <= $year.'-'.str_pad($month, 2, 0, STR_PAD_LEFT)) {
            // $days[] = clone($startDay);
            if($i==0){
                $dt = $startDay;
            }else{
                $dt = $startDay->modify('+ 7 days');
            }
            $p = $dt->format('Y-m-d');
            $d = explode('-', $p);
            if((int)$d['1']==$month){
                $ret[] = $p;
            }
            $i++;
        }
        return $ret;
    }
    public static function getNumberRange($inic,$fim,$r='impar'){
        $ret = array();
        if ($inic && $fim && $r) {
            foreach(range($inic,$fim) as $v){
                if($r=='todos'){
                    $ret[] = $v;
                }else{
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
        }
        return $ret;
    }
    static function arr_month($s_year, $limt=1){
        $year_e = $s_year+$limt;
        $ret = [];
        foreach (range($s_year,$year_e) as $key => $value) {
            $meses = self::getNumberRange(1,12);
            $ml = [];
            if(is_array($meses)){
                foreach ($meses as $km => $vm) {
                    $ml[$vm] = self::getAllDaysInAMonth($value,$vm);
                }
            }
            $ret[$key] = ['ano'=>$value,'meses'=>$ml];
        }
        return $ret;
    }
    static function arr_month2($s_year, $limt=2){
        $year_e = $s_year+$limt;
        $ret = [];
        $local = request()->segment(1);
        $dia = Qlib::qoption('dia_reuniao_fim_semana');// request()->get('dia');
        $dia_reuniao = $dia ? $dia : null;
        if(!$dia_reuniao){
            $dia_reuniao = 1;
        }
        foreach (range($s_year,$year_e) as $key => $value) {
            $meses = self::getNumberRange(1,12,'todos');
            $ml = [];
            if(is_array($meses)){
                foreach ($meses as $km => $vm) {
                    $ml[$vm] = self::getAllDaysInAMonth($value,$vm);
                    if($local=='fim-semana'){
                        if($dia_reuniao=='s'){
                            //sabado
                            $ret[$value][$vm] = self::getSaturdays($value,$vm);
                        }else{
                            //domingo
                            $ret[$value][$vm] = self::getSundays($value,$vm);
                        }
                    }else{
                        $ret[$value][$vm] = self::getAllDaysInAMonth($value,$vm);
                    }
                }
            }
        }
        return $ret;
    }
    public static function dataExtensso($data=false,$tm=false,$formato='Y-m-d'){
        $ret = false;
        $tm = $tm?$tm: '{dia} de {mes}, {ano}';
        if($formato=='Y-m-d'){
            if(strlen($data>10)){
                $dt = explode(" ",$data);
                $d = isset($dt[0])?$dt[0]: false;
                if(!$d){
                    return $data;
                }
                $data = $d;
            }
            $d = explode('-',$data);
            $meses = self::meses();
            if(isset($d[2])){
                $dia = $d[2];
                $mes = $meses[$d[1]];
                $ano = $d[0];
                $ret = str_replace('{dia}',$dia,$tm);
                $ret = str_replace('{mes}',$mes,$ret);
                $ret = str_replace('{ano}',$ano,$ret);
            }
        }
        return $ret;
    }
    /**Mtodo para retornar a strig sql do query bilder do eloquento
     * @param string $query do eloquento sem o meto get no final
     */
    static function eloquentSql($d){
        $query = str_replace(array('?'), array('\'%s\''), $d->toSql());
        $query = vsprintf($query, $d->getBindings());
        dump($query);
        $result = $d->get();
        if($result->count() > 0){
            $result = $result->toArray();
        }
        return $result;
    }
    /**
     * Metodo para retornar numero da semana de uma data aleatora
     * @parmat data $data formato Y-m-d
     * @return string $ret
     */
    static function numero_semana($data){
        $dataActual = $data;
        $dataSegundos = strtotime($dataActual);

        $semana = date('W', $dataSegundos);
        if($semana){
            return (int)$semana;
        }else{
            return false;
        }
    }

    /**
     * Metodo para retornar um link do programa da semana no wol.jw.org
     * @parmat data $data formato Y-m-d, string $html ex.: <a href="{link}">Apostila</a>
     * @return string $ret
     */
    static function link_programacao_woljw($data,$html=false){
        $d = explode("-",$data);
        $ret = '';
        if(isset($d[0]) && strlen($d[0])==4){
            $ano = $d[0];
            $semana = self::numero_semana($data);
            if(isset($d[2])){
                if($d[2]>29 && $semana>50){
                    $ano++;
                }
            }
            $tl = 'https://wol.jw.org/pt/wol/meetings/r5/lp-t/{ano}/{semana}';
            // dump($semana);
            if(!$semana){
                return $ret;
            }
            $ret = str_replace('{ano}',$ano,$tl);
            $ret = str_replace('{semana}',$semana,$ret);
            if($html){
                $ret = str_replace('{link}',$ret,$html);
            }
        }
        return $ret;
    }
    /**
     * Metodo para salvar ou atualizar os meta posts
     */
    static function update_postmeta($post_id,$meta_key=null,$meta_value=null)
    {
        $ret = false;
        $tab = 'postmeta';
        if($post_id&&$meta_key&&$meta_value){
            $verf = Qlib::totalReg($tab,"WHERE post_id='$post_id' AND meta_key='$meta_key'");
            if($verf){
                $ret=DB::table($tab)->where('post_id',$post_id)->where('meta_key',$meta_key)->update([
                    'meta_value'=>$meta_value,
                    'updated_at'=>self::dataBanco(),
                ]);
            }else{
                $ret=DB::table($tab)->insert([
                    'post_id'=>$post_id,
                    'meta_value'=>$meta_value,
                    'meta_key'=>$meta_key,
                    'created_at'=>self::dataBanco(),
                ]);
            }
            //$ret = DB::table($tab)->storeOrUpdate();
        }
        return $ret;
    }
    /**
     * Metodo para pegar os meta posts
     */
    static function get_postmeta($post_id,$meta_key=null,$string=null)
    {
        $ret = false;
        $tab = 'postmeta';
        if($post_id){
            if($meta_key){
                $d = DB::table($tab)->where('post_id',$post_id)->where('meta_key',$meta_key)->get();
                if($d->count()){
                    if($string){
                        $ret = $d[0]->meta_value;
                    }else{
                        $ret = [$d[0]->meta_value];
                    }
                }else{
                    $post_id = self::get_id_by_token($post_id);
                    if($post_id){
                        $ret = self::get_postmeta($post_id,$meta_key,$string);
                    }
                }
            }
        }
        return $ret;
    }
    static function remove_postmeta($post_id,$meta_key){
        $tab = 'postmeta';
        return DB::table($tab)->where('post_id',$post_id)->where('meta_key',$meta_key)->delete();
    }
    /**
     * Metodo buscar o post_id com o token
     * @param string $token
     * @return string $ret;
     */
    static function get_id_by_token($token)
    {
        if($token){
            return Qlib::buscaValorDb0('posts','token',$token,'ID');
        }
    }
    /**
     * Metodo para verificar se uma programção tem assemblei
     * @param int $post_id id do programa $data da semana
     * @return boolean true if successful
     */
    static function tem_congresso($post_id,$data){
        $res = self::get_postmeta($post_id,'congresso',true);
        if($res){
            if(strtotime($res) == strtotime($data)){
                return $res;
            }
        }
        return false;
    }
    /**
     * Metodo para verificar se uma programção tem assemblei
     * @param int $post_id id do programa $data da semana
     * @return boolean true if successful
     */
    static function tem_assembleia($post_id,$data){
        $res = self::get_postmeta($post_id,'assembleia',true);
        if($res){
            if(strtotime($res) == strtotime($data)){
                return $res;
            }
        }
        return false;
    }
    /**
     * Metodo para verificar se uma programção tem visita
     * @param int $post_id id do programa $data da semana
     * @return boolean true if successful
     */
    static function tem_visita($post_id,$data){
        $res = self::get_postmeta($post_id,'visita',true);
        if($res){
            if(strtotime($res) == strtotime($data)){
                return $res;
            }
        }
        return false;
    }
    /**
     * Metodo para retornar o nome do subdominio o vazio caso não seja um subdominio
     */
    static function is_subdominio(){
        $ret = explode('.', request()->getHost())[0];
        return $ret;
    }
    static function selectDefaultConnection($connection='mysql',$conn=false){
        if($connection=='tenant'){
            if(isset($conn['name']) && isset($conn['user']) && isset($conn['pass'])){
                $db = isset($conn['name'])?$conn['name']:false;
                $user = isset($conn['user'])?$conn['user']:false;
                $pass = isset($conn['pass'])?$conn['pass']:false;
                if($user && $db){
                    Config::set('database.connections.tenant.database', trim($db));
                    Config::set('database.connections.tenant.username', trim($user));
                    Config::set('database.connections.tenant.password', trim($pass));
                }
            }else{
                $arr_tenancy = session()->get('tenancy');
                if(isset($arr_tenancy['config']) && Qlib::isJson($arr_tenancy['config'])){
                    $arr_config=Qlib::lib_json_array($arr_tenancy['config']);
                    $db = isset($arr_config['name'])?$arr_config['name']:false;
                    $user = isset($arr_config['user'])?$arr_config['user']:false;
                    $pass = isset($arr_config['pass'])?$arr_config['pass']:false;
                    if($user && $db){
                        Config::set('database.connections.tenant.database', trim($db));
                        Config::set('database.connections.tenant.username', trim($user));
                        Config::set('database.connections.tenant.password', trim($pass));
                    }
                }
            }
            // $clone = config('database.connections.mysql');
            // $clone['database'] = $db;
            // Config::set('database.connections.'.$connection, $clone);

        }
        DB::purge($connection);
        DB::reconnect($connection);
        DB::setDefaultConnection($connection);
        // Config::set('database.default', $connection);
        return DB::getDefaultConnection();

    }
    /**
     * Metodo que verifica se a conexão atual é de um tenant ou não
     */
    static function is_tenant(){
        $conn = DB::getDefaultConnection();
        if($conn == 'tenant'){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Metodo que retorna o dia da reunião de fim de semana
     */
    static function get_reuniao_fim_semana(){
        $ret = '';
        $dia_reuniao = Qlib::qoption('dia_reuniao_fim_semana')?Qlib::qoption('dia_reuniao_fim_semana'):'d';
        if($dia_reuniao=='s'){
            $ret = __('Sábado');
        }else{
            $ret = __('Domingo');
        }
        return $ret;
    }
}
