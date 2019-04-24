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

<div class='bold f20 pos_str_msg'>POS收银交易对账</div>
<div>
    <div style="font-size:18px;" class='pos_index_tital white'>查询条件</div>
    <div style="margin:10px 20px;">
        <span style="">交易日期</span>
        <form class="dslb mg-l-10" id='form' method="POST" action="{{url::route('pos.transaction.depositconfirm')}}" >
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input onchange="val()" id='date' name='chatTime' style='height:30px;width:226px;' type="text" placeholder="" autocomplete="off" class="date_picker">
            <button class='mg-l-10 w80px bor-n white bg-blue' type='submit' onclick='' style='border-radius:14px;'>查询</button>
        </form>
    </div>
    <table style="text-align: center;" class='w100pc' border='1' rules='all' cellpadding='10'>
        <tr class='pos_tr'>
            <td>交易金额差额(单位：元)</td>
            <td>备注</td>
            <td>经办人</td>
            <td>复核人</td>
        </tr>
        @if(!empty($logs))
        <tr>
            <td class='red'>{{$logs->amount/100}}</td>
            <td>{{$logs->message}}</td>
            <td>{{$logs->admin_name}}</td>
            <td>{{$logs->confirm_name}}</td>
        </tr>
        @endif

        @if(empty($logs))
        <p>没有数据</p>
        @endif
    </table>
</div>
<div style="margin-top:30px;">
    <div style="font-size:18px;" class='pos_index_tital white'>查询结果</div>
    <div class='bg-000 bor-4 mg-bt-10' style=''> 
        @if(!empty($logs))
        <p style="color:red;margin: 3px 0;">&nbsp;&nbsp; &nbsp; &nbsp;<span class='dslb w200px'>我方总笔数：{{$logs->order_num}}笔</span>   &nbsp;&nbsp; &nbsp; &nbsp;     <span class=''>我方交易总金额：{{$logs->order_total/100}}元</span></p>
        <p style="color:red;margin: 3px 0;">&nbsp;&nbsp; &nbsp; &nbsp;<span class='dslb w200px'>支付平台总笔数：{{$logs->log_num}}笔</span>   &nbsp;&nbsp; &nbsp; &nbsp;     <span class=''>支付平台交易总金额：{{$logs->log_total/100}}元</span></p>
        <p style="color:red;margin: 3px 0;">&nbsp;&nbsp; &nbsp; &nbsp;<span class='dslb w200px'>对账总笔数：{{$logs->log_num}}笔</span>   &nbsp;&nbsp; &nbsp; &nbsp;     <span class=''>对账交易总金额：{{$logs->log_total/100}}元</span></p>
        @endif

        @if(empty($logs))
        <p style="color:red;margin: 3px 0;">&nbsp;&nbsp; &nbsp; &nbsp;<span class='dslb w200px'>我方总笔数：0笔    </span>   &nbsp;&nbsp; &nbsp; &nbsp;    <span class=''> 我方交易总金额：0元    </span></p>
        <p style="color:red;margin: 3px 0;">&nbsp;&nbsp; &nbsp; &nbsp;<span class='dslb w200px'>支付平台总笔数：0笔</span>   &nbsp;&nbsp; &nbsp; &nbsp;    <span class=''> 支付平台交易总金额：0元</span> </p>
        <p style="color:red;margin: 3px 0;">&nbsp;&nbsp; &nbsp; &nbsp;<span class='dslb w200px'>对账总笔数：0笔    </span>   &nbsp;&nbsp; &nbsp; &nbsp;    <span class=''> 对账交易总金额：0元    </span> </p>
        @endif
    </div>

    <div class='flex jc-end' style='width:;'>
        <button class='bor-14 mg-l-10 w80px bor-n white bg-blue'>导出</button>
        <button type='button' class='bor-14 mg-l-10 w80px bor-n white bg-blue' onclick='return alrt(0)'>手工调帐</button>
        
        @if(Gate::forUser(auth('admin')->user())->check('admin.permission.create'))
        <button type='button' class='bor-14 mg-l-10 w80px bor-n white bg-blue' onclick='return alrt(1)'>复核</button>
        @endif
    </div>
    <table id="box" class="tableA w100pc mg-t-10" style="text-align: center;" border='1' rules='all' >
        <tr class='pos_tr'>
            <td>流水号</td>
            <td>交易日期</td>
            <td>对账归属日期</td>
            <td>商家名称</td>
            <td>商户编号</td>
            <td>我方交易金额（元）</td>
            <td>支付平台交易金额（元）</td>
            <td>对账状态</td>
            <td>调账状态</td>
            <!-- <td>复核状态</td> -->
        </tr>
        @foreach($prepayments as $prepayment)
        <tr class='{{$prepayment->result_status!=0?"red":""}}'>
            <td>{{$prepayment->serial_no}}</td>
            <td>{{$prepayment->order_time}}</td>
            <td>{{$prepayment->cpcc_time}}</td>
            <td>{{$prepayment->store_name}}</td>
            <td>{{$prepayment->store_code}}</td>
            <td>{{$prepayment->order_amount/100}}</td>
            <td>{{$prepayment->cpcc_amount/100}}</td>
            @if($prepayment->result_status==0)
            <td>对账成功</td>
            @endif
            @if($prepayment->result_status==1)
            <td>对账失败 金额不符</td>
            @endif
            @if($prepayment->result_status==2)
            <td>平台无此订单</td>
            @endif
            @if($prepayment->result_status==3)
            <td>中金无此订单</td>
            @endif
            @if($prepayment->status==0)
            <td>待初审</td>
            @endif
            @if($prepayment->status==1)
            <td>待复审</td>
            @endif
            @if($prepayment->status==2)
            <td>审核完成</td>
            @endif
        </tr>
        @endforeach
    </table>
