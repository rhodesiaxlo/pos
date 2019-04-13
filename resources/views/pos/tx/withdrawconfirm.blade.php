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

<div class='bold f20 pos_str_msg'>POS商户应收款结算（划出）对账</div>
<div>
    <div style="font-size:18px;"  class='pos_index_tital white'>查询条件</div>
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
            <td>交易金额差额</td>
            <td>备注</td>
            <td>经办人</td>
            <td>复核人</td>
        </tr>
        @if(!empty($logs))
            <tr>
                <td class='red'>{{$logs->amount}}</td>
                <td>{{$logs->message}}</td>
                <td>{{$logs->admin_name}}</td>
                <td>{{$logs->confirm_name}}</td>
            </tr>
        @endif
        @if(empty($logs))
        <p> 没有数据</p>
        @endif
    </table>
</div>
<div style="margin-top:30px;">
    <div style="font-size:18px;">查询结果</div>
    <p style="color:red;margin: 3px 0;">我方总笔数：238笔   &nbsp;&nbsp; &nbsp; &nbsp;     我方交易总金额：57689元</p>
    <p style="color:red;margin: 3px 0;">支付平台总笔数：236笔   &nbsp;&nbsp; &nbsp; &nbsp;     支付平台交易总金额：57657元</p>
    <div class='fsb'>
        <p style="color:red;margin: 3px 0;">对账总笔数：236笔   &nbsp;&nbsp; &nbsp; &nbsp;     对账交易总金额：57657元</p>
        <div class='fsa' style='width:15%;'>
            <button class='bor-14 mg-l-10 w80px bor-n white bg-blue'>导出</button>
            <button class='bor-14 mg-l-10 w80px bor-n white bg-blue' onclick='alrt(0)'>手工调帐</button>
            <button class='bor-14 mg-l-10 w80px bor-n white bg-blue' onclick='alrt(1)'>复核</button>
        </div>
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
        @if(!empty($logs))
        <table  width="50%" border="1" rules='all' cellpadding="10" class='txal fixed bg-fff' style='top:50%;left:30%;'>
            <tr>
                <td>交易金额差额</td>
                <td class=''> <input type="text" class='red w100pc txal' style='border:none;' id='amount' name='amount' autocomplete="off" value='{{$logs->amount}}' /></td>
            </tr>
            <tr>
                <td >备注：</td>
                <td ><textarea name="value" id="msg" class='w100pc h100pc' style='border:none;' cols="100" rows="10">{{$logs->message}}</textarea></td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type='button' onclick='move()'>取消</button>
                    <button type='button' onclick='return prepayment()' class='mg-l-100' onclick=''>提交</button>    
                </td>
            </tr>
        </table>
        @endif
        @if(empty($logs))
        <table  width="50%" border="1" rules='all' cellpadding="10" class='txal fixed bg-fff' style='top:50%;left:30%;'>
            <tr>
                <td>交易金额差额</td>
                <td class=''> <input type="text" class='red w100pc txal' style='border:none;' id='amount' name='amount' autocomplete="off" value='0' /></td>
            </tr>
            <tr>
                <td >备注：</td>
                <td ><textarea name="value" id="msg" class='w100pc h100pc' style='border:none;' cols="100" rows="10"></textarea></td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type='button' onclick='move()'>取消</button>
                    <button type='button' onclick='return prepayment()' class='mg-l-100' onclick=''>提交</button>    
                </td>
            </tr>
        </table>
        @endif
    </form>
</div>
<div class='fixed bg-fff' id='eor' style='width:26%; left:40%; top:50%; display:none;'>
    <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
</div>

@if(empty($logs))
<input type="hidden" id="testvar"  value='99' />
<input type="hidden" id="testid"  value='0' />
@endif

@if(!empty($logs))
<input type="hidden" id="testvar"  value='{{$logs->status}}' />
<input type="hidden" id="testid"  value='{{$logs->id}}' />
@endif
<input type="hidden" id="seachdate"  value="{{$search['date']}}" />
@stop

@section('js')
<script>

let nume;
$(function(){
    $('.date_picker').date_input();
    var date=$('#seachdate').val()
    $('#date').val(date)
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
    
    var status=$('#testvar').val()
    if(status==2){
        alert('已完成复核')
        move()
        return false;
    }
    if(num==0){
        nume=0
    }else{
        nume=1
    }
}
function prepayment(){
    var amount=$('#amount').val()
    var message=$('#msg').val()
    var date=$('#date').val()
    var tx_type='1343'
    if(nume==0){
        $('#status').val('1')
        var url='/pos/transaction/firstcheck'
        var data={
            date,
            amount,
            tx_type,
            message,
            id:$('#textid').val(),
        }
        ajaxs(url,data,res=>{
            if(res.code==1){
                $('#mov').hide()
                $('#eor').empty()
                $('#eor').show()
                $('#eor').append("<div class='txal w100pc bold' style='height:100px;line-height:100px;'>初审成功</div>")
                $("#eor").fadeOut(3000);
                window.location = "/pos/transaction/depositconfirm?date="+$('#seachdate').val();
            }else{
                alert(res.message)
            }
        })
    }else{
        $('#status').val('2')
        var url='/pos/transaction/recheck'
        var data={
            date,
            amount,
            tx_type,
            message,
            id:$('#textid').val(),
        }
        ajaxs(url,data,res=>{
            // console.log(res)
            if(res.code==1){
                $('#mov').hide()
                $('#eor').empty()
                $('#eor').show()
                $('#eor').append("<div class='txal w100pc bold' style='height:100px;line-height:100px;'>复审成功</div>")
                $("#eor").fadeOut(3000);
                window.location = "/pos/transaction/depositconfirm?date="+$('#seachdate').val();
            }else{
                alert(res.message)
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

