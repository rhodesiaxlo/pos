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

<div class='bold f20'>POS商户应收款结算划出</div>
<div class='fsb' style='width:55%;'>
    <div>商家名称  <input type="text" name="" id="name" /></div>
    <div>商家编号  <input type="text" name="" id="code" /></div>
    <div>应结算日期  <input id='date' onchange="val()" style='height:30px;width:226px;' type="text" placeholder="2019-09-23" class="date_picker"></div>
    <div>结算状态  <select name="" id="staus"></select></div>
</div>
<div><button class='bor-14 mg-15' onclick='chact()'>查询</button><button class='bor-14 mg-15'>重置</button></div>
<div class='bg-gray txalE'><button onclick='end()' class='bor-14 mg-15 bg-blue' style=''>结算（划出）</button></div>
<table id="box" class="tableA f12 w100pc mg-t-10" style="text-align: center;" border='1' rules='all' >
    <tr>
        <td><input id='all' type="checkbox" name="vehicle" value=""  /></td>
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
</table>

@stop
@section('js')
<script>
        let selectName=[
            {name:'全部'},
            {name:'已结算'},
            {name:'未结算'}
        ]
        let list=[
            {
                time:'11',
                time2:33,
                msg:'用户如何放大',
                name:'规范化',
                nameNum:4576373,
                myAmount:45763,
                hisAmount:45763,
                staus1:'none',
                staus2:'none',
                staus3:'none',
                staus3:'none',
                staus3:'none',
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
                staus3:'none',
                staus3:'none',
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
                staus3:'none',
                staus3:'none',
                staus3:'none',
            },
        ]
        $(function(){
            for(let item of selectName){
                $('#staus').append(`<option value ="${item.name}">${item.name}</option>`)
            }
            for(let item of list){
                $('#box').append(`<tr style=''>
                        <td><input type="checkbox" name="" value="${list}"  /></td>
                        <td>${item.time2}</td>
                        <td>${item.msg}</td>
                        <td>${item.name}</td>
                        <td>${item.nameNum}</td>
                        <td>${item.myAmount}</td>
                        <td>${item.hisAmount}</td>
                        <td>${item.staus1}</td>
                        <td>${item.staus2}</td>
                        <td>${item.staus3}</td>
                        <td>${item.staus3}</td>
                    </tr>`)
            }
        })
        $(function(){
            $('.date_picker').date_input();
        })
        function val(e){
            var start1 = new Date(new Date(new Date().toLocaleDateString()).getTime()-24*60*60*1000);//前天00:00
            var yest1=Date.parse(start1)

            var date=new Date($('.date_picker').val())
            var tod=Date.parse(date)
            if(tod>=yest1){
                alert('当前时间不可选择')
                $('.date_picker').val('')
            }
        }
        function chact(){
            var name=$('#name').val(),code=$('#code').val(),date=$('#date').val(),staus=$('#staus').val();
            console.log(name,code,date,staus)
        }
        function end(){
            console.log($("#box :checkbox").prop("checked"))
        }
        $("#all").click(function(){    
            if(this.checked){    
                $("#box :checkbox").prop("checked", true);   
            }else{    
                $("#box :checkbox").prop("checked", false); 
            }    
        });
</script>
@stop

