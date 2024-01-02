
    <div class="row">
        <div class="card card-primary card-outline" style="width: 100%">
            <div class="card-header">
                <h3 class="card-title">{{__('Histórico de Acessos')}}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body card-h overflow-auto mw-100 d-flex">
                <ul class="timeline">
                    @foreach ($eventos as $ke=>$ve)
                        @php
                            $conf = App\Qlib\Qlib::lib_json_array($ve['config']);
                            $dt = App\Qlib\Qlib::dataExibe($ve['created_at']);
                            $data = explode('-',$dt);
                            $title = isset($conf['obs'])?$conf['obs']:false;
                            $nome_user = '';
                            if(isset($ve['user_id'])){
                                $du = App\Models\User::find($ve['user_id']);
                                if(isset($du['name'])){
                                    $nome_user = $du['name'];
                                }
                            }
                            // $createdAt = Illuminate\Support\Carbon::parse($item['created_at']);
                            // dd($createdAt);
                        @endphp
                        <li>
                            <a target="_blank" href="{{@$conf['link']}}">Evento</a>
                            <a href="{{route('users.show',['id'=>$ve['user_id']])}}" target="_blank" class="float-right">{{$data[0]}}</a>
                            {{-- <p>&nbsp;</p> --}}
                            <p>{{$nome_user}} <b>{{$title}}</b> as {{$data[1]}} </p>
                        </li>
                    @endforeach
                    {{-- <li>
                        <a href="#">21 000 Job Seekers</a>
                        <a href="#" class="float-right">4 March, 2014</a>
                        <p>Curabitur purus sem, malesuada eu luctus eget, suscipit sed turpis. Nam pellentesque felis vitae justo accumsan, sed semper nisi sollicitudin...</p>
                    </li>
                    <li>
                        <a href="#">Awesome Employers</a>
                        <a href="#" class="float-right">1 April, 2014</a>
                        <p>Fusce ullamcorper ligula sit amet quam accumsan aliquet. Sed nulla odio, tincidunt vitae nunc vitae, mollis pharetra velit. Sed nec tempor nibh...</p>
                    </li> --}}
                </ul>
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>
