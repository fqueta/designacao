<select class="form-control " data-sessao="{{$k_sessao}}" data-token="{{$token}}" onchange="selec_desig(this)" name="{{$name}}" id="">
    {{-- <option value="cad">Cadastrar designação </option> --}}
    @if (isset($arr) && is_array($arr))
        @foreach ($arr as $ks=>$vs)
            <option @if (@$value==$ks)
                selected="selected"
            @endif value="{{$ks}}">{{$vs}}</option>
        @endforeach
    @endif
</select>
