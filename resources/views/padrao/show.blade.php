@extends('adminlte::page')

@section('title', $title)

@section('content_header')
    <h3>{{$titulo}}</h3>
@stop
@section('content')
<div class="row">
    <div class="col-md-12 mens">
    </div>
    <div class="{{$config['class_card1']}}">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{__('Informações')}}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{App\Qlib\Qlib::show([
                    'campos'=>$campos,
                    'config'=>$config,
                    'value'=>$value,
                ])}}
            </div>
        </div>
        @include('qlib.show_files')
    </div>
    @if(isset($config['eventos']) && is_object($config['eventos']))
    <div class="{{$config['class_card2']}} mt-0 mb-5">
        @include('qlib.eventos.lista_eventos',['eventos'=>$config['eventos']])
    </div>
    @endif
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
            $('#inp-cpf,#inp-cpf_conjuge').inputmask('999.999.999-99');
          });
    </script>
@stop

