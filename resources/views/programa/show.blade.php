@extends('adminlte::page')

@section('title', $title)

@section('content_header')
    <div class="text-center"><h3>{{$titulo}}</h3></div>
@stop
@php
    $vista = isset($_GET['v'])?$_GET['v']:'quadro';
    $post_id = request()->segment(2);
    $d = isset($designations) ? $designations : false;
    $sessoes = isset($designations['config']['sessoes']) ? $designations['config']['sessoes'] : false;
    $tipos = isset($designations['config']['tipos_designacao']) ? $designations['config']['tipos_designacao'] : false;
    $participantes = isset($designations['config']['participantes']) ? $designations['config']['participantes'] : false;
    $programa = isset($designations['programa']) ? $designations['programa'] : false;
    $mbs1 = isset($config['mbs1']) ? $config['mbs1'] : 0;
    $mbs2 = isset($config['mbs2']) ? $config['mbs2'] : 0;
    $quadro = 1;
    $mbcard = '';
    $q = 1;
    $label_semana = isset($config['label_semana']) ? $config['label_semana'] : 'Semana:';
@endphp
@section('content')
<div class="row mt-0 pt-0 table-responsive">
    <table class="w-100">
        <thead class="mt-0 pt-0">
            <tr>
                <th>
                    <div class="col-12 text-center d-none d-print-block ">
                        @php
                            $post_type = request()->segment(1);
                            if($post_type == 'fim-semana'){
                                $tit = 'fim';
                            }else{
                                $tit = 'meio';
                            }
                        @endphp
                        <h3> {{__('Programação da Reunião de '.$tit.' de semana')}} </h3>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if(is_array($programa))
                        @foreach ($programa as $d_semana=>$semana)
                            @if(is_array($sessoes))
                                @php
                                    $assembleia = false;
                                    $congresso = false;
                                @endphp
                                @foreach ($sessoes as $k_sessao=>$sessao)
                                    @if ($routa=='fim-semana')
                                        @if($k_sessao=='inicio')
                                            @php
                                                $partes = isset($semana[$k_sessao])?$semana[$k_sessao]:false;
                                                if($k_sessao=='inicio'){
                                                    $title = $label_semana.' '. App\Qlib\Qlib::dataExtensso($d_semana);
                                                    $mbcard = '';
                                                }
                                            @endphp
                                            @if ($vista=='quadro')
                                                {{-- visualização para o quando de anuncios --}}
                                                @include('programa.quadro')
                                            @endif
                                        @endif
                                    @else
                                        @php
                                            $partes = isset($semana[$k_sessao])?$semana[$k_sessao]:false;
                                            if($k_sessao=='inicio'){
                                                $title = 'Semana: '. App\Qlib\Qlib::dataExtensso($d_semana);
                                                $checked_ass = false;
                                                $checked_visita = false;
                                                if(App\Qlib\Qlib::tem_assembleia($post_id,$d_semana)){
                                                    $title .= ' <div class="card-tools mr-1">Assembleia</div>';
                                                    $assembleia = true;
                                                }
                                                if(App\Qlib\Qlib::tem_congresso($post_id,$d_semana)){
                                                    $title .= ' <div class="card-tools mr-1">Congresso</div>';
                                                    $congresso = true;
                                                }
                                                if(App\Qlib\Qlib::tem_visita($post_id,$d_semana)){
                                                    $title .= ' <div class="card-tools mr-1">Visita</div>';
                                                }
                                                $mbcard = '';
                                            }else{
                                                $title = $sessao['label'];
                                                $mbcard = '';
                                                if($k_sessao=='final'){
                                                    if($quadro>2){
                                                        $quadro = 1;
                                                    }
                                                    if($quadro==2){
                                                        if($q==2){
                                                            $mbcard = 'style="margin-bottom:'.$mbs1.'vw" data-id="mbs1"';
                                                        }else{
                                                            $mbcard = 'style="margin-bottom:'.$mbs2.'vw" data-id="mbs2"';
                                                        }
                                                        // $title .= $quadro;
                                                        $q++;
                                                    }
                                                    $quadro++;
                                                }else{
                                                    $mbcard = '';
                                                }
                                            }
                                        @endphp
                                        @if ($vista=='quadro')
                                            {{-- visualização para o quando de anuncios --}}
                                            @include('programa.quadro')
                                        @elseif ($vista=='estudante')
                                            {{-- visualização da folha de estudante s89 --}}
                                            @include('programa.estudante')
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>
@include('qlib.btnedit')
@stop

@section('css')
    @include('qlib.csslib')
@stop

@section('js')
    @include('qlib.jslib')
    <script type="text/javascript">
          $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('#inp-cpf,#inp-cpf_conjuge').inputmask('999.999.999-99');
          });
    </script>
@stop

