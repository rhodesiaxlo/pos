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

<div class='bold f20'>POS商户应收款结算（划出）对账</div>
<div>
    <div style="font-size:18px;">查询条件</div>
    <div style="margin:10px 20px;">
        <span style="">交易日期</span>
        <form class="" id='form' method="POST" action="{{url::route('pos.transaction.depositconfirm')}}" >
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input onchange="val()" name='chatTime' style='height:30px;width:226px;' type="text" placeholder="" autocomplete="off" class="date_picker">
            <button type='submit' onclick='' style='border-radius:14px;'>查询</button>
        </form>
    </div>
    <table style="text-align: center;" class='w100pc' border='1' rules='all' cellpadding='10'>
        <tr>
            <td>交易金额差额</td>
            <td>备注</td>
            <td>经办人</td>
            <td>复核人</td>
        </tr>
        @foreach($logs as $abnormal_transaction_log)
            <tr>
                <td class='red'>{{$abnormal_transaction_log->amount}}</td>
                <td>{{$abnormal_transaction_log->message}}</td>
                <td>{{$abnormal_transaction_log->admin_name}}</td>
                <td>{{$abnormal_transaction_log->confirm_name}}</td>
            </tr>
        @endforeach
    </table>
</div>
<div style="margin-top:30px;">
    <div style="font-size:18px;">查询结果</div>
    <p style="color:red;margin: 3px 0;">我方总笔数：238笔   &nbsp;&nbsp; &nbsp; &nbsp;     我方交易总金额：57689元</p>
    <p style="color:red;margin: 3px 0;">支付平台总笔数：236笔   &nbsp;&nbsp; &nbsp; &nbsp;     支付平台交易总金额：57657元</p>
    <div class='fsb'>
        <p style="color:red;margin: 3px 0;">对账总笔数：236笔   &nbsp;&nbsp; &nbsp; &nbsp;     对账交易总金额：57657元</p>
        <div class='fsa' style='width:15%;'>
            <button class='bor-14'>导出</button>
            <button class='bor-14' onclick='alrt(0)'>手工调帐</button>
            <button class='bor-14' onclick='alrt(1)'>复核</button>
        </div>
    </div>
    <table id="box" class="tableA w100pc mg-t-10" style="text-align: center;" border='1' rules='all' >
        <tr>
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
            <td>{{$prepayment->order_amount}}</td>
            <td>{{$prepayment->cpcc_amount}}</td>
            @if($prepayment->result_status==0)
            (<td>对账成功</td>)
            @endif
            @if($prepayment->result_status==1)
            (<td>对账失败 金额不符</td>)
            @endif
            @if($prepayment->result_status==2)
            (<td>平台无此订单</td>)
            @endif
            @if($prepayment->result_status==3)
            (<td>中金无此订单</td>)
            @endif
            @if($prepayment->status==0)
            (<td>待初审</td>)
            @endif
            @if($prepayment->status==1)
            (<td>待复审</td>)
            @endif
            @if($prepayment->status==2)
            (<td>审核完成</td>)
            @endif
        </tr>
        @endforeach
    </table>
    
    <div></div>
</div>

<div id='mov' style='display:none;'>
<form method="POST" onsubmit="return validateForm();" action="{{url::route('pos.transaction.depositconfirm')}}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="status" id='status' value="">
    <table  width="50%" border="1" rules='all' cellpadding="10" class='txal fixed bg-fff' style='top:50%;left:30%;'>
        @foreach($logs as $abnormal_transaction_log)
        <tr>
            <td>交易金额差额</td>
            <td class=''> <input type="text" class='red w100pc txal' style='border:none;' name='amount' autocomplete="off" value='{{$abnormal_transaction_log->amount}}' /></td>
        </tr>
        @endforeach
        <tr>
            <td >备注：</td>
            <td ><textarea name="value" id="" class='w100pc h100pc' style='border:none;' cols="100" rows="10"></textarea></td>
        </tr>
        <tr>
            <td colspan="2">
                <button type='button' onclick='move()'>取消</button>
                <button type=submit class='mg-l-100' onclick=''>提交</button>    
            </td>
        </tr>
    </table>
</form>
</div>
@stop

@section('js')
<script>

    
$(function(){
    $('.date_picker').date_input();
})
function val(e){
    var start = new Date(new Date(new Date().toLocaleDateString()).getTime());//当天00:00
    var yest=Date.parse(start)

    var date=new Date($('.date_picker').val())
    var tod=Date.parse(date)
    if(tod>=yest){
        alert('当前时间不可选择')
        $('.date_picker').val('')
    }
}

function alrt(num){
    $('#mov').show()
    // console.log('{{$prepayment->status}}')
    debugger
    if("{{$prepayment->status}}"==2){
        alert('已完成复核')
        move()
        return false;
    }
    if(num==0){
        $('#status').val('1')
    }else{
        $('#status').val('2')
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

