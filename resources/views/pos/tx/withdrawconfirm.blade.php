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
        <tr>
            <td>566</td>
            <td>例：今日结账差额为57657元，其中5单为我方无此单，交易金额以第三方支付平台为准</td>
            <td>经办人</td>
            <td>复核人</td>
        </tr>
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
            <td>复核状态</td>
        </tr>
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
    for(let item of list){
        $('#box').append(`<tr style='color:${item.time==11?"red":""}'>
                <td>${item.time}</td>
                <td>${item.time2}</td>
                <td>${item.msg}</td>
                <td>${item.name}</td>
                <td>${item.nameNum}</td>
                <td>${item.myAmount}</td>
                <td>${item.hisAmount}</td>
                <td>${item.staus1}</td>
                <td>${item.staus2}</td>
                <td>${item.staus3}</td>
        </tr>`)
    }
    for(let i=10;i<100; i++){
        $('#selectY').append(`<option value ="${i}">${i}</option>`)
    }
})

</script> 

@stop