</div>

<div id='mov' class='' style='display:none;'>
    <form method="POST" onsubmit="return validateForm();" action="{{url::route('pos.transaction.depositconfirm')}}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="status" id='status' value="">
        <div class=''>
            <table  width="50%" border="1" rules='all' cellpadding="10" class='txal fixed bg-fff' style='top:50%;left:30%;'>
                <tr>
                    <td>交易金额差额(元)</td>
                    @if(!empty($logs))
                    <td class=''> <input type="text" class='red w100pc txal' style='border:none;outline: none;' id='amount' name='amount' autocomplete="off" value='{{$logs->amount/100}}' /></td>
                    @endif

                    @if(empty($logs))
                    <td class=''> <input type="text" class='red w100pc txal' style='border:none;outline: none;' id='amount' name='amount' autocomplete="off" value='0' /></td>
                    @endif
                </tr>
                <tr>
                    <td >备注：</td>

                    @if(!empty($logs))
                    <td ><textarea name="value" maxlength="250" id="msg" class='w100pc h100pc' style='border:none;outline: none;' cols="100" rows="10" value=''>{{$logs->message}}</textarea></td>
                    @endif

                    @if(empty($logs))
                    <td ><textarea maxlength="250" name="value" id="msg" class='w100pc h100pc' style='border:none;outline: none;' cols="100" rows="10" value=''></textarea></td>
                    @endif
                </tr>
                <tr>
                    <td colspan="2">
                        <button type='button' class="mg-l-100 w80px bor-n white " onclick='return move()'>取消</button>
                        <button type='button' onclick='return prepayment()' class="mg-l-100 w80px bor-n white " style='background: #d83138;' onclick=''>提交</button>    
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>


@if(empty($logs))
<input type="hidden" id="testvar"  value='99' />
<input type="hidden" id="textid"  value='0' />
@endif

@if(!empty($logs))
<input type="hidden" id="testvar"  value='{{$logs->status}}' />
<input type="hidden" id="textid"  value='{{$logs->id}}' />
@endif

<input type="hidden" id="seachdate"  value="{{$search['date']}}" />

<div class='fixed bg-fff' id='eor' style='width:26%; left:40%; top:50%; display:none;'>
    <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
</div>
@stop

@section('js')
<script>

   let nume; 
$(function(){
    
    var date=$('#seachdate').val()
    $('#date').val(date)
    $('.date_picker').date_input();
})
function val(e){
    var start = new Date(new Date(new Date().toLocaleDateString()).getTime());//当天00:00
    var yest=Date.parse(start)

    var date=new Date($('.date_picker').val())
    var tod=Date.parse(date)
    if(tod>=yest){
        eeor('当前时间不可选择','bg-red-2')
        $('.date_picker').val('')
    }
}

function alrt(num){
    // debugger
    $('#mov').show()
    var status=$('#testvar').val()
    if(status==2){
        move()
        eeor('已完成复核','bg-red-2')
        return false;
    };
    num==0?nume=0:nume=1;
}
function prepayment(){
    var amount=$('#amount').val()
    var message=$('#msg').val()
    var date=$('#date').val()
    var tx_type='1402'
    var id=$('#textid').val()
    if(message==''){
        eeor('请填写备注','bg-red-2')
        return false;
    }
    if(amount==''){
        eeor('请填写金额','bg-red-2')
        return false;
    }
    if(nume==0){
        var url='/pos/transaction/firstcheck'
        var data={
            date,
            amount,
            tx_type,
            message,
            id,
        }
        ajaxs(url,data,res=>{
            if(res.code==1){
                move()
                eeor('初审成功','bg_green_1')
                window.location = "/pos/transaction/depositconfirm?date="+$('#seachdate').val();
            }else{
                eeor(res.message,'bg-red-2')
            }
        })
    }else{
        var url='/pos/transaction/recheck'
        var data={
            date,
            amount,
            tx_type,
            message,
            id,
        }
        ajaxs(url,data,res=>{
            if(res.code==1){
                move()
                eeor('复审成功','bg_green_1')
                window.location = "/pos/transaction/depositconfirm?date="+$('#seachdate').val();
            }else{
                eeor(res.message,'bg-red-2')
            }
        })
    }
}

function move(){
    $('#mov').hide()
}

function validateForm(){
    var d = {};
    var t = $('form').serializeArray();
    $.each(t, function() {
        d[this.name] = this.value;
    });
    if(d.value==''){
        alert('请填写备注');
        // eeor(res.message,'bg-red-2')
        return false;
    }
    if(d.amount==''){
        alert('请填写金额')
        return false;
    }
    return true;
}

</script> 

@stop

