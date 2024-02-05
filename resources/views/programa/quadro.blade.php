<div class="col-12" {!!$mbcard!!}>
    <div class="card mb-1">
        <div class="card-header {{$sessao['color']}}">
            {{-- Semana: 04-10 de Setembro --}}
            {{$title}}
        </div>
        <div class="card-body pt-0 pb-0 mb-0">
            <div class="row ml-0">
                @if ($routa=='fim-semana')
                    @if($k_sessao=='inicio')
                        @if (is_array($partes))
                        {{-- {{dd($partes,$tipos)}} --}}
                            @foreach ($partes as $k_parte=>$parte )
                                @php
                                    $nomeParticipante = strtoupper(@$participantes[$parte['id_designado']]);
                                    $col = 6;
                                    $tema = false;
                                    if($tipos[$parte['id_designacao']]=='Orador'){
                                        $col = 6;
                                        $tema = '<div class="col-6"><b style="">Tema: </b><span>'.$parte['obs'].'</span></div>';
                                        if(!empty($parte['orador_visitante'])){
                                            $nomeParticipante = strtoupper($parte['orador_visitante']);
                                        }
                                    }
                                @endphp
                                <div class="col-{{$col}}">
                                    <b>{{@$tipos[$parte['id_designacao']]}}:</b> <span>{{$nomeParticipante}}</span>
                                </div>
                                {!!$tema!!}
                            @endforeach
                        @endif
                    @endif
                @else
                    @if($k_sessao=='inicio')
                        @if (is_array($partes))
                            @foreach ($partes as $k_parte=>$parte )
                                <div class="col-6">
                                    <b>{{@$tipos[$parte['id_designacao']]}}:</b> <span>{{strtoupper(@$participantes[$parte['id_designado']])}}</span>
                                </div>
                            @endforeach
                        @endif
                    @endif
                    @if($k_sessao=='tesouros' || $k_sessao=='vida')
                        <div class="col-12">
                            <table class="table mb-0 table-striped">
                                <tbody>
                                @if (is_array($partes))
                                    @foreach ($partes as $k_parte=>$parte )
                                        @php
                                            if($parte['numero'] != 0){
                                                $numero = $parte['numero'];
                                            }else{
                                                $numero = '';
                                            }
                                        @endphp
                                        <tr class="col-12">
                                            <td style="width:20%"><b>{{$numero}} {{@$tipos[$parte['id_designacao']]}}</b></td>
                                            <td style="width:55%"><span>{{@$parte['obs']}}</span></td>
                                            <td style="width:25%" class="text-right"><span>{{strtoupper(@$participantes[$parte['id_designado']])}}</span></td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if ($k_sessao=='ministerio')
                        <div class="col-12">
                            <table class="table mb-0">
                                <tbody>
                                @if (is_array($partes))
                                    @foreach ($partes as $k_parte=>$parte )
                                        @php
                                            $nome = strtoupper(@$participantes[$parte['id_designado']]);
                                            if($parte['id_ajudante']>0){
                                                $nome .= '/ '.strtoupper(@$participantes[$parte['id_ajudante']]);
                                            }
                                            if($parte['numero'] != 0){
                                                $numero = @$parte['numero'];
                                            }else{
                                                $numero = '';
                                            }
                                        @endphp

                                        <tr class="col-12">
                                            <td style="width:65%" class="text-left"><b>{{@$numero}} </b><b>{{@$tipos[$parte['id_designacao']]}}</b>: {{@$parte['obs']}} </td>
                                            <td style="width:35%" class="text-right"><span>{{$nome}}</span></td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if ($k_sessao=='final')
                        <div class="col-12">
                            <table class="table mb-0">
                                <tbody>
                                @if (is_array($partes))
                                    @foreach ($partes as $k_parte=>$parte )
                                        @php
                                            $nome = strtoupper(@$participantes[$parte['id_designado']]);
                                            if($parte['id_ajudante']>0){
                                                $nome .= '/ '.strtoupper(@$participantes[$parte['id_ajudante']]);
                                            }
                                        @endphp

                                        <tr class="col-12">
                                            <td style="width:35%" class="text-right"><b> {{@$tipos[$parte['id_designacao']]}}</b>:</td>
                                            <td style="width:65%" class="text-right"><span>{{$nome}}</span></td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
