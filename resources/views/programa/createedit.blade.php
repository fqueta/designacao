@extends('adminlte::page')

@include('title')

@section('content_header')
<h3>{{$titulo}}</h3>
@stop
@section('content')

@include('admin.partes.header')
<div class="row">
    <div class="col-md-12 mens">
    </div>
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Informações</h3>
                <div class="card-tools">
                    @if(isset($config['sinc_semanas']) && is_string($config['sinc_semanas']) && $config['route']!='fim-semana')
                        <button type="button" data-semanas="{{$config['sinc_semanas']}}" class="btn btn-default" onclick="sinc_partes_jw(this,'jw')" title="Antes de prosseguir é necessário sincronizar as partes com o a apostila mais atualizado no jw.org"> <i class="fa fa-refresh" aria-hidden="true"></i> (1) Sincronizar com JW</button>
                        <button type="button" data-semanas="{{$config['sinc_semanas']}}" class="btn btn-default" onclick="sinc_partes_jw(this,'inic_fim')"  title="Nessa opção é possivel adincinar as desiganções de partes mecanicas que não aparecem na apostila do mes"> <i class="fa fa-refresh" aria-hidden="true"></i> (2) Adicionar outras partes</button>
                    @endif
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{App\Qlib\Qlib::formulario([
                    'campos'=>$campos,
                    'config'=>$config,
                    'value'=>$value,
                ])}}
            </div>
        </div>
    </div>
    {{-- <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Arquivos das participações</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{App\Qlib\Qlib::gerUploadAquivos([
                    'pasta'=>$config['route'].'/'.date('Y').'/'.date('m'),
                    'token_produto'=>$value['token'],
                    'tab'=>$config['route'],
                    'listFiles'=>@$listFiles,
                    'routa'=>@$config['route'],
                    'arquivos'=>@$config['arquivos'],
                    'typeN'=>@$config['typeN'],
                ])}}
            </div>
        </div>
    </div> --}}
</div>

@stop

@section('css')
    @include('qlib.csslib')
@stop

@section('js')
    @include('qlib.jslib')
    <script type="text/javascript">
        $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
        });

    </script>
    @include('qlib.js_submit')
@stop
