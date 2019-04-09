@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
<!-- <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.js"></script>
<script src="/js/jquery.date_input.pack.js"></script> -->
<!-- <link href="/css/globle-px.css" rel="stylesheet"> -->
<style type="text/css"> 

    /*---------------------------------------demo css--------------------------------------------*/

    .date_selector, .date_selector *{width: auto;height: auto;border: none;background: none;margin: 0;padding: 0;text-align: left;text-decoration: none;}

    .date_selector{background:#fbfbfb;border: 1px solid #ccc;padding: 10px;margin:0;margin-top:-1px;position: absolute;z-index:100000;display:none;border-radius: 3px;box-shadow: 0 0 5px #aaa;box-shadow:0 2px 2px #ccc; width:220px;}

    .date_selector_ieframe{position: absolute;z-index: 99999;display: none;}

    .date_selector .nav{width: 17.5em;}

    .date_selector .nav p{clear: none;}

    .date_selector .month_nav, .date_selector .year_nav{margin: 0 0 3px 0;padding: 0;display: block;position: relative;text-align: center;}

    .date_selector .month_nav{float: left;width: 55%;}

    .date_selector .year_nav{float: right;width: 42%;margin-right: -8px;}

    .date_selector .month_name, .date_selector .year_name{font-weight: bold;line-height: 20px;}

    .date_selector .button{display: block;position: absolute;top: 0;width:18px;height:18px;line-height:16px;font-weight:bold;color:#5985c7;text-align: center;font-size:12px;overflow:hidden;border: 1px solid #ccc;border-radius:2px;}

    .date_selector .button:hover, .date_selector .button.hover{background:#5985c7;color: #fff;cursor: pointer;border-color:#3a930d;}

    .date_selector .prev{left: 0;}

    .date_selector .next{right: 0;}

    .date_selector table{border-spacing: 0;border-collapse: collapse;clear: both;margin: 0; width:220px;}

    .date_selector th, .date_selector td{width: 2.5em;height: 2em;padding: 0 !important;text-align: center !important;color: #666;font-weight: normal;}

    .date_selector th{font-size: 12px;}

    .date_selector td{border:1px solid #f1f1f1;line-height: 2em;text-align: center;white-space: nowrap;color:#5985c7;background: #fff;}

    .date_selector td.today{background: #eee;}

    .date_selector td.unselected_month{color: #ccc;}

    .date_selector td.selectable_day{cursor: pointer;}

    .date_selector td.selected{background:#2b579a;color: #fff;font-weight: bold;}

    .date_selector td.selectable_day:hover, .date_selector td.selectable_day.hover{background:#5985c7;color: #fff;}

    /*-----------------------------------------------------------------------------------*/

</style> 
<div>
    <div style="font-size:18px;">查询条件</div>
    <div style="margin:10px 20px;">
        <span style="">交易日期</span>
        <input style='height:30px;' type="text" placeholder="2019-09-23">
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
    <p style="color:red;margin: 3px 0;">对账总笔数：236笔   &nbsp;&nbsp; &nbsp; &nbsp;     对账交易总金额：57657元</p>
    <table id="box" class="tableA w100pc" style="text-align: center;" border='1' rules='all' cellpadding='10'>
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
<input onchange="val()" style="width:226px;background: #fefefe;border: 1px solid #bbb;font-size: 14px;color: #333;padding: 7px;border-radius: 3px;" type="text" class="date_picker">
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

