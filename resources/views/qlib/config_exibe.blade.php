<div class="col-md-12 d-print-none">
    <div class="card">
        <div class="modal fade" id="opcoes" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Editar Opções')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12">
                                    {{App\Qlib\Qlib::qForm([
                                        'type'=>'select',
                                        'campo'=>'limit',
                                        'placeholder'=>'',
                                        'label'=>'Dia da reunião de fim de semana',
                                        'ac'=>'alt',
                                        'value'=>@$config['dia_reuniao_fim_semana'],
                                        'tam'=>'12',
                                        'arr_opc'=>['s'=>'Sábado','d'=>'Domingo'],
                                        'event'=>'onchange=edit_dia_fim_semana(this);',
                                        'option_select'=>false,
                                        'class'=>'text-left',
                                        'class_div'=>'text-left',
                                    ])}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Fechar')}}</button>
                        {{-- <button type="button" class="btn btn-primary">Save</button> --}}
                    </div>
                </div>
            </div>
        </div>
        <form action="" id="frm-consulta" method="GET">
            <div class="row mr-0 ml-0">
                <div class="col-md-4 pt-4 pl-2">
                    @if($routa=='fim-semana')
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#opcoes" title="{{__('Configurar')}}">
                            <i class="fas fa-cogs "></i>
                        </button>
                    @endif
                    <a class="btn @if(isset($_GET['filter'])) btn-link @else btn-default @endif" data-toggle="collapse" href="#busca-id" aria-expanded="false" aria-controls="busca-id">
                        <i class="fas fa-search-location"></i> @if(isset($_GET['filter'])) Mostrar Critérios de pesquisa @else Pesquisar @endif
                    </a>
                </div>
                {{App\Qlib\Qlib::qForm([
                    'type'=>'select',
                    'campo'=>'limit',
                    'placeholder'=>'',
                    'label'=>'Por página',
                    'ac'=>'alt',
                    'value'=>@$config['limit'],
                    'tam'=>'2',
                    'arr_opc'=>['50'=>'50','150'=>'150','200'=>'200','500'=>'500','todos'=>'Todos'],
                    'event'=>'onchange=$(\'#frm-consulta\').submit();',
                    'option_select'=>false,
                    'class'=>'text-center',
                    'class_div'=>'text-center',
                ])}}
                {{App\Qlib\Qlib::qForm([
                    'type'=>'radio',
                    'campo'=>'order',
                    'placeholder'=>'',
                    'label'=>false,
                    'ac'=>'alt',
                    'value'=>@$config['order'],
                    'tam'=>'4',
                    'arr_opc'=>['asc'=>'Ordem crescente','desc'=>'Ordem decrescente'],
                    'event'=>'order=true',
                    'class'=>'btn btn-light',
                    'option_select'=>false,
                    'class_div'=>'pt-4 text-right',
                ])}}
                @can('create',$routa)

                <div class="col-md-2 text-right mt-4">
                    <a href="{{ route($routa.'.create') }}" class="btn btn-success btn-block">
                        <i class="fa fa-plus" aria-hidden="true"></i> Cadastrar
                    </a>
                </div>
                @endcan
                <div class="collapse" id="busca-id">
                    @include('qlib.busca')
                </div>
            </div>
        </form>
    </div>
</div>
