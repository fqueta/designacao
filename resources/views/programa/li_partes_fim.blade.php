@php
    $name = 'des2['.$v1.'][partes][' . $k_sessao . '][' . $ordem . ']';
    $token = !empty($designacao['token']) ? $designacao['token'] : uniqid();
    $d_orador = 'none';
    if(isset($designacao['id_designacao']) && $designacao['id_designacao']==27){
        //ou seja designacao = Orador
        $d_orador = 'block';
    }
@endphp
<li class="list-group-item" id="{{$v1}}_{{$k_sessao}}_{{$ordem}}" data-token="{{$token}}" data-li_id="{{@$designacao['id']}}">
    {{-- {{dd($designacao,$name)}} --}}
    <div class="row">
        <input type="hidden" name="{{$name.'[token]'}}" inp="token" value="{{$token}}">
        <input type="hidden" name="{{$name.'[id]'}}" value="{{@$designacao['id']}}">
        <input type="hidden" name="{{$name.'[ordem]'}}" value="{{$ordem}}">
        <div class="col-md-2 d-none">
            <label>Numero</label>
            <input type="number" value="{{@$designacao['numero']}}" class="form-control" name="{{$name.'[numero]'}}" id="">
        </div>
        <div class="col-md-5">
            <label>Designação</label>
            @include('programa.select_designacao',[
                'arr' => $config['conf']['arr_desiganacao'],
                'name' => $name.'[id_designacao]',
                'value'=>@$designacao['id_designacao'],
                'token'=>@$token,
            ])
        </div>
        <div class="col-md-6">
            <label>Nome</label>
            {{-- <input type="text" class="form-control" value="{{@$designacao['config']['nome_designado']}}" name="{{$name.'[nome_designado]'}}" id=""> --}}
            @include('programa.select_participantes',[
                'arr' => $arr_participantes,
                'name' => $name.'[id_designado]',
                'data_extensso' => App\Qlib\Qlib::dataExtensso($v1),
                'value'=>@$designacao['id_designado'],
                'tipo'=>'id_designado',
            ])
        </div>
        <div class="col-md-1 text-right pt-3 mt-3">
            <button type="button" onclick="remove_designacao('{{@$designacao['id']}}');" class="btn btn-danger btn-block"><i class="fas fa-times"></i></button>
        </div>
        <div class="row w-100 mr-0 ml-0">
            <div class="ov col-md-6 d-orador" style="display: {{$d_orador}}">
                <label>Orador Visitante</label>
                <input class="form-control" type="text" name="{{$name}}[orador_visitante]" value="{{@$designacao['orador_visitante']}}" id="">
            </div>
            <div class="tm col-md-6 d-orador" style="display: {{$d_orador}}">
                <label for="tema">Tema</label>
                <input class="form-control" type="text" name="{{$name}}[obs]" value="{{@$designacao['obs']}}" id="">
            </div>
        </div>
    </div>
</li>
