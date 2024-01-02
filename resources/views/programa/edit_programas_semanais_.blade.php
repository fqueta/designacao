@if (isset($config['conf']['semanas']) && ($sem=$config['conf']['semanas']))
@php
    $designacoes = isset($config['conf']['designacoes']['dds']) ? $config['conf']['designacoes']['dds'] : false;
    $string_designacoes = isset($config['conf']['designacoes']['lista']) ? $config['conf']['designacoes']['lista'] : false;
    $dsalv = isset($config['conf']['dsalv']) ? $config['conf']['dsalv'] : false;
    // dd($config['conf']);
    // $meses = App\Qlib\Qlib::meses();
@endphp
<table class="table">
    <thead>
        <tr>
            <th>Mês</th>
            <th>Desiganções</th>
            <th>Ação</th>
            <th>Assembléia</th>
            <th>Visita</th>
        </tr>
    </thead>
    <body>
        {{-- <form id="frm-config" method="post"> --}}
            @foreach ($sem as $k=>$v)
                @if (is_array($v))
                    @foreach ($v as $k1=>$v1)
                        <tr>
                            <td>
                                {{App\Qlib\Qlib::dataExtensso($v1)}}
                            </td>
                            <td>
                                <div id="str-{{$v1}}" class="">
                                    {{$string_designacoes}}
                                </div>
                                <input type="hidden" name="prog[{{$v1}}]['data']" value="{{$v1}}">
                            </td>
                            <td class="text-center">
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#model-{{$k1}}">
                                   Editar
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="model-{{$k1}}" tabindex="-1" role="dialog" aria-labelledby="modelTitle{{$k1}}" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" role="document">
                                        <div class="modal-content">
                                                <div class="modal-header">
                                                        <h5 class="modal-title">Editar {{App\Qlib\Qlib::dataExtensso($v1)}}</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                    </div>
                                            <div class="modal-body">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <ul class="list-group ul-{{$k1}} sortable">

                                                                @if (is_object($designacoes))
                                                                @php
                                                                    $arr_d = explode(',',$string_designacoes);
                                                                @endphp
                                                                @foreach ($designacoes as $kde=>$vde)
                                                                    @php
                                                                        if(isset($dsalv[$v1][$vde['id']]['config'])){
                                                                            $arr_dsalv=$dsalv[$v1][$vde['id']]['config'];
                                                                        }else{
                                                                            $arr_dsalv=[];
                                                                        }
                                                                    @endphp
                                                                    <li class="list-group-item">
                                                                        <div class="row">
                                                                            <div class="col-4">
                                                                                <input class="form-control id-{{$v1}}" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id]" value="{{$vde['id']}}" />
                                                                                <input class="form-control no-{{$v1}}" placeholder="{{__('Informe o nome da desiganação')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome]" id="des-{{$v1}}-{{$kde}}" value="{{$vde['nome']}}" />
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <input class="form-control tm-{{$v1}}" type="text" name="config[des][{{$v1}}][{{$kde}}][tema]" placeholder="{{__('Informe o tema da desiganação')}}" value="{{@$arr_dsalv['tema']}}" />
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <input class="form-control id_designado-{{$v1}}" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id_designado]" value="{{@$arr_dsalv['id_designado']}}" />
                                                                                <input class="form-control autocomplete nome_designado-{{$v1}}" placeholder="{{__('Nome do desiganado')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome_designado]"  url_autocomplete="{{route('publicadores.index')}}" value="{{@$arr_dsalv['nome_designado']}}" />
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                @endforeach

                                                            @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                <button type="button" onclick="insertPrograma()" class="btn btn-primary"><i class="fas fa-check"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"> <input type="checkbox" name="" id=""> </td>
                            <td class="text-center"> <input type="checkbox" name="" id=""> </td>
                        </tr>
                    @endforeach
            @endif
            @endforeach
        {{-- </form> --}}
    </body>
</table>
{{-- {{dd($sem)}} --}}

@endif
