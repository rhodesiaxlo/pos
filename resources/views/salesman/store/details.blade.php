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
        position: absolute;
        left: -1;
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

                            <form class="form-horizontal" role="form" method="POST" action="">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="cove_image"/>
                                <input type="hidden" name="id" value="{{old('id',$message['id'])}}"/>
                                <div class="tabs" id="tabs">
                                    <div id="tab_title" class="tab_title">
                                        <ul>
                                            <li class="selector"><a href="#">门店详情</a></li>
                                            <li><a href="#">店主信息</a></li>
                                            <li><a href="#">牌匾信息</a></li>
                                            <li><a href="#">店面特征</a></li>
                                            <li><a href="#">审核操作</a></li>
                                            <li><a href="#">门店订单</a></li>
                                        </ul>
                                    </div>
                                    <div class="tab_content" id="tab_content">
                                        <div class="tabct" style="display: block;">
                                            <table class="table table-bordered">
                                                <tr class="info">
                                                    <td class="col-xs-3">门店id</td>
                                                    <td class="col-xs-3"><input class="form-control" name="store_sn" value="{{old('store_sn',$message['data']->store_sn)}}" type="text" readonly/></td>
                                                    <td class="col-xs-3">原店铺名称</td>
                                                    <td class="col-xs-3"><input class="form-control" name="store_old_name" value="{{old('store_old_name',$message['data']->store_old_name)}}" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">更名后店铺名称</td>
                                                    <td class="col-xs-3"><input class="form-control" name="store_name" value="{{old('store_name',$message['data']->store_name)}}" type="text"/></td>
                                                    <td class="col-xs-3">店铺地址</td>
                                                    <td class="col-xs-3">
                                                        <span id="distpicker5">
                                                            <label class="sr-only" for="province10">Province</label>
                                                             <select class="form-control" name="province"  style="display: inherit; padding: 0px 0px;width: 28%;"  id="province10">
                                                             </select>
                                                             <label class="sr-only" for="city10">City</label>
                                                             <select class="form-control" name="city" style="display: inherit; padding: 0px 0px;width: 28%;"  id="city10"></select>
                                                             <label class="sr-only" for="district10">District</label>
                                                             <select class="form-control" name="region" style="display: inherit; padding: 0px 0px;width: 28%;"  id="district10"></select>
                                                            </span>
                                                    </td>
                                                </tr>
                                                <input type="hidden"  id="province" value="{{old('province',$message['data']->province)}}"/>
                                                <input type="hidden" id="city" value="{{old('city',$message['data']->city)}}"/>
                                                <input type="hidden"  id="region" value="{{old('region',$message['data']->region)}}"/>
                                                <tr class="active">
                                                    <td class="col-xs-3">地址详情</td>
                                                    <td class="col-xs-3"><input class="form-control"  name="address" value="{{old('address',$message['data']->address)}}" type="text"/></td>
                                                    <td class="col-xs-3">开发业务员</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="sales_id">
                                                            <option value="">请选择...</option>
                                                            @foreach($message['sales_id'] as $value)
                                                                <option value="{{$value['id']}}" @if($message['data']->sales_id == $value['id']) selected="selected" @endif>{{$value['salens_name']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">门店类型</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="store_type">
                                                            <option value="" >请选择...</option>
                                                            <option value="0" @if($message['data']->store_type==1) selected @endif>连锁加盟店</option>
                                                            <option value="1" @if($message['data']->store_type==2) selected @endif>独立自营店</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">店铺图片</td>
                                                    <td class="col-xs-3">
                                                        <input id="fileA" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none;margin-top: 19px;" alt="" id="previewerA" src="{{old('store_thumb',$message['data']->store_thumb)}}">
                                                        <input type="hidden" name="licenseA"  id="licenseA" value=""/>
                                                    </td>
                                                    <td class="col-xs-3">
                                                        <input id="fileB" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none; margin-top: 19px;" alt="" src="{{old('store_thumb',$message['data']->store_img[0])}}"  id="previewerB">
                                                        <input type="hidden" name="licenseB" id="licenseB" value=""/>
                                                    </td>
                                                    <td class="col-xs-3">
                                                        <input id="fileF" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none; margin-top: 19px;" alt="" src="{{old('store_thumb',$message['data']->store_img[1])}}" id="previewerF">
                                                        <input type="hidden" name="licenseF" id="licenseF" value=""/>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                            <table class="table table-bordered">
                                                <tr class="info">
                                                    <td class="col-xs-3">店长姓名</td>
                                                    <td class="col-xs-3"><input class="form-control" name="shopowner_name" value="{{old('shopowner_name',$message['data']->shopowner_name)}}" type="text"/></td>
                                                    <td class="col-xs-3">性别</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="shopowner_sex">
                                                            <option value="">请选择</option>
                                                            <option value="1" @if($message['data']->shopowner_sex==1)selected="selected"@endif>男</option>
                                                            <option value="2" @if($message['data']->shopowner_sex==2)selected="selected"@endif>女</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">年龄</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="shopowner_age">
                                                            <option value="">请选择</option>
                                                            <option value="1" @if($message['data']->shopowner_age==1) selected @endif>45岁及以下</option>
                                                            <option value="2" @if($message['data']->shopowner_age==2) selected @endif>45岁以上</option>
                                                        </select>
                                                    </td>
                                                    </td>
                                                    <td class="col-xs-3">电话</td>
                                                    <td class="col-xs-3"><input class="form-control" name="shopowner_mobile" value="{{old('shopowner_mobile',$message['data']->shopowner_mobile)}}" type="text"/></td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">备用电话</td>
                                                    <td class="col-xs-3"><input class="form-control" name="tell_phone" value="{{old('tell_phone',$message['data']->tell_phone)}}" type="text"/></td>
                                                    <td class="col-xs-3">微信</td>
                                                    <td class="col-xs-3"><input class="form-control" name="wechat" value="{{old('wechat',$message['data']->wechat)}}" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">亲和力</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="shopowner_affinity">
                                                            <option value="">请选择</option>
                                                            <option value="1" @if($message['data']->shopowner_affinity==1) selected @endif>很好</option>
                                                            <option value="2" @if($message['data']->shopowner_affinity==2) selected @endif>好</option>
                                                            <option value="3" @if($message['data']->shopowner_affinity==3) selected @endif>一般</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">营业执照</td>
                                                    <td class="col-xs-3">
                                                        <input id="fileH" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none;margin-top: 19px;" alt="" src="{{old('business_license',$message['data']->business_license[0])}}" id="previewerH">
                                                        <input type="hidden" name="licenseH" id="licenseH" value=""/>
                                                    </td>
                                                    <td class="col-xs-3">
                                                        <input id="fileL" type="file">
                                                        <img style="width: 4.5rem;height:4.5rem ;border: none; margin-top: 19px;" alt="" src="{{old('business_license',$message['data']->business_license[1])}}"  id="previewerL">
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
                                                    <td class="col-xs-3"><input class="form-control" name="main_long" value="{{old('main_long',$message['data']->main_long)}}" type="text"/></td>
                                                    <td class="col-xs-3">左长（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="left_long" value="{{old('left_long',$message['data']->left_long)}}" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">右长（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control"  name="right_long" value="{{old('right_long',$message['data']->right_long)}}" type="text"/></td>
                                                    <td class="col-xs-3">主高（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="main_height" value="{{old('main_height',$message['data']->main_height)}}" type="text"/></td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">左高（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="left_height" value="{{old('left_height',$message['data']->left_height)}}" type="text"/></td>
                                                    <td class="col-xs-3">右高（cm）</td>
                                                    <td class="col-xs-3"><input class="form-control" name="right_height" value="{{old('right_height',$message['data']->right_height)}}" type="text"/></td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">面积(㎡)</td>
                                                    <td class="col-xs-3"><input class="form-control" name="acreage" value="{{old('acreage',$message['data']->acreage)}}" type="text"/></td>
                                                    <td class="col-xs-3">原牌匾材质</td>
                                                    <td class="col-xs-3"><input class="form-control" name="material_quality" value="{{old('material_quality',$message['data']->material_quality)}}" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">加盟提供材质</td>
                                                    <td class="col-xs-3"><input class="form-control" name="join_material_quality" value="{{old('join_material_quality',$message['data']->join_material_quality)}}" type="text"/></td>
                                                    <td class="col-xs-3">牌匾名称</td>
                                                    <td class="col-xs-3"><input class="form-control" name="door_card_name" value="{{old('door_card_name',$message['data']->door_card_name)}}" type="text"/></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                            <table class="table table-bordered">
                                                <tr class="info">
                                                    <td class="col-xs-3">营业人员数量（人）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="personnel_num">
                                                            <option value="0" @if($message['data']->personnel_num==0)selected @endif>2人及以下</option>
                                                            <option value="1" @if($message['data']->personnel_num==1)selected @endif>1-2人</option>
                                                            <option value="2" @if($message['data']->personnel_num==2)selected @endif>2-3人</option>
                                                            <option value="3" @if($message['data']->personnel_num==3)selected @endif>3-5人/option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">营业面积(㎡)</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="personnel_acreage">
                                                            <option value="0" @if($message['data']->personnel_acreage==0) selected @endif>小于20平</option>
                                                            <option value="1" @if($message['data']->personnel_acreage==1) selected @endif>20-50平</option>
                                                            <option value="2" @if($message['data']->personnel_acreage==2) selected @endif>50-100平</option>
                                                            <option value="2" @if($message['data']->personnel_acreage==3) selected @endif>大于1000平</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">周边便利店数目（个）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="around_store_num">
                                                            <option value="0" selected>0</option>
                                                            <option value="1" @if($message['data']->around_store_num==1) selected @endif>1-3家</option>
                                                            <option value="2" @if($message['data']->around_store_num==2) selected @endif>3家以上</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">日营业额（万元）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="daily_turnover">
                                                            <option value="0" @if($message['data']->daily_turnover==0) selected @endif>少于1000</option>
                                                            <option value="1" @if($message['data']->daily_turnover==1) selected @endif>1000-2000</option>
                                                            <option value="2" @if($message['data']->daily_turnover==2) selected @endif>2000-3000</option>
                                                            <option value="3" @if($message['data']->daily_turnover==3) selected @endif>多于3000</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">营业时长（小时）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="business_duration">
                                                            <option value="0" @if($message['data']->business_duration==0) selected @endif>非24小时店</option>
                                                            <option value="1" @if($message['data']->business_duration==1) selected @endif>24小时营业店</option>
                                                            <option value="2" @if($message['data']->business_duration==2) selected @endif>存在24小时消费群</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">是否有POS系统</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_pos">
                                                            <option value="0" @if($message['data']->is_pos==0) selected="selected"@endif>没有</option>
                                                            <option value="1" @if($message['data']->is_pos==1) selected="selected"@endif>有</option>
                                                            <option value="2" @if($message['data']->is_pos==2) selected="selected"@endif>有但不使用</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">店主配合程度</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="coordination_type">
                                                            <option value="0" @if($message['data']->is_pos==0) selected="selected"@endif>低</option>
                                                            <option value="1" @if($message['data']->is_pos==1) selected="selected"@endif>一般</option>
                                                            <option value="2" @if($message['data']->is_pos==2) selected="selected"@endif>高</option>
                                                            <option value="3" @if($message['data']->is_pos==3) selected="selected"@endif>非常满意</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">店铺地理位置</td>
                                                    <td class="col-xs-3"><input class="form-control" name="shop_location" value="{{old('shop_location',$message['data']->shop_location)}}" type="text"/></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">辐射区域</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="radiant_region">
                                                            <option value="">请选择</option>
                                                            @foreach($rest as $list)
                                                                <option value="{{$list->id}}" @if($message['data']->radiant_region==$list->id) selected="selected" @endif>{{$list->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    <td class="col-xs-3">店铺年限（年）</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="store_use_years">
                                                            <option value="0" @if($message['data']->store_use_years==0) selected="selected" @endif>小于1年</option>
                                                            <option value="1" @if($message['data']->store_use_years==1) selected="selected" @endif>多于2年</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-3">店铺是否连锁</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_chain">
                                                            <option value="0" @if($message['data']->is_chain==0)selected="selected"@endif>否</option>
                                                            <option value="1" @if($message['data']->is_chain==1)selected="selected"@endif>是</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">补货方式</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="replenishment_method">
                                                            <option value="自己理货主动下单" @if($message['data']->replenishment_method=='自己理货主动下单')selected="selected"@endif>自己理货主动下单</option>
                                                            <option value="自主订货/依赖业务员补货混合" @if($message['data']->replenishment_method=='自主订货/依赖业务员补货混合')selected="selected"@endif>自主订货/依赖业务员补货混合</option>
                                                            <option value="依赖业务员补货" @if($message['data']->replenishment_method=='依赖业务员补货')selected="selected"@endif>依赖业务员补货</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">是否对订货平台有兴趣</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_dinghuo">
                                                            <option value="0" @if($message['data']->is_dinghuo==0) selected @endif>经常网上购物订餐</option>
                                                            <option value="1" @if($message['data']->is_dinghuo==1) selected @endif>会使用一般的订货平台</option>
                                                            <option value="2" @if($message['data']->is_dinghuo==2) selected @endif>不会但有兴趣</option>
                                                            <option value="3" @if($message['data']->is_dinghuo==3) selected @endif>不会且没有兴趣</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">是否对后续服务感兴趣</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_after_sale">
                                                            <option value="0" @if($message['data']->is_after_sale==0) selected="selected" @endif>没有</option>
                                                            <option value="1" @if($message['data']->is_after_sale==1) selected="selected" @endif>有</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">店铺摆放/周边整洁度</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="shop_cleaning">
                                                            <option value="0" @if($message['data']->shop_cleaning==0) selected="selected" @endif>不整洁</option>
                                                            <option value="1" @if($message['data']->shop_cleaning==1) selected="selected" @endif>一般</option>
                                                            <option value="2" @if($message['data']->shop_cleaning==2) selected="selected" @endif>整洁</option>
                                                            <option value="3" @if($message['data']->shop_cleaning==3) selected="selected" @endif>非常整洁</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">对APP的使用态度</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="is_app">
                                                            <option value="0" @if($message['data']->is_app==0) selected="selected" @endif>不满意</option>
                                                            <option value="1" @if($message['data']->is_app==1) selected="selected" @endif>一般</option>
                                                            <option value="2" @if($message['data']->is_app==2) selected="selected" @endif>满意</option>
                                                            <option value="3" @if($message['data']->is_app==3) selected="selected" @endif>非常认可</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-3">货架数量</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="shelf_quantity">
                                                            <option value="0" @if($message['data']->shelf_quantity==0) selected @endif>5组以下</option>
                                                            <option value="1" @if($message['data']->shelf_quantity==1) selected @endif>6-10</option>
                                                            <option value="2" @if($message['data']->shelf_quantity==2) selected @endif>11-20</option>
                                                            <option value="3" @if($message['data']->shelf_quantity==3) selected @endif>20以上</option>
                                                        </select>
                                                    </td>
                                                    <td class="col-xs-3">冰箱个数</td>
                                                    <td class="col-xs-3"><input class="form-control" name="ice_num" type="text" value="{{$message['data']->ice_num}}"/></td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">陈列柜</td>
                                                    <td class="col-xs-3"><input class="form-control" name="cabinet_num" type="text" value="{{$message['data']->cabinet_num}}"/></td>
                                                    <td class="col-xs-3">其他经营品描述</td>
                                                    <td class="col-xs-3"><input class="form-control" name="business_description" type="text" value="{{$message['data']->business_description}}"/></td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">经营品类</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="business_classification[]" value="酒水饮料"  @if(in_array('酒水饮料',$message['data']->business_classification)) checked @endif/>酒水饮料
                                                        <input type="checkbox" name="business_classification[]" value="休闲食品" @if(in_array('休闲食品',$message['data']->business_classification)) checked @endif/>休闲食品
                                                        <input type="checkbox" name="business_classification[]" value="方便速食" @if(in_array('方便速食',$message['data']->business_classification)) checked @endif/>方便速食
                                                        <input type="checkbox" name="business_classification[]" value="酒水饮料" @if(in_array('酒水饮料',$message['data']->business_classification)) checked @endif/>酒水饮料
                                                        <input type="checkbox" name="business_classification[]" value="冷冻冷藏" @if(in_array('冷冻冷藏',$message['data']->business_classification)) checked @endif/>冷冻冷藏
                                                        <input type="checkbox" name="business_classification[]" value="厨房调料" @if(in_array('厨房调料',$message['data']->business_classification)) checked @endif/>厨房调料
                                                        <input type="checkbox" name="business_classification[]" value="米面粮油" @if(in_array('米面粮油',$message['data']->business_classification)) checked @endif/>米面粮油
                                                        <input type="checkbox" name="business_classification[]" value="纸品日化" @if(in_array('纸品日化',$message['data']->business_classification)) checked @endif/>纸品日化
                                                        <input type="checkbox" name="business_classification[]" value="蔬菜" @if(in_array('蔬菜',$message['data']->business_classification)) checked @endif/>蔬菜
                                                        <input type="checkbox" name="business_classification[]" value="水果" @if(in_array('水果',$message['data']->business_classification)) checked @endif/>水果
                                                        <input type="checkbox" name="business_classification[][" value="酒水饮料" @if(in_array('酒水饮料',$message['data']->business_classification)) checked @endif/>生鲜
                                                        <input type="checkbox" name="business_classification[]" value="关东煮" @if(in_array('关东煮',$message['data']->business_classification)) checked @endif/>关东煮
                                                    </td>
                                                    <td class="col-xs-3">门店期望</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="store_expectations[]" value="便当添加"  @if(in_array('便当添加',$message['data']->store_expectations)) checked @endif/>便当添加
                                                        <input type="checkbox" name="store_expectations[]" value="车辆需求"  @if(in_array('车辆需求',$message['data']->store_expectations)) checked @endif/>车辆需求
                                                        <input type="checkbox" name="store_expectations[]" value="廉价供货"  @if(in_array('廉价供货',$message['data']->store_expectations)) checked @endif/>廉价供货
                                                        <input type="checkbox" name="store_expectations[]" value="金融需求"  @if(in_array('金融需求',$message['data']->store_expectations)) checked @endif/>金融需求
                                                        <input type="checkbox" name="store_expectations[]" value="证照办理"  @if(in_array('证照办理',$message['data']->store_expectations)) checked @endif/>证照办理
                                                        <input type="checkbox" name="store_expectations[]" value="店面需求"  @if(in_array('店面需求',$message['data']->store_expectations)) checked @endif/>店面需求
                                                    </td>
                                                </tr>
                                                <tr class="info">
                                                    <td class="col-xs-3">是否代收发快递</td>
                                                    <td class="col-xs-3">
                                                        <input type="checkbox" name="is_express[]" value="代收" @if(in_array('代收',$message['data']->is_express)) checked @endif/>代收
                                                        <input type="checkbox" name="is_express[]" value="无"  @if(in_array('无',$message['data']->is_express)) checked @endif/>无
                                                    </td>
                                                    <td class="col-xs-3">堆头情况</td>
                                                    <td class="col-xs-3">
                                                        <select class="form-control" name="pile_head">
                                                            <option value="0" @if($message['data']->pile_head==0) selected @endif>无堆头位置</option>
                                                            <option value="1" @if($message['data']->pile_head==1) selected @endif>有但不使用</option>
                                                            <option value="2" @if($message['data']->pile_head==2) selected @endif>有推头的填写使用品牌</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                            <table class="table table-bordered">
                                                <tr class="success">
                                                    <td class="col-xs-2"></td>
                                                    <td class="col-xs-2"></td>
                                                    <td class="col-xs-2"></td>
                                                    <td class="col-xs-2"></td>
                                                    <td class="col-xs-2"></td>
                                                </tr>
                                                <tr class="active">
                                                    <td class="col-xs-2">门店开发标准</td>
                                                    <td class="col-xs-2">
                                                        <input type="checkbox" name="development_standard[]" value="面积80平米以上" @if(in_array('面积80平米以上',$message['data']->development_standard)) checked @endif/>面积80平米以上
                                                        <input type="checkbox" name="development_standard[]" value="2名及以上雇员"  @if(in_array('2名及以上雇员',$message['data']->development_standard)) checked @endif/>2名及以上雇员
                                                        <input type="checkbox" name="development_standard[]" value="2套POS及以上"  @if(in_array('2套POS及以上',$message['data']->development_standard)) checked @endif/>2套POS及以上
                                                        <input type="checkbox" name="development_standard[]" value="营业时间16个小时以上" @if(in_array('营业时间16个小时以上',$message['data']->development_standard)) checked @endif/>营业时间16个小时以上
                                                        <input type="checkbox" name="development_standard[]" value="堆头陈列专架等费" @if(in_array('堆头陈列专架等费',$message['data']->development_standard)) checked @endif/>堆头陈列专架等费
                                                        <input type="checkbox" name="development_standard[]" value="有70种烟" @if(in_array('有70种烟',$message['data']->development_standard)) checked @endif/>有70种烟
                                                        <input type="checkbox" name="development_standard[]" value="营业时间16个小时以上" @if(in_array('营业时间16个小时以上',$message['data']->development_standard)) checked @endif/>营业时间16个小时以上
                                                        <input type="checkbox" name="development_standard[]" value="20组货架" @if(in_array('20组货架',$message['data']->development_standard)) checked @endif/>20组货架
                                                        <input type="checkbox" name="development_standard[]" value="奶柜" @if(in_array('奶柜',$message['data']->development_standard)) checked @endif/>奶柜
                                                        <input type="checkbox" name="development_standard[]" value="休闲区" @if(in_array('休闲区',$message['data']->development_standard)) checked @endif/>休闲区
                                                        <input type="checkbox" name="development_standard[]" value="辣小鸭" @if(in_array('辣小鸭',$message['data']->development_standard)) checked @endif/>辣小鸭
                                                    </td>
                                                    <td class="col-xs-2"></td>
                                                    <td class="col-xs-2"></td>
                                                    <td class="col-xs-2"></td>
                                                </tr>
                                                <tr class="success">
                                                    <td class="col-xs-2">ID</td>
                                                    <td class="col-xs-2">审核时间</td>
                                                    <td class="col-xs-2">审核人</td>
                                                    <td class="col-xs-2">审核状态</td>
                                                    <td class="col-xs-2">审核备注</td>
                                                </tr>
                                                @foreach($message['log'] as $v)
                                                    <tr class="active">
                                                        <td class="col-xs-2">{{$v->id}}</td>
                                                        <td class="col-xs-2">{{$v->created_at}}</td>
                                                        <td class="col-xs-2">{{$v->name}}</td>
                                                        <td class="col-xs-2">@if($v->is_active==0)未审核@elseif($v->is_active==1)<span style="color:#00c0ef;">审核通过</span>@else<span style="color: #9f191f;">未通过</span>@endif</td>
                                                        <td class="col-xs-2">{{$v->ramk}}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                        <div class="tabct" style="display: none;">
                                            <table id="tags-table" class="table table-striped table-bordered">
                                                <thead>
                                                <tr>
                                                    {{-- <th data-sortable="false" class="hidden-sm"></th>--}}
                                                    <th class="hidden-sm">序号</th>
                                                    <th class="hidden-sm">订单编号</th>
                                                    <th class="hidden-sm">下单门店</th>
                                                    <th class="hidden-md">促销员</th>
                                                    <th class="hidden-md">下单时间</th>
                                                    <th class="hidden-md">订单总金额</th>
                                                    <th class="hidden-md">实付总金额</th>
                                                    <th class="hidden-md">结算状态</th>
                                                    <th class="hidden-md">备注</th>
                                                    <th data-sortable="false">操作</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="promoters_id" attr="{{$message['data']->id}}" value="{{$message['data']->id}}"/>
                                 <input type="hidden" id="cid" attr="0" >
                                {{-- <div class="form-group">
                                     <div class="col-md-7 col-md-offset-3">
                                         <button type="submit" class="btn btn-primary btn-md">
                                             <i class="fa fa-plus-circle"></i>
                                             添加
                                         </button>
                                     </div>
                                 </div>--}}

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
        $(document).ready(function($) {
            function $(id) {
                return typeof id == "string" ? document.getElementById(id) : id;
            }
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
        });
        $(document).ready(function($) {
            var province=$('#province').val();
            var city=$('#city').val();
            var region=$('#region').val();
            $("#province10").find("option[value='"+province+"']").attr("selected",true);
            var option = $("<option selected>").val(city).text(city);
            $("#city10").append(option);
            var option1 = $("<option selected>").val(region).text(region);
            $("#district10").append(option1);
        });
    </script>
    <script>
        $(function () {
            var cid = $('#cid').attr('attr');
            var promoters = $('#promoters_id').attr('attr');
            var table = $("#tags-table").DataTable({
                language: {
                    "sProcessing": "处理中...",
                    "sLengthMenu": "显示 _MENU_ 项结果",
                    "sZeroRecords": "没有匹配结果",
                    "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                    "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                    "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                    "sInfoPostFix": "",
                    "sSearch": "搜索:",
                    "sUrl": "",
                    "sEmptyTable": "表中数据为空",
                    "sLoadingRecords": "载入中...",
                    "sInfoThousands": ",",
                    "oPaginate": {
                        "sFirst": "首页",
                        "sPrevious": "上页",
                        "sNext": "下页",
                        "sLast": "末页"
                    },
                    "oAria": {
                        "sSortAscending": ": 以升序排列此列",
                        "sSortDescending": ": 以降序排列此列"
                    }
                },
                order: [[5, "asc"]],
                serverSide: true,

                ajax: {
                    url: "{{route('salesman.order.index')}}",
                    type: 'GET',
                    data: function (d) {
                        d.cid = cid
                        d.promoters=promoters;
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                },
                "columns": [
                    {"data": "order_id"},
                    {"data": "order_sn"},
                    {"data": "user_name"},
                    {"data": "promoters"},
                    {"data": "add_time"},
                    {"data": "order_amount"},
                    {"data": "pay_fee"},
                    {"data": "order_status"},
                    {"data": "pay_name"},
                    {"data": "order_sn"}
                ],
                columnDefs: [
                    {
                        'targets': -1, "render": function (data, type, row) {
                        var row_edit = {{Gate::forUser(auth('admin')->user())->check('admin.permission.edit') ? 1 : 0}};
                        var row_delete = {{Gate::forUser(auth('admin')->user())->check('admin.permission.destroy') ? 1 :0}};
                        var str = '';
                        /* //编辑
                         if (row_edit) {
                         str += '<a style="margin:3px;" href="/salesman/sales/' + row['order_id'] + '/edit" class="X-Small btn-xs text-success "><i class="fa fa-edit"></i> 编辑</a>';
                         }*/

                        //删除
                        if (row_delete) {
                            str += '<a style="margin:3px;" href="#" attr="' + row['order_id'] + '" class="delBtn X-Small btn-xs text-danger"><i class="fa fa-times-circle"></i> 删除</a>';
                        }

                        return str;

                    }
                    }
                ]
            });

            table.on('preXhr.dt', function () {
                loadShow();
            });

            table.on('draw.dt', function () {
                loadFadeOut();
            });

            table.on('order.dt search.dt', function () {
                table.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            $("table").delegate('.delBtn', 'click', function () {
                var id = $(this).attr('attr');
                $('.deleteForm').attr('action', '/salesman/order/destroy/'+ id);
                $("#modal-delete").modal();
            });
            $('.btn-md').on('click',function(){
                var url="{{route('salesman.sales.create',['cid'=>1])}}";
                layer.open({
                    type: 2,
                    shadeClose: false,
                    shade: false,
                    maxmin: true, //开启最大化最小化按钮
                    area: ['400px', '600px'],
                    content:url
                });
            });
        });
    </script>
@stop