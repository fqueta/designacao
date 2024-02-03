@php
$arr_participantes = $des2['config']['participantes'];
@endphp
@foreach ($prg[$k_sessao] as $ordem=>$designacao )
    @if(@$designacao['id_designado']!=0 || @$designacao['id_designacao']!=0)
        @if ($sec=='fim-semana')
            @include('programa.li_partes_fim')
        @else
            @include('programa.li_partes_meio')
        @endif
    @endif
@endforeach
