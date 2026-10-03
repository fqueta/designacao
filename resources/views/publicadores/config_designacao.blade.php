@php
    $aceita = isset($dados['value']['designacao']['aceita']) ? $dados['value']['designacao']['aceita'] : [];
    $designacao_cfg = isset($dados['value']['designacao']) && is_array($dados['value']['designacao']) ? $dados['value']['designacao'] : [];
    $tipoDesigancao = isset($dados['tipoDesigancao']) ? $dados['tipoDesigancao'] : [];
    $d = isset($dados['d'])?$dados['d'] : [];
    $partes_fim_semana = isset($dados['partes_fim_semana'])?$dados['partes_fim_semana'] : [];
    $genero = isset($d['genero']) ? $d['genero'] : '';
    $intervalo_opcs = [0=>'Sem intervalo', 1=>'1 mês', 2=>'2 meses', 3=>'3 meses', 4=>'4 meses'];
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="card card-secondary">
            <div class="card-header">
                {{__('Designação de meio de semana')}}
            </div>
            <div class="card-body">
                <small>
                    {{__('Designações aceitas por este participante')}}
                </small>
                @if (isset($tipoDesigancao) && is_object($tipoDesigancao))
                    <table class="table table-hover">
                        <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{__('Designação')}}</th>
                                    <th>{{__('Intervalo')}}</th>
                                    {{-- <th>{{__('Sala')}}</th> --}}
                                </tr>
                        </thead>
                        <tbody>
                            @foreach ($_GET["tipoDesignacao"] as $k=>$v)
                                @php
                                    $checked = false;
                                    if(in_array($v->id,$aceita)){
                                        $checked = 'checked';
                                    }
                                @endphp
                                <tr>
                                    <td><input type="checkbox" {{$checked}} name="config[designacao][aceita][]" value="{{$v->id}}" id="aceita_{{$v->id}}"></td>
                                    <td>{{$v->nome}}</td>
                                    <td>
                                        @php $int_sel = isset($designacao_cfg['intervalo_'.$v->id]) ? (int)$designacao_cfg['intervalo_'.$v->id] : 0; @endphp
                                        <select name="config[designacao][intervalo_{{$v->id}}]" id="intervalo_{{$v->id}}" class="form-control form-control-sm" title="Intervalo mínimo entre duas execuções desta parte">
                                            @foreach ($intervalo_opcs as $ik=>$il)
                                                <option value="{{$ik}}" @if($int_sel===$ik) selected @endif>{{$il}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    {{-- <td>{{__('Sala')}}</td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    @if ($genero=='m')
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    {{__('Designação de meio de fim de semana')}}
                </div>
                <div class="card-body">
                    <small>
                        {{__('Designações aceitas por este participante')}}
                    </small>

                    @if (isset($partes_fim_semana) && is_array($partes_fim_semana))
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{__('Designação')}}</th>
                                    <th>{{__('Intervalo')}}</th>
                                    {{-- <th>{{__('Sala')}}</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($partes_fim_semana as $id=>$vsf)
                                    @php
                                        $checked = false;
                                        if(in_array($id,$aceita)){
                                            $checked = 'checked';
                                        }
                                    @endphp
                                <tr>
                                    <td><input type="checkbox" {{$checked}} name="config[designacao][aceita][]" value="{{$id}}" id="aceita_{{$id}}"></td>
                                    <td>{{$vsf}}</td>
                                    <td>
                                        @php $int_sel = isset($designacao_cfg['intervalo_'.$id]) ? (int)$designacao_cfg['intervalo_'.$id] : 0; @endphp
                                        <select name="config[designacao][intervalo_{{$id}}]" id="intervalo_{{$id}}" class="form-control form-control-sm" title="Intervalo mínimo entre duas execuções desta parte">
                                            @foreach ($intervalo_opcs as $ik=>$il)
                                                <option value="{{$ik}}" @if($int_sel===$ik) selected @endif>{{$il}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
