@if (isset($config['conf']['semanas']) && ($sem=$config['conf']['semanas']))
@php
    $post_id = request()->segment(2);
    $designacoes = isset($config['conf']['co']['des']) ? $config['conf']['co']['des'] : false;
    if(!$designacoes)
    $designacoes = isset($config['conf']['designacoes']['dds']) ? $config['conf']['designacoes']['dds'] : false;
    $string_designacoes = isset($config['conf']['designacoes']['lista']) ? $config['conf']['designacoes']['lista'] : false;
    $dsalv = isset($config['conf']['dsalv']) ? $config['conf']['dsalv'] : false;
    // $meses = App\Qlib\Qlib::meses();
    if(isset($value['config']['des']) && is_array($value['config']['des'])){
        $value = $value['config']['des'];
    }
    $json_sessoes = App\Qlib\Qlib::qoption('sessoes_designacao');
    $arr_sessoes = App\Qlib\Qlib::lib_json_array($json_sessoes);
    $des2 = isset($config['conf']['desiganations']) ? $config['conf']['desiganations'] : false;
    $sessoes = isset($des2['config']['sessoes']) ? $des2['config']['sessoes'] : $arr_sessoes;
    $arr_participantes = isset($des2['config']['participantes'])?$des2['config']['participantes']:[];
    $config['conf']['arr_desiganacao'] = isset($config['conf']['desiganations']['config']['tipos_designacao']) ? $config['conf']['desiganations']['config']['tipos_designacao'] : [];
    // dd($des2);
    // dd($sessoes);
    $sec = isset($config['conf']['sec']) ? $config['conf']['sec']: false;
    $label_semana = isset($config['conf']['label_semana']) ? $config['conf']['label_semana'] : 'Semana:';
