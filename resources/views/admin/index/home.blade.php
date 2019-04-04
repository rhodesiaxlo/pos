@extends('admin.layouts.base')

@section('title','首页')

@section('pageHeader','首页')

@section('pageDesc','首页')

@section('content')

    <link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.7.2/css/amazeui.min.css">
    <style>
        *,body,div,ul,li{padding: 0;margin: 0;box-sizing: border-box;list-style: none;font-size: 16px;}
        .field-month,.field-year{display: none;}
        .am-selected{width: auto}
        .field>li:not(:first-child){margin-left: 30px}
        .field>li>span{font-weight: 550}
        #container2,#container3{display: none}
    </style>

    @include('admin.partials.errors')
    @include('admin.partials.success')

    <body>
    <ul class="am-nav am-nav-pills title">
        <li class="am-nav-item am-active"><a>按年统计</a></li>
        <li class="am-nav-item "><a href="#">按月统计</a></li>
        <li class="am-nav-item "><a href="#">按天统计</a></li>
    </ul>
    <hr>
    <ul class="am-nav am-nav-pills field">
        <li>
            <span>统计类型：</span>
            <select data-am-selected>
                <option value="a">Apple</option>
                <option value="b" selected>Banana</option>
                <option value="o">Orange</option>
                <option value="m">Mango</option>
                <option value="d" disabled>禁用鸟</option>
            </select>
        </li>
        <li>
            <span>制定业务员：</span>
            <select data-am-selected>
                <option value="a">Apple</option>
                <option value="b" selected>Banana</option>
                <option value="o">Orange</option>
                <option value="m">Mango</option>
                <option value="d" disabled>禁用鸟</option>
            </select>
        </li>
        <li class="field-year" style="display:none">
            <span>选择年</span>
            <select data-am-selected>
                <option>请选择年份</option>
                <option value="a">Apple</option>
                <option value="o">Orange</option>
                <option value="m">Mango</option>
                <option value="d" disabled>禁用鸟</option>
            </select>
        </li>
        <li class="field-month" style="display:none">
            <span>选择月</span>
            <select data-am-selected>
                <option>请选择月份</option>
                <option value="a">Apple</option>
                <option value="o">Orange</option>
                <option value="m">Mango</option>
                <option value="d" disabled>禁用鸟</option>
            </select>
        </li>
        <li>
            <span>选择图标类型：</span>
            <select data-am-selected class="form-type">
                <option value="柱状图" selected>柱状图</option>
                <option value="折线图">折线图</option>
                <option value="饼图">饼图</option>
            </select>
        </li>
    </ul>
    <hr>
    <!-- 柱状图 -->
    <div id="container1"></div>
    <!-- 折线图 -->
    <div id="container2"></div>
    <!-- 饼图 -->
    <div id="container3"></div>

    </body>


@stop

@section('js')
    <script src="https://cdn.staticfile.org/jquery/1.11.2/jquery.min.js"></script>
    <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.min.js"></script>
    <script src="http://cdn.hcharts.cn/highcharts/highcharts.js"></script>
    <script type="text/javascript">
       $(function(){
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
        /**
         *柱状图 histogram
         */
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
                categories: [
                    '一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'
                ],
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: '降雨量 (mm)'
                }
            },
            tooltip: {
                // head + 每个 point + footer 拼接成完整的 table
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
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
                name: '东京',
                data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6,
                    54.4
                ]
            }, {
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
            }]
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
            yAxis: {
                title: {
                    text: '就业人数'
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            plotOptions: {
                series: {
                    label: {
                        connectorAllowed: false
                    },
                    pointStart: 2010
                }
            },
            series: [{
                name: '安装，实施人员',
                data: [43934, 52503, 57177, 69658, 97031, 119931, 137133, 154175]
            }, {
                name: '工人',
                data: [24916, 24064, 29742, 29851, 32490, 30282, 38121, 40434]
            }, {
                name: '销售',
                data: [11744, 17722, 16005, 19771, 20185, 24377, 32147, 39387]
            }, {
                name: '项目开发',
                data: [null, null, 7988, 12169, 15112, 22452, 34400, 34227]
            }, {
                name: '其他',
                data: [12908, 5948, 8105, 11248, 8989, 11816, 18274, 18111]
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
                data: [{
                    name: 'Chrome',
                    y: 61.41,
                    sliced: true,
                    selected: true
                }, {
                    name: 'Internet Explorer',
                    y: 11.84
                }, {
                    name: 'Firefox',
                    y: 10.85
                }, {
                    name: 'Edge',
                    y: 4.67
                }, {
                    name: 'Safari',
                    y: 4.18
                }, {
                    name: 'Sogou Explorer',
                    y: 1.64
                }, {
                    name: 'Opera',
                    y: 1.6
                }, {
                    name: 'QQ',
                    y: 1.2
                }, {
                    name: 'Other',
                    y: 2.61
                }]
            }]
        });
       });
    </script>
    @endsection
