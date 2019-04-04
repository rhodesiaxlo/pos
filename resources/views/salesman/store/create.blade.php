@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')
<style>
    .tabs input[type=checkbox]{
        margin: 15px 21px 0;
    }
    .tabs{
        width: 100%;
        border: 1px solid #eee;
        overflow: hidden;
        margin: 0;
        padding: 0;
        list-style: none;
        font-size: 12px;
    }
    .tab_title{
        height: 27px;
        position: relative;
        background: #f7f7f7;
    }

    .tab_title ul{
        width:  100%;
        position: absolute;
        left: -40;
    }

    .tab_title li{
        float: left;
        width: 58px;
        height: 26px;
        line-height: 26px;
        text-align: center;
        padding: 0 1px;
        border-bottom: 1px solid #eee;
        overflow: hidden;
    }

    .tabs li a:link,.tab li a:visited{
        text-decoration: none;
        color: #000;
    }

    .tabs li a:hover{
        color: #f90;
        font-weight: 700; /*字体变粗*/
    }
    .tab_title li.selector{
        background: #fff;
        border-bottom-color: #fff;
        border-left: 1px solid #eee;
        border-right: 1px solid #eee;
        padding: 0;
    }
    .tabs .tab_content .tabct {
        margin: 10px 10px 10px 19px;
    }
    .tabs .tab_content .tabct ul li{
        float: right;
        width: 20%;
        height: 25px;
        overflow: hidden;
    }
    .trd{
        border-collapse:separate;
        border-spacing:10px;
    }