@endphp
<table class="table">
    <thead>
        <tr>
            <th>
                <span>Mês</span>
                <span class="float-right d-print-none">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="recolherTudo();" title="Recolhe semanas e sessões para visão rápida"><i class="fas fa-compress"></i> Recolher tudo</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="expandirTudo();" title="Expande semanas e sessões"><i class="fas fa-expand"></i> Expandir tudo</button>
                </span>
            </th>
            {{-- <th>Desiganções</th> --}}
            {{-- <th>Ação</th> --}}
            {{-- <th>Assembléia</th>
            <th>Visita</th> --}}
        </tr>
    </thead>
    <body>
            @foreach ($sem as $k=>$v)
                @if (is_array($v))
                    @foreach ($v as $k1=>$v1)
                        <tr>
                            {{-- <td>

                            </td> --}}
                            <td>
                                <div class="col-12">
                                    <div class="card card-secondary card-outline card-semana" data-semana="{{$v1}}">
                                        <div class="card-header card-semana-header" style="cursor:pointer;" title="Clique para recolher/expandir a semana">
                                            <h3 class="card-title">
                                                {{$label_semana}}
                                                {{App\Qlib\Qlib::dataExtensso($v1)}} {!!App\Qlib\Qlib::link_programacao_woljw($v1,'<div class=""><a class="underline" href="{link}" target="_BLANK">Acesso à Programação no Jw.ORG</a></div>')!!}
                                            </h3>
                                            @php
                                                $checked_cong = false;
                                                $checked_ass = false;
                                                $checked_visita = false;
                                                if(App\Qlib\Qlib::tem_assembleia($post_id,$v1)){
                                                    $checked_ass = 'checked';
                                                }
                                                if(App\Qlib\Qlib::tem_congresso($post_id,$v1)){
                                                    $checked_cong = 'checked';
                                                }
                                                if(App\Qlib\Qlib::tem_visita($post_id,$v1)){
                                                    $checked_visita = 'checked';
                                                }
                                            @endphp
                                            <div class="card-tools d-print-none">
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Recolher/expandir semana">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <button type="button" data-semanas="{{App\Qlib\Qlib::encodeArray([$v1])}}" data-post_type="{{$sec}}" class="btn btn-sm btn-outline-primary" onclick="designar_auto(this)" title="Preenche as partes vazias desta semana com os participantes elegíveis mais antigos (suas escolhas manuais são mantidas, você ajusta depois)">
                                                    <i class="fa fa-magic" aria-hidden="true"></i> (3) Designar automaticamente
                                                </button>
                                                <label for="congresso_{{$k1}}">
                                                    <input {{$checked_cong}} type="checkbox" name="des2[{{$v1}}][congresso]" id="congresso_{{$k1}}"> {{__('Congresso')}}
                                                </label>
                                                <label for="assembleia_{{$k1}}">
                                                    <input {{$checked_ass}} type="checkbox" name="des2[{{$v1}}][assembleia]" id="assembleia_{{$k1}}"> {{__('Assembléia')}}
                                                </label>
                                                <label for="visita_{{$k1}}">
                                                    <input {{$checked_visita}} type="checkbox" name="des2[{{$v1}}][visita]" id="visita_{{$k1}}"> {{__('Visita')}}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body" id="car-{{$v1}}">
                                            <div style="display: none;" class="d-none designation-template-wrapper">
                                                <ul id="template-{{$v1}}">
                                                    @php
                                                        $ordem = 0;
                                                        $k_sessao = 'inicio';
                                                        $name = 'des2['.$v1.'][partes][' . $k_sessao . '][' . $ordem . ']';
                                                        $designacao = ['id' => '', 'id_designacao' => '', 'id_designado' => '', 'id_ajudante' => '', 'numero' => '', 'obs' => '', 'token' => ''];
                                                    @endphp
                                                    @if ($sec=='fim-semana')
                                                        @include('programa.li_partes_fim')
                                                    @else
                                                        @include('programa.li_partes_meio')
                                                    @endif
                                                </ul>
                                            </div>
                                            @if(isset($des2['programa']) && is_array($des2['programa']))
                                                @php
                                                    $prg = isset($des2['programa'][$v1])?$des2['programa'][$v1]:false;
                                                @endphp
                                                @if(is_array($sessoes))
                                                    @foreach ($sessoes as $k_sessao=>$sessao)
                                                        @if ($sec=='fim-semana')
                                                            @if($k_sessao=='inicio')
                                                            <div class="card card-sessao card-sessao-{{$k_sessao}}" data-sessao="{{$v1}}|{{$k_sessao}}">
                                                                <div class="card-header {{@$sessao['color']}} card-sessao-header" style="cursor:pointer;" title="Clique para recolher/expandir a sessão">
                                                                    {{@$sessao['label']}}
                                                                    <div class="card-tools d-print-none">
                                                                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Recolher/expandir sessão">
                                                                            <i class="fas fa-minus"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="card-body">
                                                                    <ul class="list-group sortable">
                                                                        {{-- @if(is_array($prg)) --}}
                                                                        @if(isset($prg[$k_sessao]) && is_array($prg[$k_sessao]))
                                                                        {{-- {{dd($prg)}} --}}
                                                                            @include('programa.list_edit_desiganacao')
                                                                        @else
                                                                            @if($k_sessao=='inicio')
                                                                                @php
                                                                                    $ordem = 0;
                                                                                    $name = 'des2['.$v1.'][partes][' . $k_sessao . '][' . $ordem . ']';
                                                                                @endphp
                                                                                @if ($sec=='fim-semana')
                                                                                    @include('programa.li_partes_fim')
                                                                                @else
                                                                                    @include('programa.li_partes_meio')
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    </ul>

                                                                </div>
                                                                <div class="card-footer text-muted">
                                                                    <button type="button" class="btn btn-outline-secondary" onclick="add_designation2('{{$v1}}','{{$k_sessao}}');"><i class="fas fa-plus"></i> {{__('Adicionar')}}</button>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @else
                                                            <div class="card card-sessao card-sessao-{{$k_sessao}}" data-sessao="{{$v1}}|{{$k_sessao}}">
                                                                <div class="card-header {{@$sessao['color']}} card-sessao-header" style="cursor:pointer;" title="Clique para recolher/expandir a sessão">
                                                                    {{@$sessao['label']}}
                                                                    <div class="card-tools d-print-none">
                                                                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Recolher/expandir sessão">
                                                                            <i class="fas fa-minus"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="card-body">
                                                                    <ul class="list-group sortable">
                                                                        {{-- @if(is_array($prg)) --}}
                                                                        @if(isset($prg[$k_sessao]) && is_array($prg[$k_sessao]))
                                                                        {{-- {{dd($prg)}} --}}
                                                                            @include('programa.list_edit_desiganacao')
                                                                        @else
                                                                            @if($k_sessao=='inicio')
                                                                                @php
                                                                                    $ordem = 0;
                                                                                    $name = 'des2['.$v1.'][partes][' . $k_sessao . '][' . $ordem . ']';
                                                                                @endphp
                                                                                @if ($sec=='fim-semana')
                                                                                    @include('programa.li_partes_fim')
                                                                                @else
                                                                                    @include('programa.li_partes_meio')
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    </ul>

                                                                </div>
                                                                <div class="card-footer text-muted">
                                                                    <button type="button" class="btn btn-outline-secondary" onclick="add_designation2('{{$v1}}','{{$k_sessao}}');"><i class="fas fa-plus"></i> {{__('Adicionar')}}</button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                {{-- {{dd($prg)}} --}}
                                            @else
                                            <ul class="list-group ul-{{$k1}} sortable" id="ul-{{$v1}}">
                                                    @if (is_object($designacoes))
                                                        @php
                                                            $arr_d = explode(',',$string_designacoes);
                                                        @endphp
                                                        {{-- @foreach ($arr_d as $kde=>$vde) --}}
                                                        @foreach ($designacoes as $kde=>$vde)
                                                            @php
                                                                if(isset($dsalv[$v1][$vde['id']]['config'])){
                                                                    $arr_dsalv=$dsalv[$v1][$vde['id']]['config'];
                                                                }else{
                                                                    $arr_dsalv=[];
                                                                }
                                                            @endphp
                                                            <li class="list-group-item" id="li-{{$v1}}-{{$kde}}" data-key="{{$kde}}">
                                                                <div class="row">
                                                                    <div class="col-3">
                                                                        @isset($config['conf']['arr_desiganacao'])
                                                                            @include('programa.select_designacao',[
                                                                                'arr' => $config['conf']['arr_desiganacao'],
                                                                                'name' => 'config[des]['.$v1.']['.$kde.'][id]',
                                                                                'value'=>@$vde['id'],
                                                                                ])
                                                                        @endisset

                                                                        {{-- <input class="form-control" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id]" value="{{$vde['id']}}" />
                                                                        <input class="form-control no-{{$v1}}" placeholder="{{__('Informe o nome da desiganação')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome]"  value="{{$vde['nome']}}" /> --}}
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control tm-{{$v1}}" type="text" name="config[des][{{$v1}}][{{$kde}}][tema]" placeholder="{{__('Informe o tema da desiganação')}}" value="{{@$arr_dsalv['tema']}}" />
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control id_designado-{{$v1}}" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id_designado]" id="des-id_designado-{{$v1}}-{{$kde}}" value="{{@$arr_dsalv['id_designado']}}" />
                                                                        <input class="form-control autocomplete nome_designado-{{$v1}}" placeholder="{{__('Nome do desiganado')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome_designado]" id="nome_designado-{{$v1}}-{{$kde}}" url_autocomplete="{{route('publicadores.index')}}" value="{{@$arr_dsalv['nome_designado']}}" />
                                                                    </div>
                                                                    <div class="col-1 text-right">
                                                                        <button type="button" title="{{__('Remover')}}" class="btn btn-outline-danger" onclick="remove_designation('li-{{$v1}}-{{$kde}}')"><i class="fas fa-trash"></i></button>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    @elseif (is_array($designacoes) && isset($designacoes[$v1]))
                                                        @foreach ($designacoes[$v1] as $kde=>$vde)
                                                            @php
                                                                if(isset($dsalv[$v1][@$vde['id']]['config'])){
                                                                    $arr_dsalv=$dsalv[$v1][$vde['id']]['config'];
                                                                }else{
                                                                    $arr_dsalv=[];
                                                                }
                                                                // App\Qlib\Qlib::lib_print($arr_dsalv);
                                                            @endphp
                                                            <li class="list-group-item" id="li-{{$v1}}-{{$kde}}" data-key="{{$kde}}">
                                                                <div class="row">
                                                                    <div class="col-12 mb-2">
                                                                        @if(is_array($arr_sessoes))
                                                                        @php
                                                                            $class_select = isset($arr_sessoes[@$vde['sessao']]['color'])?$arr_sessoes[@$vde['sessao']]['color']:false;
                                                                        @endphp
                                                                        <select class="form-control {{$class_select}}" onchange="select_sessao(this);" name="config[des][{{$v1}}][{{$kde}}][sessao]">
                                                                            <option value="">Selecione Sessão que a desiganção faz parte</option>
                                                                            @foreach ($arr_sessoes as $kse=>$vse )
                                                                                @php
                                                                                    $selesec = false;
                                                                                    if($kse==@$vde['sessao']){
                                                                                        $selesec = 'selected';
                                                                                    }
                                                                                @endphp
                                                                                <option class="{{$vse['color']}}" {{$selesec}} value="{{$kse}}">{{$vse['label']}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @endif
                                                                    </div>
                                                                    <div class="col-3">
                                                                        @isset($config['conf']['arr_desiganacao'])
                                                                            @include('programa.select_designacao',[
                                                                                'arr' => $config['conf']['arr_desiganacao'],
                                                                                'name' => 'config[des]['.$v1.']['.$kde.'][id]',
                                                                                'value'=>@$vde['id'],
                                                                                ])
                                                                        @endisset
                                                                        {{-- <input class="form-control" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id]" value="{{@$vde['id']}}" />
                                                                        <input class="form-control no-{{$v1}}" placeholder="{{__('Informe o nome da desiganação')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome]"  value="{{@$vde['nome']}}" /> --}}
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control tm-{{$v1}}" type="text" name="config[des][{{$v1}}][{{$kde}}][tema]" placeholder="{{__('Informe o tema da desiganação')}}" value="{{@str_replace('{','',$arr_dsalv['tema'])}}" />
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <input class="form-control id_designado-{{$v1}}" type="hidden" name="config[des][{{$v1}}][{{$kde}}][id_designado]" id="des-id_designado-{{$v1}}-{{$kde}}" value="{{@$arr_dsalv['id_designado']}}" />
                                                                        <input class="form-control autocomplete nome_designado-{{$v1}}" placeholder="{{__('Nome do desiganado')}}" type="text" name="config[des][{{$v1}}][{{$kde}}][nome_designado]" id="nome_designado-{{$v1}}-{{$kde}}" url_autocomplete="{{route('publicadores.index')}}" value="{{str_replace('{','',@$arr_dsalv['nome_designado'])}}" />
                                                                    </div>
                                                                    <div class="col-1 text-right">
                                                                        <button type="button" title="{{__('Remover')}}" class="btn btn-outline-danger" onclick="remove_designation('li-{{$v1}}-{{$kde}}')"><i class="fas fa-trash"></i></button>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    @endif

                                                </ul>
                                                @endif
                                        </div>
                                        {{-- <div class="card-footer text-muted">
                                            <button type="button" class="btn btn-outline-secondary" onclick="add_designation('{{$v1}}');"><i class="fas fa-plus"></i> {{__('Adicionar')}}</button>
                                        </div> --}}
                                    </div>
                                </div>
                                <input type="hidden" name="prog[{{$v1}}]['data']" value="{{$v1}}">
                            </td>
                        </tr>
                    @endforeach
            @endif
            @endforeach
        {{-- </form> --}}
    </body>
