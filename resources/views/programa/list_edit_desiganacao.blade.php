@php
$arr_participantes = $des2['config']['participantes'];
@endphp
@foreach ($prg[$k_sessao] as $ordem=>$designacao )
    @if(@$designacao['id_designado']!=0)
        @include('programa.li_partes')
    @endif
@endforeach
