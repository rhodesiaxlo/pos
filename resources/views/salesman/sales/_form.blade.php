


<div class="form-group">
    <label for="tag" class="col-md-3 control-label">业务员名称</label>
    <div class="col-md-6">
        <input type="text" class="form-control" name="name" id="name" value="{{$data['salens_name']}}" autofocus>
        <input type="hidden" class="form-control" name="cid" id="tag" value="{{$data['cid']}}" autofocus>
    </div>
</div>
<div class="form-group">
    <label for="tag" class="col-md-3 control-label">手机号</label>
    <div class="col-md-6">
        <input type="text" class="form-control"  name="mobile" id="mobile" value="{{$data['mobile']}}" autofocus>
    </div>
</div>
<div class="form-group">
    <label for="tag" class="col-md-3 control-label">密码</label>
    <div class="col-md-6">
        <input type="text" class="form-control"  name="password" id="password" value="{{$data['password']}}" autofocus>
    </div>
</div>

<div class="form-group">
    <label for="tag" class="col-md-3 control-label">确认密码</label>
    <div class="col-md-6">
        <input type="text" class="form-control"  name="confirm_password" id="confirm_password" value="{{$data['password']}}" autofocus>
    </div>
</div>
<div class="form-group">
    <label for="tag" class="col-md-3 control-label">性别</label>
    <div class="col-md-6">
        <input type="radio" name="sex" value="0" @if($data['sex']=='男') checked @endif>男
        <input type="radio" name="sex" style="margin-left: 40px;" value="1" @if($data['sex']=='女') checked @endif>女
        <input type="radio" name="sex" style="margin-left: 40px;" value="2" @if($data['sex']=='人妖') checked @endif>保密
    </div>
</div>
<div class="form-group">
    <label for="tag" class="col-md-3 control-label">业务员类型</label>
    <div class="col-md-6">
        <select class="form-control" name="salens_type">
            <option value="">请选择</option>
            <option value="0" @if($data['salens_type']==0) selected @endif>星星自营业务员</option>
            <option value="1" @if($data['salens_type']==1) selected @endif>联营商业务员</option>
        </select>
    </div>
</div>
@if($cid = 99 )
{{--图标修改--}}
   {{-- <link rel="stylesheet" href="/plugins/bootstrap-iconpicker/icon-fonts/font-awesome-4.2.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="/plugins/bootstrap-iconpicker/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css"/>

    <div class="form-group">
    <label for="tag" class="col-md-3 control-label">图标</label>
    <div class="col-md-6">
        <!-- Button tag -->
        <button class="btn btn-default" name="icon" data-iconset="fontawesome" data-icon="{{ $icon?$icon:'fa-sliders' }}" role="iconpicker"></button>
    </div>

    </div>--}}
@section('js')
    <script type="text/javascript" src="/plugins/bootstrap-iconpicker/bootstrap-iconpicker/js/iconset/iconset-fontawesome-4.3.0.min.js"></script>
    <script type="text/javascript" src="/plugins/bootstrap-iconpicker/bootstrap-iconpicker/js/bootstrap-iconpicker.js"></script>

@stop
@endif
<div class="form-group">
    <label for="tag" class="col-md-3 control-label">备注</label>
    <div class="col-md-6">
        <textarea name="ramk" class="form-control" rows="3">{{$data['ramk']}}</textarea>
    </div>
</div>