</table>
{{-- {{dd($sem)}} --}}

<script type="text/javascript">
    // Vanilla (sem jQuery): este trecho fica no meio do body e o jQuery
    // só carrega no fim da página (causava "$ is not defined")
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.designation-template-wrapper input, .designation-template-wrapper select, .designation-template-wrapper textarea').forEach(function(el) {
            el.disabled = true;
        });
        // Aplica estado salvo (semanas recolhidas) após o AdminLTE carregar
        try {
            var salvas = JSON.parse(localStorage.getItem('semanas_recolhidas_v1') || '[]');
            if(salvas.length){
                setTimeout(function(){
                    salvas.forEach(function(v1){
                        var card = document.querySelector('.card-semana[data-semana="' + v1 + '"]');
                        if(card && !card.classList.contains('collapsed-card')){
                            var btn = card.querySelector('[data-card-widget="collapse"]');
                            if(btn){ btn.click(); }
                            else { card.classList.add('collapsed-card'); }
                        }
                    });
                }, 300);
            }
        } catch(e){}
        // Clique no cabeçalho (fora de botões/links/inputs) recolhe/expande
        document.querySelectorAll('.card-semana-header').forEach(function(h){
            h.addEventListener('click', function(e){
                if(e.target.closest('button, a, input, label, select')){ return; }
                var card = h.closest('.card-semana');
                if(!card){ return; }
                var btn = card.querySelector(':scope > .card-header [data-card-widget="collapse"], :scope [data-card-widget="collapse"]');
                if(btn){ btn.click(); }
                else { card.classList.toggle('collapsed-card'); }
            });
        });
        // Clique no cabeçalho da sessão recolhe/expande só a sessão
        document.querySelectorAll('.card-sessao-header').forEach(function(h){
            h.addEventListener('click', function(e){
                if(e.target.closest('button, a, input, label, select')){ return; }
                e.stopPropagation();
                var card = h.closest('.card-sessao');
                if(!card){ return; }
                var btn = card.querySelector('[data-card-widget="collapse"]');
                if(btn){ btn.click(); }
                else { card.classList.toggle('collapsed-card'); }
            });
        });
        // Aplica sessões salvas como recolhidas
        try {
            var sesses = JSON.parse(localStorage.getItem('sessoes_recolhidas_v1') || '[]');
            if(sesses.length){
                setTimeout(function(){
                    sesses.forEach(function(k){
                        var sc = document.querySelector('.card-sessao[data-sessao="' + k + '"]');
                        if(sc && !sc.classList.contains('collapsed-card')){
                            var sb = sc.querySelector('[data-card-widget="collapse"]');
                            if(sb){ sb.click(); }
                            else { sc.classList.add('collapsed-card'); }
                        }
                    });
                }, 350);
            }
        } catch(e){}
    });
    function semanasRecolhidasGet(){
        try { return JSON.parse(localStorage.getItem('semanas_recolhidas_v1') || '[]'); }
        catch(e){ return []; }
    }
    function semanasRecolhidasSet(v){
        try { localStorage.setItem('semanas_recolhidas_v1', JSON.stringify(v)); } catch(e){}
    }
    function sessoesRecolhidasGet(){
        try { return JSON.parse(localStorage.getItem('sessoes_recolhidas_v1') || '[]'); }
        catch(e){ return []; }
    }
    function sessoesRecolhidasSet(v){
        try { localStorage.setItem('sessoes_recolhidas_v1', JSON.stringify(v)); } catch(e){}
    }
    function setCard(card, recolher){
        if(!card){ return; }
        var colapsado = card.classList.contains('collapsed-card');
        if(recolher && !colapsado){
            var b1 = card.querySelector('[data-card-widget="collapse"]');
            if(b1){ b1.click(); } else { card.classList.add('collapsed-card'); }
        } else if(!recolher && colapsado){
            var b2 = card.querySelector('[data-card-widget="collapse"]');
            if(b2){ b2.click(); } else { card.classList.remove('collapsed-card'); }
        }
    }
    function setCardSemana(card, recolher){ setCard(card, recolher); }
    function recolherTodasSemanas(){
        document.querySelectorAll('.card-semana').forEach(function(c){ setCard(c, true); });
        var todas = [];
        document.querySelectorAll('.card-semana').forEach(function(c){ todas.push(c.getAttribute('data-semana')); });
        semanasRecolhidasSet(todas);
    }
    function expandirTodasSemanas(){
        document.querySelectorAll('.card-semana').forEach(function(c){ setCard(c, false); });
        semanasRecolhidasSet([]);
    }
    // Fusão: recolhe/expande semanas + sessões de uma vez
    function recolherTudo(){
        recolherTodasSemanas();
        document.querySelectorAll('.card-sessao').forEach(function(c){ setCard(c, true); });
        var todas = [];
        document.querySelectorAll('.card-sessao').forEach(function(c){ todas.push(c.getAttribute('data-sessao')); });
        sessoesRecolhidasSet(todas);
    }
    function expandirTudo(){
        expandirTodasSemanas();
        document.querySelectorAll('.card-sessao').forEach(function(c){ setCard(c, false); });
        sessoesRecolhidasSet([]);
    }
    // Na impressão, expande tudo para não sair conteúdo oculto
    window.addEventListener('beforeprint', function(){
        document.querySelectorAll('.card-semana.collapsed-card, .card-sessao.collapsed-card').forEach(function(c){ setCard(c, false); });
    });
