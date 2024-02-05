<div class="card card-secondary">
    <div class="card-header">
        {{__('Desiganção')}}
    </div>
    <div class="card-body">
        @if (isset($_GET["tipoDesignacao"]) && is_object($_GET["tipoDesignacao"]))
        {{-- {{dd()}} --}}
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{__('Designação')}}</th>
                        <th>{{__('Ultima')}}</th>
                        <th>{{__('Sala')}}</th>
                        <th>{{__('Demora')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($_GET["tipoDesignacao"] as $k=>$v)
                        <tr>
                            <td><input type="checkbox" name="config[designacao][aceita][]" value="{{$v->id}}" id="aceita_{{$v->id}}"></td>
                            <td>{{$v->nome}}</td>
                            <td><input type="date" name="config[designacao][ultima_{{$v->id}}]" id="dat_ultima_{{$v->id}}"></td>
                            <td>{{__('Sala')}}</td>
                            <td>{{__('Demora')}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="card-footer text-muted">
        Footer
    </div>
</div>
