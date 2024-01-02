@php
    $meses = App\Qlib\Qlib::meses();
    $m = isset($_GET['m'])?$_GET['m'] : date('m');
    $y = isset($_GET['y'])?$_GET['y'] : date('Y');
    $m = App\Qlib\Qlib::zerofill($m,2);
@endphp
<style>
    #lin1{
        display:none;
        margin-top:5px;
    }
    #lin2{
        display:none;
        margin-top:5px;
        text-align: center;
    }
    #lin2 .btn{
        padding: 4px;
    }
</style>

<div class="col-12 d-none title-print" style="border-bottom:1px solid #333;">
    <span style="font-weight:bold;font-size:19px">{{$titulo}} {{$meses[$m]}} - {{$y}}</span>
</div>
<div class="card d-print-none">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-print-none text-center" id="lin0" sec="lin0" style="">
                <a href="javascript:void(0);" class="btn btn-primary" data-ret0="true"><i class="fa fa-arrow-left"></i></a>
                <a href="javascript:void(0);" class="btn btn-primary" data-btn="mes-ano" data-m="{{$m}}" data-y="2022"><m>{{$meses[$m]}}</m> - <y>{{$y}}</y></a>
                <a href="javascript:void(0);" class="btn btn-primary" data-ava0="true"><i class="fa fa-arrow-right"></i></a>
            </div>
            <div class="col-12 d-print-none text-center" style="margin-top: 5px; " id="lin1">
                <a href="javascript:void(0);" class="btn btn-primary" data-ret="true"><i class="fa fa-arrow-left"></i></a>
                <a href="javascript:void(0);" class="btn btn-primary" data-btn="ano" data-y="{{$y}}"><span id="y">{{$y}}</span></a>
                <a href="javascript:void(0);" class="btn btn-primary" data-ava="true"><i class="fa fa-arrow-right"></i></a>
                <span>
                    <button type="type" class="btn btn-default" style="z-index: 120;" onclick="window.print();"><i class="fa fa-print"></i></button>
                </span>
            </div>
            <div class="col-12 d-print-none" id="lin2" style="">
                @if (is_array($meses))
                    @foreach ($meses as $k=>$mes)
                        @php
                            $active = false;
                            if($k==$m){
                                $active = 'active';
                            }
                        @endphp
                        <a href="javascript:void(0);" onclick="acaoPg('{{$k}}');" class="btn btn-primary {{$active}}" data-m="{{$k}}">{{$mes}}</a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
