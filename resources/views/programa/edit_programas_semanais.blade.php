@if (isset($config['conf']['semanas']) && ($sem=$config['conf']['semanas']))
@php
    $designacoes = isset($config['conf']['co']['des']) ? $config['conf']['co']['des'] : false;
    if(!$designacoes)
    $designacoes = isset($config['conf']['designacoes']['dds']) ? $config['conf']['designacoes']['dds'] : false;
    $string_designacoes = isset($config['conf']['designacoes']['lista']) ? $config['conf']['designacoes']['lista'] : false;
    $dsalv = isset($config['conf']['dsalv']) ? $config['conf']['dsalv'] : false;
    // $meses = App\Qlib\Qlib::meses();
    if(isset($value['config']['des']) && is_array($value['config']['des'])){
        $value = $value['config']['des'];
    }
    $json_sessoes = App\Qlib\Qlib::qoption('sessoes_designacao');
    $arr_sessoes = App\Qlib\Qlib::lib_json_array($json_sessoes);
    $des2 = isset($config['conf']['desiganations']) ? $config['conf']['desiganations'] : false;
    $sessoes = isset($des2['config']['sessoes']) ? $des2['config']['sessoes'] : $arr_sessoes;
    $arr_participantes = isset($des2['config']['participantes'])?$des2['config']['participantes']:[];
    $config['conf']['arr_desiganacao'] = isset($config['conf']['desiganations']['config']['tipos_designacao']) ? $config['conf']['desiganations']['config']['tipos_designacao'] : [];
    // dd($des2);
    // dd($config['conf']);
    $sec = isset($config['conf']['sec']) ? $config['conf']['sec']: false;