</script>
<script type="text/javascript">
    // Persiste recolhidas via eventos do AdminLTE (quando jQuery disponível)
    (function(){
        function ligar(){
            if(typeof window.jQuery === 'undefined'){ setTimeout(ligar, 300); return; }
            var $ = window.jQuery;
            $(document).on('collapsed.lte.cardwidget', '.card-semana', function(){
                var v = this.getAttribute('data-semana');
                if(!v){ return; }
                var l = semanasRecolhidasGet();
                if(l.indexOf(v) === -1){ l.push(v); semanasRecolhidasSet(l); }
            });
            $(document).on('expanded.lte.cardwidget', '.card-semana', function(){
                var v = this.getAttribute('data-semana');
                if(!v){ return; }
                semanasRecolhidasSet(semanasRecolhidasGet().filter(function(x){ return x !== v; }));
            });
            $(document).on('collapsed.lte.cardwidget', '.card-sessao', function(){
                var k = this.getAttribute('data-sessao');
                if(!k){ return; }
                var l = sessoesRecolhidasGet();
                if(l.indexOf(k) === -1){ l.push(k); sessoesRecolhidasSet(l); }
            });
            $(document).on('expanded.lte.cardwidget', '.card-sessao', function(){
                var k = this.getAttribute('data-sessao');
                if(!k){ return; }
                sessoesRecolhidasSet(sessoesRecolhidasGet().filter(function(x){ return x !== k; }));
            });
        }
        if(document.readyState === 'complete'){ ligar(); }
        else { window.addEventListener('load', ligar); }
    })();
</script>
@endif
