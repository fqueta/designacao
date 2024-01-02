<select class="form-control" name="{{$name}}" id="">
    <option value=""> Selecione </option>
    @if (isset($arr) && is_array($arr))
        @foreach ($arr as $ks=>$vs)
            <option @if (@$value==$ks)
                selected="selected"
            @endif value="{{$ks}}">{{$vs}}</option>
        @endforeach
    @endif
</select>
