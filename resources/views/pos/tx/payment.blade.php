@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
<style>
    td{
        padding:10px;
    }
</style>

<div class='bold f20 pos_str_msg'>POS商户应收款结算划出</div>
<!-- <?php echo json_encode($search); ?> -->
<form class='' action="{{url::route('pos.transaction.payment')}}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class='pos_index_tital white'>
        <div class='fsb ' style='width:55%;'>
            <div>商家名称  <input class='bor-n bor-4 mg-l-3 ' style='color: black;' type="text" @if($search['storeName']) value="{{$search['storeName']}}" @endif name="storeName" id="name" /></div>
            <div>商家编号  <input class='bor-n bor-4 mg-l-3 ' style='color: black;' type="text" @if($search['storeCode']) value="{{$search['storeCode']}}" @endif name="storeCode" id="code" /></div>
            <div class='gray'><span class='white'>应结算日期</span><input  id='date' name='date' onchange="val()" style='height:;width:226px;color: black;' type="text" placeholder="" autocomplete="off" class='date_picker bor-n bor-4 mg-l-3 ' ></div>
            <div>结算状态  <select class='bor-n bor-4 mg-l-3 black'  name="status" id="status"></select></div>
        </div>
    </div>
    <!-- <input onchange="val()" name='chatTime' style='height:30px;width:226px;' type="text" placeholder="" autocomplete="off" class="date_picker"> -->
    <div>
        <button type='submit' class='bor-14 mg-15 w80px bor-n white bg-blue'>查询</button>
        <span id='clear' class=' bor-14 mg-15 dslb txal w80px bor-n white bg-blue' style='' onclick="" >重置</span>
    </div>
</form>
    <div class='txalE pos_index_tital white'>
        <button type='button' class='bor-14 mg-l-10 w80px bor-n bg-blue' style=''><a href="{{$exporturl}}" class='white a_w'>导出</a></button>
        @if(Gate::forUser(auth('admin')->user())->check('pos.transaction.outflow'))
        <button type='button' onclick='al()' class='bor-14 mg-l-10 w125px bor-n bg-blue' style=''>结算（划出）</button>
        @endif
    </div>
    <table id="box" class="tableA f12 w100pc mg-t-10" style="text-align: center;" border='1' rules='all' >
        <tr class='pos_tr'>
            <td><input id='all' type="checkbox" name="" value=""  /></td>
            <td>商家名称</td>
            <td>商家编号</td>
            <td>交易总金额（元）</td>
            <td>服务费（元）</td>
            <td>结算总金额（元）</td>
            <td>开户行</td>
            <td>商家结算账号</td>
            <td>结算账户户名</td>
            <td>结算（划出）状态</td>
            <td>实际划出时间</td>
        </tr>
        @foreach($outflows as $out)
        <tr>
            <td><input id='' type="checkbox" name="chart" value="{{$out->id}}"  /></td>
            <td class='red'>{{$out->store_name}}</td>
            <td>{{$out->OrderNo}}</td>
            <td>{{$out->Amount/100}}</td>
            <td>0</td>
            <td>{{$out->Amount/100}}</td>
            <td>{{$out->bank->name}}</td>
            <td>{{$out->AccountNumber}}</td>
            <td>{{$out->AccountName}}</td>
            @if($out->status==0)
            <td>初始化</td>
            @endif
            @if($out->status==1)
            <td>成功</td>
            @endif
            @if($out->status==2)
            <td>失败</td>
            @endif
            @if($out->status==40)
            <td>成功</td>
            @endif
            <td>{{$out->notify_time==0?"-":date('Y-m-d H:i:s', $out->notify_time)}}</td>
        </tr>
        @endforeach
    </table>
    <div class='fixed bg-fff pos_aler' id='ok' style='width:26%; left:40%;top:50%;display:none;'>
        <p class='txal bold w100pc pos_aler_title' style='border-bottom:1px solid #999;'>结算划出</p>
        <div class='txal w100pc bold' style='height:100px;line-height:100px;'>
            <p>确定对选中商家作划出结算款？</p>
        </div>
        <div class='fsa mg-b-10'>
            <input type="button" class="mg-l-10 w80px bor-n white " style='background: #d83138;' onclick='return end()' value="是" />
            <input type="button" class="mg-l-10 w80px bor-n white " onclick="$('#ok').hide()" value='否' />
        </div>
    </div>
    <div class='fixed bg-fff' id='eor' style='width:26%; left:40%; top:50%; display:none;'>
        <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
    </div>
    <input type="hidden" id="seachdate"  value="{{$search['date']}}" />
    @if($search['status']!='') 
    <input type="hidden" id="seachStatus"  value="{{$search['status']}}" />
    @endif


@stop 
@section('js')
<script>
        let selectName=[
            {name:'全部',status:'',},
            {name:'已结算',status:1,},
            {name:'未结算',status:0,}
        ]
        $(function(){
            sele()
            var date=$('#seachdate').val()
            $('#date').val(date)
            $('.date_picker').date_input();
            if(!$('#seachStatus').val()){$('#status').val('')}else{$('#status').val($('#seachStatus').val())}
            
        })
        function sele(){
            $('#status').empty()
            for(let i of selectName){
                $('#status').append(`<option value ="${i.status}">${i.name}</option>`)
            }
        }
        function val(e){
            // var start1 = new Date(new Date(new Date().toLocaleDateString()).getTime()-24*60*60*1000);//前天00:00
            var start1 = new Date(new Date(new Date().toLocaleDateString()).getTime());//当天00:00//前天00:00
            var yest1=Date.parse(start1)

            var date=new Date($('.date_picker').val())
            var tod=Date.parse(date)
            if(tod>=yest1){
                eeor('当前时间不可选择','bg-red-2')
                $('.date_picker').val('')
            }
        }
        function al(){
            var list=[]
            $(":checkbox:checked").closest("tr").find("input").each(function(i,e){
                if(e.value!=''){
                    list.push(e.value)
                }
            });
            if(list.length==0){
                eeor('请先选择要清算的账单','bg-red-2')
            }else{
                $('#ok').show()
            }
        }
        function end(){
            var list=[]
            $(":checkbox:checked").closest("tr").find("input").each(function(i,e){
                if(e.value!=''){
                    list.push(e.value)
                }
            });
            var datas = JSON.stringify(list);
            console.log(datas)
            let url='/pos/transaction/outflow'
            let data={
                checked:datas
            }
            ajaxsPost(url,data,res=>{
                if(res.code==1){
                    $('#ok').hide()
                    eeor('清算成功！','bg_green_1')
                    window.location = "/pos/transaction/payment?date="+$('#seachdate').val();
                }else{
                    eeor(res.message,'bg-red-2')
                    // alert(res.message)
                }
            })
        }
        $("#all").click(function(){    
            if(this.checked){    
                $("#box :checkbox").prop("checked", true);   
            }else{    
                $("#box :checkbox").prop("checked", false); 
            }    
        });
        $('#clear').click(function(){
            $('#name').val('')
            $('#code').val('')
            $('#date').val('')
            sele()
        })
</script>
@stop

