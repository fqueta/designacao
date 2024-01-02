@extends('adminlte::page')

@section('title', 'Data Brasil - Painel')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Transparência</h1>
    </div><!-- /.col -->
    <div class="col-sm-6 text-right">
        <div class="btn-group" role="group" aria-label="actions">
            @can('create','familias')
                <a href="{{route('familias.create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Novo cadastro</a>
            @endcan
            <a href="{{route('familias.index')}}" class="btn btn-secondary"><i class="fa fa-list"></i> Ver cadastros</a>
            <!--<a href="{{route('relatorios.social')}}" class="btn btn-dark"><i class="fa fa-chart-bar"></i> Ver relatórios</a>-->
        </div>
    </div><!-- /.col -->
</div>
@stop

@section('content')
<!--<p>Welcome to this beautiful admin panel.</p>-->
@can('ler','familias')
{{-- Inicio painel filtro ano --}}
<div class="row">
    <div class="col-md-12 mb-3">
        @include('familias.filtro_ano',['arr_ano'=>@$config['c_familias']['anos']])
    </div>
</div>
{{-- Fim painel filtro ano --}}
<div class="row card-top">
    @if (isset($config['c_familias']['cards_home']))
    <div class="col-md-12 text-center mb-3">
        <h4 class="">{{__('Cadastros socioeconômicos')}}</h4>
        <p>
            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged
        </p>
    </div>
    @foreach ($config['c_familias']['cards_home'] as $k=>$v)
    <div class="col-lg-{{$v['lg']}} col-{{$v['xs']}}">
                <!-- small box -->
                <div class="small-box bg-{{$v['color']}}" title="{{$v['obs']}}">
                  <div class="inner">
                    <h3>{{$v['valor']}}</h3>

                    <p>{{$v['label']}}</p>
                  </div>
                  <div class="icon">
                    <i class="{{$v['icon']}}"></i>
                  </div>
                  <a href="{{$v['href']}}" title="{{__('Ver detalhes de ')}}{{__($v['label'])}}" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
                </div>
              </div>
            @endforeach
        @endif
    </div>
    <div class="row">
        <div class="col-md-12 text-center mt-3 mb-3">
            <h4 class="">{{__('Levantamentos topográficos')}}</h4>
            <p>
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged
            </p>
        </div>
        <div class="col-lg-6 col-6">
            <div class="small-box bg-info" title="">
            <div class="inner">
                <h3></h3>
                <p>MAPA INICIAL DA ÁREA</p>
            </div>
            <div class="icon">
                <i class="fa fa-map-marked-alt"></i>
            </div>
            <a href="http://127.0.0.1:8000/familias" title="Ver detalhes de Todos cadastrados" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-6 col-6">
            <div class="small-box bg-info" title="">
            <div class="inner">
                <h3></h3>
                <p>MAPA FINAL</p>
            </div>
            <div class="icon">
                <i class="fa fa-map-marked-alt"></i>
            </div>
            <a href="http://127.0.0.1:8000/familias" title="Ver detalhes de Todos cadastrados" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    <!--

        <div class="col-md-6">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h4 class="card-title">MAPA INICIAL DA ÁREA</h4>
                </div>
                <div class="card-body">
                    <p class="card-text">Trata-se de um mapa tipo croqui com as indicações dos lotes / casas a serem cadastrados. É um mapa inicial e pode/deve mudar até o final devido às incidências de questões legais que estão relacionadas no item CADASTROS SOCIOECONÔMICOS acima. Esse MAPA pode trazer informações como a área total, o número de quadras e sua nomenclatura e o número de lotes e sua nomenclatura. Por nomenclatura entende-se a numeração / código que os topógrafos darão para aquela(s) área(s).</p>
                </div>
                <div class="card-footer text-right">
                    <a href="#" class="btn btn-default">Visualizar <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h4 class="card-title">MAPA FINAL</h4>
                </div>
                <div class="card-body">
                    <p class="card-text">Trata-se da configuração final da área já definida retirando-se dela aqueles lotes que possuem registro ou tenham sido recusados ou não localizados. Será neste mapa que o Fernando e o Patrick farão o trabalho de identificação dos lotes e seus proprietários a partir da movimentação do cursor.</p>
                </div>
                <div class="card-footer text-right">
                    <a href="#" class="btn btn-default">Visualizar <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    -->
    </div>
    <div class="row">
        <div class="col-md-12 text-center mt-3 mb-3">
            <h4 class="">{{__('Processo Jurídico')}}</h4>
            <p>
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged
            </p>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info" title="">
            <div class="inner">
                <h3>0</h3>
                <p>LEGISLAÇÃO PERTINENTE</p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="http://127.0.0.1:8000/familias" title="Ver detalhes de Todos cadastrados" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info" title="">
            <div class="inner">
                <h3>0</h3>
                <p>DECRETOS MUNICIPAIS</p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="http://127.0.0.1:8000/familias" title="Ver detalhes de Todos cadastrados" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info" title="">
            <div class="inner">
                <h3>1</h3>
                <p>ATENDIMENTOS JURÍDICOS</p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="http://127.0.0.1:8000/familias" title="Ver detalhes de Todos cadastrados" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info" title="">
            <div class="inner">
                <h3>0</h3>
                <p>PROCESSOS ENTREGUES</p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="http://127.0.0.1:8000/familias" title="Ver detalhes de Todos cadastrados" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info" title="">
            <div class="inner">
                <h3>0</h3>
                <p>CERTIDÕES</p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="http://127.0.0.1:8000/familias" title="Ver detalhes de Todos cadastrados" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        @if (isset($config['c_familias']['progresso']))
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <strong>Progresso dos cadastros</strong>
                </div>
                <div class="card-body">
                    @foreach ($config['c_familias']['progresso'] as $k=>$v)
                        <div class="progress-group">
                            {{$v['label']}}
                            <span class="float-right"><b>{{$v['total']}}</b>/{{$v['geral']}}</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar {{$v['color']}}" style="width: {{$v['porcento']}}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <!--<div class="col-md-12">
            @if (isset($config['mapa']['config']))
                //App\Http\Controllers\MapasController::exibeMapas($config['mapa']['config']) !!}
            @endif
        </div>-->
    </div>
    @else
    <div class="col-md-12">

        <h3>Seja bem vindo para ter acesso entre em contato com o suporte</h3>
    </div>

    @endcan


  </div>
@stop

@section('css')
    @include('qlib.csslib')
@stop

@section('js')
    @include('qlib.jslib')
    @include('mapas.jslib')
@stop
