<div class="card card-secondary">
    <div class="card-header">
        {{__('Desiganção')}}
    </div>
    <div class="card-body">
        @if (isset($_GET["tipoDesignacao"]) && is_object($_GET["tipoDesignacao"]))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{__('Designação')}}</th>
                        <th>{{__('Ultima')}}</th>
                        <th>{{__('Sala')}}</th>
                        <th>{{__('Demora')}}</th>
                    </tr>
                </thead>
                @foreach ($_GET["tipoDesignacao"] as $k=>$v)
                <tbody>
                    <tr>
                        <td>{{$v->nome}}</td>
                        <td><input type="date" name="config[designacao][ultima_{{$v->id}}]" id="dat_ultima_{{$v->id}}"></td>
                        <td>{{__('Sala')}}</td>
                        <td>{{__('Demora')}}</td>
                    </tr>
                </tbody>
                @endforeach
            </table>
        @endif
    </div>
    <div class="card-footer text-muted">
        Footer
    </div>
</div>
