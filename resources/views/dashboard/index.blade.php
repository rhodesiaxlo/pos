@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')
@section('content')
    <link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.7.0/css/amazeui.min.css">
   <style>
         body,div,ul,li{padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 1.4rem;}
        .field-month,.field-year{display: none;}
        .am-selected{width: auto}
        .field>li:not(:first-child){margin-left: 30px}
        .field>li>span{font-weight: 800}
        #container2,#container3{display: none}
    </style>

    <ul class="am-nav am-nav-pills title">
        <li class="am-nav-item am-active" data-value="0"><a href="#">按年统计</a></li>
        <li class="am-nav-item " data-value="1"><a href="#">按月统计</a></li>
        <li class="am-nav-item " data-value="2"><a href="#">按天统计</a></li>
    </ul>
   <input type="hidden" name="select" id="select" value="0"/>
    <input type="hidden" name="selecttype" id="selecttype" value="a"/>
    <input type="hidden" name="saletype" id="saletype" value=""/>
    <input type="hidden" name="year" id="year" value="0"/>
    <input type="hidden" name="month" id="month" value="0"/>
   <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <hr>
    <ul class="am-nav am-nav-pills field">
        <li>
            <span>统计类型：</span>
            <select class="seltype" data-am-selected style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;">
                <option value="m">开发门店数</option>
                <option value="a" selected>业务员数</option>
                <option value="b">促销订单数</option>
                <option value="o">促销订金额</option>
            </select>
        </li>
        <li>
            <span>制定业务员：</span>
            <select data-am-selected id="salenametype" style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;">
                <option value="" selected>选择业务员</option>
                @foreach($result as $k=>$v)
                <option value="{{$v->id}}">{{$v->salens_name}}</option>
               @endforeach
                <option value="d" disabled>禁用鸟</option>
            </select>
        </li>
        <li class="field-year" style="display:none">
            <span>选择年</span>
            <select class="year" data-am-selected style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;">
                <option>请选择年份</option>
                <option value="2015">2015年</option>
                <option value="2016">2016年</option>
                <option value="2017">2017年</option>
                <option value="2018">2018年</option>
                <option value="2019">2019年</option>
                <option value="2020">2020年</option>
                <option value="2021">2021年</option>
                <option value="d" disabled>禁用鸟</option>
            </select>
        </li>
        <li class="field-month" style="display:none">
            <span>选择月</span>
            <select class="month" data-am-selected style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;">
                <option>请选择月份</option>
                <option value="1">一月</option>
                <option value="2">二月</option>
                <option value="3">三月</option>
                <option value="4">四月</option>
                <option value="5">五月</option>
                <option value="6">六月</option>
                <option value="7">七月</option>
                <option value="8">八月</option>
                <option value="9">九月</option>
                <option value="10">十月</option>
                <option value="11">十一月</option>
                <option value="12">十二月</option>
                <option value="d" disabled>禁用鸟</option>
            </select>
        </li>
        <li>
            <span>选择图标类型：</span>
            <select data-am-selected class="form-type" style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;">
                <option value="柱状图" selected>柱状图</option>
                <option value="折线图">折线图</option>
                <option value="饼图">饼图</option>
            </select>
        </li>
    </ul>
    <hr>
    <!-- 柱状图 -->
    <div id="container1" style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;"></div>
    <!-- 折线图 -->
    <div id="container2" style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;"></div>
    <!-- 饼图 -->
    <div id="container3" style="padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 14px;"></div>
    @stop

    @section('js')
        <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.min.js"></script>
        <script src="http://cdn.hcharts.cn/highcharts/highcharts.js"></script>
       <script>
            $(function () {
                $('.am-nav-item').click(function () {
                    $('.am-nav-item').removeClass('am-active');
                    $(this).addClass('am-active');
                    switch ($(this).index()) {
                        case 0:
                            $('.field-year').hide();
                            $('.field-month').hide();
                            break;
                        case 1:
                            $('.field-year').show();
                            $('.field-month').hide();
                            break;
                        case 2:
                            $('.field-year').show();
                            $('.field-month').show();
                            break;
                        default:
                            $('.field-year').hide();
                            $('.field-month').hide();
                            break;
                    }
                });
                /**
                 * 图表类型选择
                 */
                $('.form-type').change(function () {
                    $('#container1').hide();
                    $('#container2').hide();
                    $('#container3').hide();
                    switch ($(this).val()) {
                        case '柱状图':
                            $('#container1').show();
                            break;
                        case '折线图':
                            $('#container2').show();
                            break;
                        case '饼图':
                            $('#container3').show();
                            break;
                    }
                })
                $('.am-nav-item').on('click',function(){
                    var val=$(this).val('data');
                    $('#select').val(val[0].dataset.value);
                    setAjax();
                });
                $('.seltype').on('change',function(){
                   var value=$('.seltype').val();
                    $('#selecttype').val(value);
                    setAjax();
                });
                $('#salenametype').on('change',function(){
                    var value=$('#salenametype').val();
                    $('#saletype').val(value);
                    setAjax();
                });
                $('.year').on('change',function(){
                    var value=$('.year').val();
                    $('#year').val(value);
                    setAjax();
                });
                $('.month').on('change',function(){
                    var value=$('.month').val();
                    $('#month').val(value);
                    setAjax();
                });
                setAjax();
                /**
                 *柱状图 histogram
                 */
                function setAjax(){
                    var keywords=$('#select').val();
                    var seltype=$('#selecttype').val();
                    var saletype=$('#saletype').val();
                    var year=$('#year').val();
                    var month=$('#month').val();
                    $.ajax({
                        url:"{{route('dashboard.dash.index')}}", //你的路由地址
                        type:"post",
                        dataType:"json",
                        data:{keywords:keywords,seltype:seltype,saletype:saletype,year:year,month:month},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        success:function(data){
                            searchUserResponse(data,keywords,seltype);
                        }
                    });
                }
                function searchUserResponse(res,keywords,seltype){
                    var categories=[];
                    var da=[];
                    var types='';
                    if(seltype=='m'){
                        var Ytype='数量 (个)';
                        var titletype='个数/年';
                    }else if(seltype=='a'){
                        var Ytype='人数 (个)';
                        var titletype='人数/年';
                    }else if(seltype=='b'){
                        var Ytype='订单数 (个)';
                        var titletype='订单数/年';
                    }else if(seltype=='o'){
                        var Ytype='金额 (个)';
                        var titletype='金额/年';
                    }
                    if(keywords==0){
                      types='年'
                    }else if(keywords==1){
                        types='月'
                    }
                    for(var i=0;i<res.length;i++){
                        categories[i]=res[i].createTime+types;
                        da[i]=parseInt(res[i].sum);
                    }
                    // '一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'
                    //49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6,54.4
                    var histogram = Highcharts.chart('container1', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: ''
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: {
                            categories,
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text:Ytype
                            }
                        },
                        tooltip: {
                            // head + 每个 point + footer 拼接成完整的 table
                            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                            '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                borderWidth: 0
                            }
                        },
                        series: [{
                            name: titletype,
                            data:da
                        }/*, {
                         name: '纽约',
                         data: [83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6,
                         92.3
                         ]
                         }, {
                         name: '伦敦',
                         data: [48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2]
                         }, {
                         name: '柏林',
                         data: [42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]
                         }*/]
                    });
                    /**
                     *  折线图 lineChart
                     */
                    var lineChart = Highcharts.chart('container2', {
                        title: {
                            text: ''
                        },
                        subtitle: {
                            text: ''
                        },
                        chart: {
                            animation: {
                                duration: 1500,
                                easing: 'easeOutBounce'
                            }
                        },
                        xAxis: {
                            categories:categories
                        },
                        yAxis: {
                            title: {
                                text: '业务员增长'
                            }
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle'
                        },
                       /* plotOptions: {
                            series: {
                                label: {
                                    connectorAllowed: false
                                },
                                pointStart: 0
                            }
                        },*/
                        series: [{
                            name: '业务员数量',
                            data: da
                        }],
                        responsive: {
                            rules: [{
                                condition: {
                                    maxWidth: 500
                                },
                                chartOptions: {
                                    legend: {
                                        layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom'
                                    }
                                }
                            }]
                        }
                    });
                     var mins=[];
                    categories.forEach(function(value,index,array){
                        let obj={};
                        obj['name']=value;
                        obj['y']=da[index];
                        obj['sliced']=true;
                        obj['selected']=true;
                        mins.push(obj);
                    });
                    /**
                     * 饼图 pieChart
                     */
                    var pieChart = Highcharts.chart('container3', {
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                            type: 'pie'
                        },
                        title: {
                            text: ''
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                    style: {
                                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                    }
                                }
                            }
                        },
                        series: [{
                            name: 'Brands',
                            colorByPoint: true,
                            data: mins
                        }]
                    });
                }
            });
        </script>
@stop