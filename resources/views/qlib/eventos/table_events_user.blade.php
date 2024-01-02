<table class="table table-striped table-hover dataTable">
    <thead>
        <tr>
            <th>
                DATA
            </th>
            <th>
                USUÁRIO
            </th>
            <th>
                DESCRIÇÃO
            </th>
            <th>
                AÇÃO
            </th>
        </tr>
    </thead>
    <tbody>
        @if (isset($dados))
            @foreach ($dados['dados'] as $k=>$v)
                @php
                    $conf = App\Qlib\Qlib::lib_json_array($v['config']);
                    $link = false;
                    if(isset($conf['link']) && $conf['link'] !=''){
                        $link = $conf['link'];
                    }
                    // dd($conf);
                    if(isset($v['user_id'])){
                        $du = App\Models\User::find($v['user_id']);
                        if(isset($du['name'])){
                            $nome_user = $du['name'];
                        }
                    }
                @endphp
            <tr>
                <td>
                    {{App\Qlib\Qlib::dataExibe($v['created_at'])}}
                </td>
                <td>
                    {{$nome_user}}
                </td>
                <td>
                    {{$conf['obs']}}
                </td>
                <td>
                    <a href="{{$link}}" title=" {{__('Link Acessado')}} " target="_blank" class="btn btn-default" rel="noopener noreferrer"><i class="fas fa-eye    "></i></a>
                </td>
            </tr>
            @endforeach

        @endif
    </tbody>
</table>
