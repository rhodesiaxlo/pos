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

<?php echo json_encode($prepayments); ?>
<div class='bold f20'>POS收银交易对账</div>
<div>
    <div style="font-size:18px;">查询条件</div>
    <div style="margin:10px 20px;">
        <span style="">交易日期</span>
        <input onchange="val()" style='height:30px;width:226px;' type="text" placeholder="2019-09-23" class="date_picker">
        <!-- <input onchange="val()" style="width:226px;background: #fefefe;border: 1px solid #bbb;font-size: 14px;color: #333;padding: 7px;border-radius: 3px;" type="text" class="date_picker"> -->
        <button style='border-radius:14px;'>查询</button>
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
            <button class='bor-14' onclick='alrt(1)'>手工调帐</button>
            <button class='bor-14' onclick='alrt(2)'>复核</button>
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
    <div class='fsa'>
        <p>每页<select id="selectY"></select>共8条/1页</p>
        <p>
            <span>首页</span>
            <span> 上一页</span>
            <input type="text" style='width:30px;' /> 
            <span> 下一页 </span>
            <span>尾页</span>
        </p>
    </div>
</div>

<div id='mov' style='display:none;'>
    <table  width="50%" border="1" rules='all' cellpadding="10" class='txal fixed bg-fff' style='top:50%;left:30%;'>
        <tr>
            <td>交易金额差额</td>
            <td class='red'>867894764</td>
        </tr>
        <tr>
            <td >备注：</td>
            <td ><textarea name="" id="" class='w100pc h100pc' style='border:none;' cols="100" rows="10"></textarea></td>
        </tr>
        <tr>
            <td colspan="2">
                <button onclick='move()'>取消</button>
                <button class='mg-l-100'>提交</button>    
            </td>
        </tr>
    </table>
</div>
@stop

@section('js')
<script>

    
$(function(){
    $('.date_picker').date_input();
})
function val(e){
    var curDate = new Date();
    var preDate = new Date(curDate.getTime() - 24*60*60*1000); //前一天
    var start = new Date(new Date(new Date().toLocaleDateString()).getTime());//当天00:00
    console.log('当天时间：'+start)
    console.log('当天时间：'+Date.parse(start))
    var yest=Date.parse(start)

    var start1 = new Date(new Date(new Date().toLocaleDateString()).getTime()-24*60*60*1000);//前天00:00
    console.log('前天时间：'+start1)
    console.log('前天时间：'+Date.parse(start1))
    var yest1=Date.parse(start1)

    console.log($('.date_picker').val())
    var date=new Date($('.date_picker').val())
    console.log('选择时间'+Date.parse(date))
    var tod=Date.parse(date)
    if(tod>=yest){
        alert('当前时间不可选择')
        $('.date_picker').val('')
    }
}

function alrt(num){
    $('#mov').show()
    // $("p").hide();
}

function move(){
    $('#mov').hide()
}

let list=[
    {
        time:11,
        time2:33,
        msg:'用户如何放大',
        name:'规范化',
        nameNum:4576373,
        myAmount:45763,
        hisAmount:45763,
        staus1:'none',
        staus2:'none',
        staus3:'none',
    },
    {
        time:12,
        time2:33,
        msg:'用户如何放大',
        name:'规范化',
        nameNum:4576373,
        myAmount:45763,
        hisAmount:45763,
        staus1:'none',
        staus2:'none',
        staus3:'none',
    },
    {
        time:13,
        time2:33,
        msg:'用户如何放大',
        name:'规范化',
        nameNum:4576373,
        myAmount:45763,
        hisAmount:45763,
        staus1:'none',
        staus2:'none',
        staus3:'none',
    },
]

$(function(){
    for(let item of str){
        $('#box').append(`<tr style='color:${item.time==11?"red":""}'>
                <td>${item.serial_no}</td>
                <td>${item.order_time}</td>
                <td>${item.cpcc_time}</td>
                <td>${item.store_name}</td>
                <td>${item.store_code}</td>
                <td>${item.order_amount}</td>
                <td>${item.cpcc_amount}</td>
                <td>${item.result_status}</td>
                <td>${item.status}</td>
        </tr>`)
    }
    for(let i=10;i<100; i++){
        $('#selectY').append(`<option value ="${i}">${i}</option>`)
    }
})

</script> 

@stop

