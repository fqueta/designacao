    @php
        $url = url('/');
    @endphp
    @if($k_sessao=='tesouros' || $k_sessao=='ministerio')
        @if (is_array($partes))
        <div class="row">
            @foreach ($partes as $k_parte=>$parte)
                @if (isset($parte['id_designado']) && $parte['id_designado']>0)
                    @php
                        $link_zap = (new App\Http\Controllers\admin\designaController)->link_whatsapp($parte['id']);
                    @endphp
                    <div class="col-6 pr-2 pl-2">
                        <table class="table mb-3">
                            <thead>
                                <tr class="text-right d-print-none">
                                    <td colspan="2" >
                                        @if ($link_zap)
                                            <a href="{!!$link_zap!!}" class="btn btn-outline-success"><i class="fa fa-whatsapp"></i> Enviar</a>
                                        @endif
                                            <a href="{!!$url!!}/publicadores/{{$parte['id_designado']}}/edit?redirect_base={{base64_encode(url()->current())}}" class="btn btn-outline-primary"> <i class="fa fa-pen" title="{{__('Editar')}}"></i></a>
                                        </td>
                                    </tr>
                                <tr>
                                    <th class="text-center border-0" colspan="2"> {{__('DESIGNAÇÃO PARA A REUNIÃO NOSSA VIDA E MINISTÉRIO CRISTÃO')}} </th>
                                </tr>
                            </thead>
                            <tbody>
                                    @php
                                        if($parte['numero'] != 0){
                                            $numero = $parte['numero'];
                                        }else{
                                            $numero = '';
                                        }
                                    @endphp
                                    <tr>
                                        <td colspan="2">
                                            <div class="col-12">
                                                <label> {{__('Nome')}}: </label>
                                                {{strtoupper(@$participantes[$parte['id_designado']])}}
                                            </div>
                                        </td>
                                    </tr>
                                    @if($parte['id_ajudante']>0)
                                    <tr>
                                        <td class="border-0" colspan="2">
                                            <div class="col-12">
                                                <label> {{__('Ajudante')}}: </label>
                                                {{strtoupper(@$participantes[$parte['id_ajudante']])}}
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="border-0" colspan="2">
                                            <div class="col-12">
                                                <label> {{__('Semana')}}: </label>
                                                {{App\Qlib\Qlib::dataExtensso($d_semana)}}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0" colspan="2">
                                            <div class="col-12">
                                                <label> {{__('Número da parte')}}: </label>
                                                {{$numero}}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0" colspan="2">
                                            <div class="col-12">
                                                {{-- <label> {{__('Obs')}}: </label> --}}
                                                {{$parte['obs']}}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0" colspan="2">
                                            <div class="col-12">

                                                <label> {{__('Local')}}: </label>
                                                <ul style="list-style: none">
                                                    <li>
                                                        <label style="font-weight:500"><input checked type="checkbox" name="" id=""> {{__('Salão principal')}} </label>
                                                    </li>
                                                    <li>
                                                        <label style="font-weight:500"><input type="checkbox" name="" id=""> {{__('Sala B')}} </label>
                                                    </li>
                                                    <li>
                                                        <label style="font-weight:500"><input type="checkbox" name="" id=""> {{__('Sala C')}} </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0" colspan="2" style="text-align:justify">
                                            <div class="col-12">

                                                <p>
                                                    <b>{{__('observação para o estudante')}}</b>
                                                    {{__('A lição e a fonte de matéria para a sua designação estão na')}} <i>{{__('Apostila da Reunião vida e Ministério.')}}</i> {{__('Veja as instruções para a parte que estão nas')}} <i> {{__('Instruções para a Reunão Nossa Vida e Ministério Cristão')}} </i> (S-38)
                                                </p>
                                            </td>
                                        </div>
                                    </tr>
                                    <tr>
                                        <td class="border-0" colspan="2">
                                            <div class="col-12">
                                                <small>
                                                    S-89-T   11/23
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- <tr class="col-12">
                                        <td style="width:20%"><b>{{$numero}} {{@$tipos[$parte['id_designacao']]}}</b></td>
                                        <td style="width:55%"><span>{{@$parte['obs']}}</span></td>
                                        <td style="width:25%" class="text-right"><span>{{strtoupper(@$participantes[$parte['id_designado']])}}</span></td>
                                    </tr> --}}
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach
        </div>
        @endif
    @endif
    {{-- @if ($k_sessao=='ministerio')
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
    @endif --}}

