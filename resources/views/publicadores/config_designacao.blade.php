@php
    $aceita = isset($dados['value']['designacao']['aceita']) ? $dados['value']['designacao']['aceita'] : [];
    $tipoDesigancao = isset($dados['tipoDesigancao']) ? $dados['tipoDesigancao'] : [];
    $d = isset($dados['d'])?$dados['d'] : [];
    $partes_fim_semana = isset($dados['partes_fim_semana'])?$dados['partes_fim_semana'] : [];
    $genero = isset($d['genero']) ? $d['genero'] : '';
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
                                <th>{{__('Ultima')}}</th>
                                {{-- <th>{{__('Sala')}}</th>
                                <th>{{__('Demora')}}</th> --}}
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
                                    <td><input type="date" name="config[designacao][ultima_{{$v->id}}]" id="dat_ultima_{{$v->id}}"></td>
                                    {{-- <td>{{__('Sala')}}</td> --}}
                                    {{-- <td>{{__('Demora')}}</td> --}}
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
                                    <th>{{__('Ultima')}}</th>
                                    {{-- <th>{{__('Sala')}}</th>
                                    <th>{{__('Demora')}}</th> --}}
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
                                        <td><input type="date" name="config[designacao][ultima_{{$id}}]" id="dat_ultima_{{$id}}"></td>
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