<script>
    window.onload = (ev) =>{
        initMen();
    }
    function acaoPg(mes){
		var ano = document.querySelector('#y').innerHTML;
		//var url = 'https://aeroclubejf.com.br/admin/relatorios?sec=cmVsYXRvcmlvLXZlbmRhcw==' + '&m='+mes+'&y='+ano;
		// var comple = document.querySelector('#comple_url').innerHTML;
		var url = lib_trataAddUrl('m',mes);
		url = lib_trataAddUrl('y',ano,url);
		window.location = url;
	}

    function initMen(){
        const mesAno = document.querySelector('[data-btn="mes-ano"]');
        const ano = document.querySelector('[data-btn="ano"]');
        mesAno.addEventListener('click',function(){
            var btn = 'mes-ano';
            painelFiltroRelatorio(btn);
        });
        ano.addEventListener('click',function(){
            var btn = 'ano';
            painelFiltroRelatorio(btn);
        });
        const ret = document.querySelector('[data-ret="true"]');
        const ava = document.querySelector('[data-ava="true"]');
        ret.addEventListener('click',function(){
            var sY = document.getElementById('y');
            var y = sY.innerHTML;
            var r = new Number(y) - 1;
            sY.innerHTML = r;
        });
        ava.addEventListener('click',function(){
            var sY = document.getElementById('y');
            var y = sY.innerHTML;
            var r = new Number(y) + 1;
            sY.innerHTML = r;
        });
        const ret0 = document.querySelector('[data-ret0="true"]');
        const ava0 = document.querySelector('[data-ava0="true"]');
        ret0.addEventListener('click',function(){
            prevMenu();
        });
        ava0.addEventListener('click',function(){
            nextMenu();
        });
    }
    function prevMenu(){
        const btnMesAno = document.querySelector('[data-btn="mes-ano"]');
        const btnAno = document.querySelector('[data-btn="mes-ano"]');
        var m = btnMesAno.getAttribute('data-m');
        var comp = 0;
        if(m > 0){
            var r1 = new Number(m) - 1;
            if(r1){
                var mex = Meses(r1);
                btnMesAno.setAttribute('data-m',r1);
                document.querySelector('m').innerHTML=mex;
            }else if(r1 == comp){
                m = 12;
                r1 = 12;
                if(r1){
                    var mex = Meses(r1);
                    btnMesAno.setAttribute('data-m',r1);
                    btnAno.setAttribute('data-y',r1);
                    document.querySelector('m').innerHTML=mex;
                    var y = document.querySelector('y').innerHTML;
                    var r = new Number(y) - 1;
                    document.querySelector('y').innerHTML=r;
                    document.querySelector('#y').innerHTML=r;
                }
            }
            acaoPg(r1);
        }
    }
    function nextMenu(){
        const btnMesAno = document.querySelector('[data-btn="mes-ano"]');
        const btnAno = document.querySelector('[data-btn="mes-ano"]');
        var m = btnMesAno.getAttribute('data-m');
        var comp = 13;
        if(m > 0){
            var r1 = new Number(m) + 1;
            if(r1<=12){
                var mex = Meses(r1);
                btnMesAno.setAttribute('data-m',r1);
                document.querySelector('m').innerHTML=mex;
            }else if(r1 == comp){
                m = 12;
                r1 = 12;
                if(r1){
                    var mex = Meses(r1);
                    btnMesAno.setAttribute('data-m',r1);
                    btnAno.setAttribute('data-y',r1);
                    document.querySelector('m').innerHTML=mex;
                    var y = document.querySelector('y').innerHTML;
                    var r = new Number(y) + 1;
                    document.querySelector('y').innerHTML=r;
                    document.querySelector('#y').innerHTML=r;
                }
            }
            acaoPg(r1);
        }
    }
    function painelFiltroRelatorio(btn){
        const lin0 = document.querySelector('#lin0');
        const lin1 = document.querySelector('#lin1');
        const lin2 = document.querySelector('#lin2');
        if(btn=='mes-ano'){
            lin0.style.display='none';
            lin1.style.display='block';
            lin2.style.display='block';
        }else if(btn=='ano'){
            lin0.style.display='block';
            lin1.style.display='none';
            lin2.style.display='none';
        }

    }
    // $(function(){
    //     $('[name="pg"]').on('change',function(){
    //             var val = $(this).val();
    //             abrirRelatorio('relatorios','pg',val,btoa('relatorio-vendas'));
    //     });
    //     $('[quet-ac="crelatorio"]').on('click',function(){
    //             var val = $('[name="pg"]').val();
    //             abrirRelatorio('relatorios','pg',val,btoa('relatorio-vendas'));
    //     });



    //     $('[data-ava0="true"]').on('click',function(){
    //         var m = $('[data-btn="mes-ano"]').data('m');
    //         if(m > 0){
    //             var r1 = new Number(m) + 1;
    //             if(r1<=12){
    //                 var mex = Meses(r1);
    //                 $('[data-btn="mes-ano"]').data('m',r1);
    //                 $('m').html(mex);
    //             }else if(r1 == 13){
    //                 m = 1;
    //                 r1 = 1;
    //                 if(r1){
    //                     var mex = Meses(r1);
    //                     $('[data-btn="mes-ano"]').data('m',r1);
    //                     $('[data-btn="ano"]').data('y',r1);
    //                     $('m').html(mex);
    //                     var y = $('y').html();
    //                     var r = new Number(y) + 1;
    //                     $('y').html(r);
    //                     $('#y').html(r);
    //                 }
    //             }
    //             acaoPg(r1);
    //         }
    //     });
    // });
</script>


