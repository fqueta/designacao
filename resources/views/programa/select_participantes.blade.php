@php
    $nome_select = 'Selecionar Participante';
    $id_m = str_replace('[', '_', $name);
    $id_m = str_replace(']', '', $id_m);
    $post_type = request()->segment(1);
    if(isset($arr[@$value])){
        $nome_select = $arr[@$value];
    }
@endphp
<div class="modal fade" id="{{$id_m}}" tabindex="-1" role="dialog" aria-labelledby="modelTitle{{$id_m}}" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
                <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12" id="b-{{$id_m}}">
                            {{-- <select class="form-control" name="{{$name}}" id="">
                                <option value=""> Selecione </option>
                                @if (isset($arr) && is_array($arr))
                                    @foreach ($arr as $ks=>$vs)
                                        <option @if (@$value==$ks)
                                            selected="selected"
                                        @endif value="{{$ks}}">{{$vs}}</option>
                                    @endforeach
                                @endif
                            </select> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="{{$name}}" value="{{$value}}" /><br>
<a href="javascript:void(0)" data-toggle="modal" data-target="#{{$id_m}}" data-tipo="{{$tipo}}" data-post_type="{{$post_type}}" onclick="select_parcipante(this);" data-extensso="{{@$data_extensso}}" class="underline" data-campo="{{$name}}"> {{$nome_select}} </a>