@endphp
<table class="table">
    <thead>
        <tr>
            <th>Mês</th>
            {{-- <th>Desiganções</th> --}}
            {{-- <th>Ação</th> --}}
            {{-- <th>Assembléia</th>
            <th>Visita</th> --}}
        </tr>
    </thead>
    <body>
            @foreach ($sem as $k=>$v)
                @if (is_array($v))
                    @foreach ($v as $k1=>$v1)
                        <tr>
                            {{-- <td>

                            </td> --}}
                            <td>
                                <div class="col-12">
                                    <div class="card card-secondary card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                {{App\Qlib\Qlib::dataExtensso($v1)}} {!!App\Qlib\Qlib::link_programacao_woljw($v1,'<div class=""><a class="underline" href="{link}" target="_BLANK">Acesso à Programação no Jw.ORG</a></div>')!!}
                                            </h3>
                                            <div class="card-tools d-print-none">
                                                <label for="assembleia_{{$k1}}">
                                                    <input type="checkbox" name="config[des][{{$v1}}][assembleia]" id="assembleia_{{$k1}}"> {{__('Assembléia')}}
                                                </label>
                                                <label for="visita_{{$k1}}">
                                                    <input type="checkbox" name="config[des][{{$v1}}][visita]" id="visita_{{$k1}}"> {{__('Visita')}}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body" id="car-{{$v1}}">
                                            @if(isset($des2['programa']) && is_array($des2['programa']))
                                                @php
                                                    $prg = isset($des2['programa'][$v1])?$des2['programa'][$v1]:false;
                                                @endphp
                                                @if(is_array($sessoes))
                                                    @foreach ($sessoes as $k_sessao=>$sessao)
                                                        @if ($sec=='fim-semana')
                                                            @if($k_sessao=='inicio')
                                                            <div class="card card-sessao-{{$k_sessao}}">
                                                                <div class="card-header {{@$sessao['color']}}">
                                                                    {{@$sessao['label']}}
                                                                </div>
                                                                <div class="card-body">
                                                                    <ul class="list-group sortable">
                                                                        {{-- @if(is_array($prg)) --}}
                                                                        @if(isset($prg[$k_sessao]) && is_array($prg[$k_sessao]))
                                                                        {{-- {{dd($prg)}} --}}
                                                                            @include('programa.list_edit_desiganacao')
                                                                        @else
                                                                            @if($k_sessao=='inicio')
                                                                                @php
                                                                                    $ordem = 0;
                                                                                    $name = 'des2['.$v1.'][partes][' . $k_sessao . '][' . $ordem . ']';
                                                                                @endphp
                                                                                @if ($sec=='fim-semana')
                                                                                    @include('programa.li_partes_fim')
                                                                                @else
                                                                                    @include('programa.li_partes_meio')
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    </ul>

                                                                </div>
                                                                <div class="card-footer text-muted">
                                                                    <button type="button" class="btn btn-outline-secondary" onclick="add_designation2('{{$v1}}','{{$k_sessao}}');"><i class="fas fa-plus"></i> {{__('Adicionar')}}</button>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @else
                                                            <div class="card card-sessao-{{$k_sessao}}">
                                                                <div class="card-header {{@$sessao['color']}}">
                                                                    {{@$sessao['label']}}
                                                                </div>
                                                                <div class="card-body">
                                                                    <ul class="list-group sortable">
                                                                        {{-- @if(is_array($prg)) --}}
                                                                        @if(isset($prg[$k_sessao]) && is_array($prg[$k_sessao]))
                                                                        {{-- {{dd($prg)}} --}}
                                                                            @include('programa.list_edit_desiganacao')
                                                                        @else
                                                                            @if($k_sessao=='inicio')
                                                                                @php
                                                                                    $ordem = 0;
                                                                                    $name = 'des2['.$v1.'][partes][' . $k_sessao . '][' . $ordem . ']';
                                                                                @endphp
                                                                                @if ($sec=='fim-semana')
                                                                                    @include('programa.li_partes_fim')
                                                                                @else
                                                                                    @include('programa.li_partes_meio')
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    </ul>

                                                                </div>
                                                                <div class="card-footer text-muted">
                                                                    <button type="button" class="btn btn-outline-secondary" onclick="add_designation2('{{$v1}}','{{$k_sessao}}');"><i class="fas fa-plus"></i> {{__('Adicionar')}}</button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                {{-- {{dd($prg)}} --}}
                                            @else
                                            <ul class="list-group ul-{{$k1}} sortable" id="ul-{{$v1}}">
                                                    @if (is_object($designacoes))
                                                        @php
                                                            $arr_d = explode(',',$string_designacoes);
                                                        @endphp
                                                        {{-- @foreach ($arr_d as $kde=>$vde) --}}
                                                        @foreach ($designacoes as $kde=>$vde)
                                                            @php
                                                                if(isset($dsalv[$v1][$vde['id']]['config'])){
                                                                    $arr_dsalv=$dsalv[$v1][$vde['id']]['config'];
                                                                }else{
                                                                    $arr_dsalv=[];
                                                                }
                                                            @endphp
                                                            <li class="list-group-item" id="li-{{$v1}}-{{$kde}}" data-key="{{$kde}}">
                                                                <div class="row">
                                                                    <div class="col-3">
                                                                        @isset($config['conf']['arr_desiganacao'])
                                                                            @include('programa.select_designacao',[
                                                                                'arr' => $config['conf']['arr_desiganacao'],
                                                                                'name' => 'config[des]['.$v1.']['.$kde.'][id]',
                                                                                'value'=>@$vde['id'],
                                                                                ])
                                                                        @endisset

                                                                        {{-- <input class="form-control" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id]" value="{{$vde['id']}}" />
                                                                        <input class="form-control no-{{$v1}}" placeholder="{{__('Informe o nome da desiganação')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome]"  value="{{$vde['nome']}}" /> --}}
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control tm-{{$v1}}" type="text" name="config[des][{{$v1}}][{{$kde}}][tema]" placeholder="{{__('Informe o tema da desiganação')}}" value="{{@$arr_dsalv['tema']}}" />
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control id_designado-{{$v1}}" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id_designado]" id="des-id_designado-{{$v1}}-{{$kde}}" value="{{@$arr_dsalv['id_designado']}}" />
                                                                        <input class="form-control autocomplete nome_designado-{{$v1}}" placeholder="{{__('Nome do desiganado')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome_designado]" id="nome_designado-{{$v1}}-{{$kde}}" url_autocomplete="{{route('publicadores.index')}}" value="{{@$arr_dsalv['nome_designado']}}" />
                                                                    </div>
                                                                    <div class="col-1 text-right">
                                                                        <button type="button" title="{{__('Remover')}}" class="btn btn-outline-danger" onclick="remove_designation('li-{{$v1}}-{{$kde}}')"><i class="fas fa-trash"></i></button>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    @elseif (is_array($designacoes) && isset($designacoes[$v1]))
                                                        @foreach ($designacoes[$v1] as $kde=>$vde)
                                                            @php
                                                                if(isset($dsalv[$v1][@$vde['id']]['config'])){
                                                                    $arr_dsalv=$dsalv[$v1][$vde['id']]['config'];
                                                                }else{
                                                                    $arr_dsalv=[];
                                                                }
                                                                // App\Qlib\Qlib::lib_print($arr_dsalv);
                                                            @endphp
                                                            <li class="list-group-item" id="li-{{$v1}}-{{$kde}}" data-key="{{$kde}}">
                                                                <div class="row">
                                                                    <div class="col-12 mb-2">
                                                                        @if(is_array($arr_sessoes))
                                                                        @php
                                                                            $class_select = isset($arr_sessoes[@$vde['sessao']]['color'])?$arr_sessoes[@$vde['sessao']]['color']:false;
                                                                        @endphp
                                                                        <select class="form-control {{$class_select}}" onchange="select_sessao(this);" name="config[des][{{$v1}}][{{$kde}}][sessao]">
                                                                            <option value="">Selecione Sessão que a desiganção faz parte</option>
                                                                            @foreach ($arr_sessoes as $kse=>$vse )
                                                                                @php
                                                                                    $selesec = false;
                                                                                    if($kse==@$vde['sessao']){
                                                                                        $selesec = 'selected';
                                                                                    }
                                                                                @endphp
                                                                                <option class="{{$vse['color']}}" {{$selesec}} value="{{$kse}}">{{$vse['label']}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @endif
                                                                    </div>
                                                                    <div class="col-3">
                                                                        @isset($config['conf']['arr_desiganacao'])
                                                                            @include('programa.select_designacao',[
                                                                                'arr' => $config['conf']['arr_desiganacao'],
                                                                                'name' => 'config[des]['.$v1.']['.$kde.'][id]',
                                                                                'value'=>@$vde['id'],
                                                                                ])
                                                                        @endisset
                                                                        {{-- <input class="form-control" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id]" value="{{@$vde['id']}}" />
                                                                        <input class="form-control no-{{$v1}}" placeholder="{{__('Informe o nome da desiganação')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome]"  value="{{@$vde['nome']}}" /> --}}
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control tm-{{$v1}}" type="text" name="config[des][{{$v1}}][{{$kde}}][tema]" placeholder="{{__('Informe o tema da desiganação')}}" value="{{@str_replace('{','',$arr_dsalv['tema'])}}" />
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control id_designado-{{$v1}}" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id_designado]" id="des-id_designado-{{$v1}}-{{$kde}}" value="{{@$arr_dsalv['id_designado']}}" />
                                                                        <input class="form-control autocomplete nome_designado-{{$v1}}" placeholder="{{__('Nome do desiganado')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome_designado]" id="nome_designado-{{$v1}}-{{$kde}}" url_autocomplete="{{route('publicadores.index')}}" value="{{str_replace('{','',@$arr_dsalv['nome_designado'])}}" />
                                                                    </div>
                                                                    <div class="col-1 text-right">
                                                                        <button type="button" title="{{__('Remover')}}" class="btn btn-outline-danger" onclick="remove_designation('li-{{$v1}}-{{$kde}}')"><i class="fas fa-trash"></i></button>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    @endif

                                                </ul>
                                                @endif
                                        </div>
                                        {{-- <div class="card-footer text-muted">
                                            <button type="button" class="btn btn-outline-secondary" onclick="add_designation('{{$v1}}');"><i class="fas fa-plus"></i> {{__('Adicionar')}}</button>
                                        </div> --}}
                                    </div>
                                </div>
                                <input type="hidden" name="prog[{{$v1}}]['data']" value="{{$v1}}">
                            </td>
                        </tr>
                    @endforeach
            @endif
            @endforeach
        {{-- </form> --}}
    </body>
</table>
{{-- {{dd($sem)}} --}}

@endif