</style>
@section('content')
    <div class="main animsition">
        <div class="container-fluid">

            <div class="row">
                <div class="">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">添加门店</h3>
                        </div>
                        <div class="panel-body">

                            @include('admin.partials.errors')
                            @include('admin.partials.success')

                            <form class="form-horizontal" role="form" method="POST" action="{{route('salesman.store.create')}}" onsubmit="return FormVit(this)">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="cove_image"/>
                                <div class="tabs" id="tabs">
                                    <div id="tab_title" class="tab_title">
                                        <ul>
                                            <li class="selector"><a href="#">门店详情</a></li>
                                            <li><a href="#">店主信息</a></li>
                                            <li><a href="#">牌匾信息</a></li>
                                            <li><a href="#">店面特征</a></li>
                                            <li><a href="#">审核记录</a></li>
                                        </ul>
                                    </div>
                                    <div class="tab_content" id="tab_content">
                                        <div class="tabct" style="display: block;">
                                            <table class="table table-bordered">
                                                <tr class="info">
                                                    <td class="col-xs-3">门店id</td>
                                                    <td class="col-xs-3"><input class="form-control" name="store_sn" value="{{old('store_sn',$message['store_id'])}}" type="text" readonly/></td>
                                                    <td class="col-xs-3">原店铺名称</td>
                                                    <td class="col-xs-3"><input class="form-control" name="store_old_name" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">更名后店铺名称</td>
                                                    <td class="col-xs-3"><input class="form-control" name="store_name" type="text"/></td>
                                                    <td class="col-xs-3">店铺地址</td>
                                                    <td class="col-xs-3">
                                                        <span id="distpicker5">
                                                            <label class="sr-only" for="province10">Province</label>
                                                             <select class="form-control" name="province" style="display: inherit; padding: 0px 0px;width: 28%;"  id="province10"></select>
                                                             <label class="sr-only" for="city10">City</label>
                                                             <select class="form-control" name="city" style="display: inherit; padding: 0px 0px;width: 28%;"  id="city10"></select>
                                                             <label class="sr-only" for="district10">District</label>
                                                             <select class="form-control" name="region" style="display: inherit; padding: 0px 0px;width: 28%;"  id="district10"></select>
                                                            </span>
                                                    </td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">地址详情</td>
                                                    <td class="col-xs-3"><input class="form-control"  name="address" type="text"/></td>
                                                    <td class="col-xs-3">开发业务员</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="sales_id">
                                                            <option value="">请选择...</option>
                                                            @foreach($message['sales_id'] as $value)
                                                            <option value="{{$value['id']}}">{{$value['salens_name']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">门店类型</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="store_type">
                                                            <option value="">请选择...</option>
                                                                <option value="1">连锁加盟店</option>
                                                                <option value="2">独立自营店</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">店铺图片</td>
                                                    <td class="col-xs-3">
                                                        <input id="fileA" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none;margin-top: 19px;" alt="" id="previewerA">
                                                        <input type="hidden" name="licenseA" id="licenseA" value=""/>
                                                    </td>
                                                    <td class="col-xs-3">
                                                        <input id="fileB" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none; margin-top: 19px;" alt="" id="previewerB">
                                                        <input type="hidden" name="licenseB" id="licenseB" value=""/>
                                                    </td>
                                                    <td class="col-xs-3">
                                                        <input id="fileF" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none; margin-top: 19px;" alt="" id="previewerF">
                                                        <input type="hidden" name="licenseF" id="licenseF" value=""/>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                                <table class="table table-bordered">
                                                    <tr class="info">
                                                        <td class="col-xs-3">店长姓名</td>
                                                        <td class="col-xs-3"><input class="form-control" name="shopowner_name" type="text"/></td>
                                                        <td class="col-xs-3">性别</td>
                                                        <td class="col-xs-3">
                                                            <select class="form-control" name="shopowner_sex">
                                                                <option value="">请选择</option>
                                                                <option value="1">男</option>
                                                                <option value="2">女</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td class="col-xs-3">年龄</td>
                                                        <td class="col-xs-3">
                                                            <select class="form-control" name="shopowner_age">
                                                                <option value="">请选择</option>
                                                                <option value="1">45岁及以下</option>
                                                                <option value="2">45岁以上</option>
                                                            </select>
                                                        </td>
                                                        <td class="col-xs-3">电话</td>
                                                        <td class="col-xs-3"><input class="form-control" name="shopowner_mobile" type="text"/></td>
                                                    </tr>
                                                    <tr class="active">
                                                        <td class="col-xs-3">备用电话</td>
                                                        <td class="col-xs-3"><input class="form-control" name="tell_phone" type="text"/></td>
                                                        <td class="col-xs-3">微信</td>
                                                        <td class="col-xs-3"><input class="form-control" name="wechat" type="text"/></td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td class="col-xs-3">亲和力</td>
                                                        <td class="col-xs-3">
                                                            <select class="form-control" name="shopowner_affinity">
                                                                <option value="">请选择</option>
                                                                <option value="1">很好</option>
                                                                <option value="2">好</option>
                                                                <option value="3">一般</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr class="info">
                                                        <td class="col-xs-3">营业执照</td>
                                                        <td class="col-xs-3">
                                                            <input id="fileH" type="file">
                                                            <img style="width: 4.5rem;height:4.5rem ;border: none;margin-top: 19px;" alt="" id="previewerH">
                                                            <input type="hidden" name="licenseH" id="licenseH" value=""/>
                                                        </td>
                                                        <td class="col-xs-3">
                                                            <input id="fileL" type="file">
                                                            <img style="width: 4.5rem;height:4.5rem ;border: none; margin-top: 19px;" alt="" id="previewerL">
                                                            <input type="hidden" name="licenseL" id="licenseL" value=""/>
                                                        </td>
                                                        <td class="col-xs-3">
                                                        </td>
                                                    </tr>
                                                </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                            <table class="table table-bordered">
                                                <tr class="info">
                                                    <td class="col-xs-3">主长（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="main_long" type="text"/></td>
                                                    <td class="col-xs-3">左长（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="left_long" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">右长（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control"  name="right_long" type="text"/></td>
                                                    <td class="col-xs-3">主高（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="main_height" type="text"/></td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">左高（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="left_height" type="text"/></td>
                                                    <td class="col-xs-3">右高（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="right_height" type="text"/></td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">面积(㎡)</td>
                                                    <td class="col-xs-3"><input class="form-control" name="acreage" type="text"/></td>
                                                    <td class="col-xs-3">原牌匾材质</td>
                                                    <td class="col-xs-3"><input class="form-control" name="material_quality" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">加盟提供材质</td>
                                                    <td class="col-xs-3"><input class="form-control" name="join_material_quality" type="text"/></td>
                                                    <td class="col-xs-3">牌匾名称</td>
                                                    <td class="col-xs-3"><input class="form-control" name="door_card_name" type="text"/></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                            <table class="table table-bordered" style="overflow: scroll;">
                                                <tr class="info">
                                                    <td class="col-xs-3">营业人员数量（人）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="personnel_num">
                                                            <option value="0" selected>2人及以下</option>
                                                            <option value="1">1-2人</option>
                                                            <option value="2">2-3人</option>
                                                            <option value="3">3-5人/option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">营业面积(㎡)</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="personnel_acreage">
                                                            <option value="1" selected>小于20平</option>
                                                            <option value="2">20-50平</option>
                                                            <option value="3">50-100平</option>
                                                            <option value="4">大于1000平</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">周边便利店数目（个）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="around_store_num">
                                                            <option value="1" selected>0家</option>
                                                            <option value="2">1-3家</option>
                                                            <option value="3">3家以上</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">日营营业额（万元）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="daily_turnover">
                                                            <option value="1" selected>少于1000</option>
                                                            <option value="2">1000-2000</option>
                                                            <option value="3">2000-3000</option>
                                                            <option value="4">多于3000</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">营业时长（小时）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="business_duration">
                                                            <option value="1" selected>非24小时店</option>
                                                            <option value="2">24小时营业店</option>
                                                            <option value="3">存在24小时消费群</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">是否有POS系统</td>
                                                    <td class="col-xs-3">
                                                    <select class="form-control" name="is_pos">
                                                        <option value="1">在用</option>
                                                        <option value="2">有但不使用</option>
                                                        <option value="3">没有</option>
                                                    </select>
                                                    </td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">店主配合程度</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="coordination_type">
                                                            <option value="0">低</option>
                                                            <option value="1">一般</option>
                                                            <option value="2">高</option>
                                                            <option value="3">非常满意</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">店铺地理位置</td>
                                                    <td class="col-xs-3"><input class="form-control" name="shop_location" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">辐射区域</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="radiant_region">
                                                            <option value="">请选择</option>
                                                            @foreach($rest as $list)
                                                                <option value="{{$list->id}}">{{$list->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">店铺年限（年）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="store_use_years">
                                                            <option value="1" >小于1年</option>
                                                            <option value="2" >1-2年</option>
                                                            <option value="3" >2-3年</option>
                                                            <option value="4" >3-5年</option>
                                                            <option value="5" >大于5年</option>

                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">店铺是否连锁</td>
                                                    <td class="col-xs-3">
                                                    <select class="form-control" name="is_chain">
                                                        <option value="0">否</option>
                                                        <option value="1">是</option>
                                                    </select>
                                                    </td>
                                                    <td class="col-xs-3">补货方式</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="replenishment_method">
                                                            <option value="1">自己理货主动下单</option>
                                                            <option value="2">自主订货/依赖业务员补货混合</option>
                                                            <option value="3" selected>依赖业务员补货</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">对订货平台使用态度</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_dinghuo">
                                                            <option value="1">经常网上购物订餐</option>
                                                            <option value="2">会使用一般的订货平台</option>
                                                            <option value="3">不会但有兴趣</option>
                                                            <option value="4">不会且没有兴趣</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">是否对后续服务感兴趣</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_after_sale">
                                                            <option value="1">没有</option>
                                                            <option value="2">有</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">店铺摆放/周边整洁度</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="shop_cleaning">
                                                            <option value="1" >很整洁</option>
                                                            <option value="2" >一般</option>
                                                            <option value="3" >杂乱无章</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">对APP的使用态度</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_app">
                                                            <option value="1">不满意</option>
                                                            <option value="2">一般</option>
                                                            <option value="3">满意</option>
                                                            <option value="4">非常认可</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">货架数量</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="shelf_quantity">
                                                            <option value="1">5组以下</option>
                                                            <option value="2">6-10</option>
                                                            <option value="3">11-20</option>
                                                            <option value="4">20以上</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">冰箱个数</td>
                                                    <td class="col-xs-3"><input class="form-control" name="ice_num" type="text"/></td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">陈列柜</td>
                                                    <td class="col-xs-3"><input class="form-control" name="cabinet_num" type="text"/></td>
                                                    <td class="col-xs-3">其他经营品描述</td>
                                                    <td class="col-xs-3"><input class="form-control" name="ice_num" type="text"/></td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">经营品类</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="business_classification[][" value="1"/>酒水饮料
                                                        <input type="checkbox" name="business_classification[]" value="2" checked/>休闲食品
                                                        <input type="checkbox" name="business_classification[]" value="3"/>方便速食
                                                        <input type="checkbox" name="business_classification[]" value="4"/>冷冻冷藏
                                                        <input type="checkbox" name="business_classification[]" value="5"/>厨房调料
                                                        <input type="checkbox" name="business_classification[]" value="6"/>米面粮油
                                                        <input type="checkbox" name="business_classification[]" value="7"/>纸品日化
                                                        <input type="checkbox" name="business_classification[]" value="8"/>蔬菜
                                                        <input type="checkbox" name="business_classification[]" value="9"/>水果

                                                        <input type="checkbox" name="business_classification[]" value="10"/>关东煮


                                                    </td>
                                                    <td class="col-xs-3">门店期望</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="store_expectations[]" value="便当添加"/>便当添加
                                                        <input type="checkbox" name="store_expectations[]" value="车辆需求" checked/>车辆需求
                                                        <input type="checkbox" name="store_expectations[]" value="廉价供货" />廉价供货
                                                        <input type="checkbox" name="store_expectations[]" value="金融需求" />金融需求
                                                        <input type="checkbox" name="store_expectations[]" value="证照办理" />证照办理
                                                        <input type="checkbox" name="store_expectations[]" value="店面需求" />店面需求
                                                    </td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">是否代收发快递</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="is_express[]" value="1"/>代收
                                                        <input type="checkbox" name="is_express[]" value="2"/>代收
                                                        <input type="checkbox" name="is_express[]" value="3" checked/>代收
                                                    </td>
                                                    <td class="col-xs-3">堆头情况</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="pile_head">
                                                            <option value="1">无堆头位置</option>
                                                            <option value="2">有但不使用</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                   <tr class="info">
                                                    <td class="col-xs-3">房屋属性</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="house_attribute">
                                                            <option value="0" >无</option>
                                                            <option value="1" >租赁</option>
                                                            <option value="2" >自有</option>
                                                        </select>
                                                    <td class="col-xs-3">堆头描述</td>
                                                    <td class="col-xs-3"><input class="form-control" name="pile_head_remarks" type="text" value=""/></td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">辐射区域</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="1" >医院
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="2" >高校/技校/职校
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="3"  >中学
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="4" >小学/幼儿园
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="5" >写字楼
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="6" >高级社区
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="7" >老社区
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="8" >商业街
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="9" >市场(菜市/果市)
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="10" >饭店集中地
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="11" >车站
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="12" >大马路边
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="13" >宾馆集中区域
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="14" >现代化工业区
                                                        <input type="checkbox" name="radiation_region_check_result[]" value="15" >老工厂区域
                                                    </td>
                                                    <td class="col-xs-3">门店编码</td>
                                                    <td class="col-xs-3"><input class="form-control" name="store_code" type="text" value=""/></td>
                                                </tr>


                                            </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                            <table class="table table-bordered">
                                                <tr class="success">
                                                    <td class="col-xs-3">审核</td>
                                                    <td class="col-xs-3">
                                                        <input type="radio" name="is_store_active" value="1" checked/>待审核
                                                        <input type="radio" style="margin-left: 15%;" name="is_store_active" value="2"/>通过
                                                        <input type="radio" style="margin-left: 15%;" name="is_store_active" value="3"/>未通过
                                                        <input type="radio" style="margin-left: 15%;" name="is_store_active" value="4"/>延迟处理

                                                    </td>
                                                    <td class="col-xs-3"></td>
                                                    <td class="col-xs-3"></td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">门店开发标准</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="development_standard[]" value="面积80平米以上"/>面积80平米以上
                                                        <input type="checkbox" name="development_standard[]" value="2名及以上雇员" />2名及以上雇员
                                                        <input type="checkbox" name="development_standard[]" value="2套POS及以上" />2套POS及以上
                                                        <input type="checkbox" name="development_standard[]" value="营业时间16个小时以上" />营业时间16个小时以上
                                                        <input type="checkbox" name="development_standard[]" value="堆头陈列专架等费" />堆头陈列专架等费
                                                        <input type="checkbox" name="development_standard[]" value="有70种烟" />有70种烟
                                                        <input type="checkbox" name="development_standard[]" value="营业时间16个小时以上" />营业时间16个小时以上
                                                        <input type="checkbox" name="development_standard[]" value="20组货架" />20组货架
                                                        <input type="checkbox" name="development_standard[]" value="奶柜" />奶柜
                                                        <input type="checkbox" name="development_standard[]" value="休闲区" />休闲区
                                                        <input type="checkbox" name="development_standard[]" value="辣小鸭" />辣小鸭
                                                    </td>
                                                    <td class="col-xs-3"></td>
                                                    <td class="col-xs-3"></td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">备注</td>
                                                    <td class="col-xs-3"><textarea class="form-control" name="store_ramk" rows="3"></textarea></td>
                                                    <td class="col-xs-3"></td>
                                                    <td class="col-xs-3"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group"  style="margin-right: -100%; margin-left: -10%;">
                                    <div class="col-md-7 col-md-offset-3">
                                        <button type="submit"  class="btn btn-primary btn-md">
                                            <i class="fa fa-plus-circle"></i>
                                            添加
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="/js/distpicker.data.js"></script>
    <script src="/js/distpicker.js"></script>
    <script src="/js/main.js"></script>
    <script type="text/javascript">
    $(function() {
        function $(id) {
            return typeof id == "string" ? document.getElementById(id) : id;
        }
        window.onload = function () {
            var titleName = $("tab_title").getElementsByTagName("li");
            var tabContent = $("tab_content").getElementsByTagName("div");
            if (titleName.length != tabContent.length) {
                return;
            }
            for (var i = 0; i < titleName.length; i++) {
                titleName[i].id = i;
                titleName[i].onclick = function () {
                    for (var j = 0; j < titleName.length; j++) {
                        titleName[j].className = "";
                        tabContent[j].style.display = "none";
                    }
                    this.className = "selector";
                    tabContent[this.id].style.display = "block";
                }
            }
        }

        var upload_file = document.getElementById('fileA');
        var previewer = document.getElementById('previewerA');
        var imgvalue = document.getElementById('licenseA');
        lodup(upload_file, previewer, imgvalue);

        var upload_fileB = document.getElementById('fileB');
        var previewerB = document.getElementById('previewerB');
        var imgvalueB = document.getElementById('licenseB');
        lodup(upload_fileB, previewerB, imgvalueB);

        var upload_fileF = document.getElementById('fileF');
        var previewerF = document.getElementById('previewerF');
        var imgvalueF = document.getElementById('licenseF');
        lodup(upload_fileF, previewerF, imgvalueF);

        var upload_fileH = document.getElementById('fileH');
        var previewerH = document.getElementById('previewerH');
        var imgvalueH = document.getElementById('licenseH');
        lodup(upload_fileH, previewerH, imgvalueH);

        var upload_fileL = document.getElementById('fileL');
        var previewerL = document.getElementById('previewerL');
        var imgvalueL = document.getElementById('licenseL');
        lodup(upload_fileL, previewerL, imgvalueL);

        function lodup(upload_file, previewer, imgvalue) {
            var maxsize = 200 * 1024;

            upload_file.onchange = function () {
                var files = this.files;
                var file = files[0];

                // 接受 jpeg, jpg, png 类型的图片
                if (!/\/(?:jpeg|jpg|png)/i.test(file.type)) return;

                var reader = new FileReader();
                reader.onload = function () {
                    var result = this.result;
                    var img = new Image();

                    // 如果图片小于 200kb，不压缩
                    if (result.length <= maxsize) {
                        toPreviewer(result, previewer, imgvalue);
                        return;
                    }

                    img.onload = function () {
                        var compressedDataUrl = compress(img, file.type);
                        toPreviewer(compressedDataUrl, previewer, imgvalue);
                        img = null;
                    };

                    img.src = result;
                };
                reader.readAsDataURL(file);
            };
        }

        function toPreviewer(dataUrl, previewer, imgvalue) {
            previewer.src = dataUrl;
            imgvalue.value = dataUrl;
            upload_file.value = '';
        }

        function compress(img, fileType) {
            var canvas = document.createElement("canvas");
            var ctx = canvas.getContext('2d');

            var width = img.width;
            var height = img.height;

            canvas.width = width;
            canvas.height = height;

            ctx.fillStyle = "#fff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, width, height);

            // 压缩
            var base64data = canvas.toDataURL(fileType, 0.5);
            canvas = ctx = null;
            return base64data;
        }
    });
    function FormVit(form){
        var store_name=$('input[name=store_name]').val();
        var shopowner_mobile=$('input[name=shopowner_mobile]').val();
        var shopowner_name=$('input[name=shopowner_name]').val();
        var address=$('input[name=address]').val();
        var licenseH=$('input[name=licenseH]').val();
        var licenseL=$('input[name=licenseL]').val();
        var sales_id=$('select[name=sales_id]').val();
        var door_card_name=$('input[name=door_card_name]').val();
        var personnel_num=$('input[name=personnel_num]').val();
        if(store_name==''){
            layer.msg('店铺名称不能为空', {time: 5000, icon:6});
            return false;
        }
        // if(shopowner_name==''){
        //     layer.msg('店主名称不能为空', {time: 5000, icon:6});
        //     return false;
        // }
        if(shopowner_mobile==''){
            layer.msg('手机号不能为空', {time: 5000, icon:6});
            return false;
        }
        if(address==''){
            layer.msg('店铺地址不能为空', {time: 5000, icon:6});
            return false;
        }
        if(licenseH==''){
            layer.msg('营业执照正面不能为空', {time: 5000, icon:6});
            return false;
        }
        // if(licenseL==''){
        //     layer.msg('营业执照背面不能为空', {time: 5000, icon:6});
        //     return false;
        // }
        if(sales_id==''){
            layer.msg('业物员不能为空', {time: 5000, icon:6});
            return false;
        }
        if(!(/^1[34578]\d{9}$/.test(shopowner_mobile))){
            layer.msg('手机号码格式不正确', {time: 5000, icon:6});
            return false;
        }
        // if(door_card_name==''){
        //     //layer.msg('牌匾名称不能为空', {time: 5000, icon:6});
        //     //return false;
        // }
        if(personnel_num==''){
            layer.msg('店铺人数不能为空', {time: 5000, icon:6});
            return false;
        }
        return true;
    }
</script>
    @endsection