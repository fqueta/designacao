@extends('adminlte::page')

@section('title', 'Data Brasil - Painel')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 tit-sep" >Painel</h1>
    </div><!-- /.col -->

</div>
@stop

@section('content')
{{-- Fim painel filtro ano --}}
<div class="row card-top">

 </div>
 <div class="row mb-5">
    <div class="col-md-12">

        <h3>Seja bem vindo para ter acesso entre em contato com o suporte</h3>
    </div>
  </div>
@stop

@section('css')
    @include('qlib.csslib')
    <style>
        .tit-sep{
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
@stop

@section('js')
    @include('qlib.jslib')
    {{-- @include('mapas.jslib') --}}
@stop
