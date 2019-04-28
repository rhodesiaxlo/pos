@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
    <div class="main animsition">
        <div class="container-fluid">

            <div class="row">
                <div class="">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">编辑用户(修改密码则填写密码)</h3>
                        </div>
                        <div class="panel-body">

                            @include('admin.partials.errors')
                            @include('admin.partials.success')
                            <div class="bg-red-2" id="eor">
                            </div>
                            <form class="form-horizontal" role="form" method="POST"
                                  action="/admin/user/{{ $id }}" onsubmit="return toVaild()">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" name="id" value="{{ $id }}">
                                @include('admin.user._form')
                                <div class="form-group">
                                    <div class="col-md-7 col-md-offset-3">
                                        <button type="submit" class="btn btn-primary btn-md">
                                            <i class="fa fa-plus-circle"></i>
                                            保存
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
<script>
    function toVaild(){
        var d = {};
        var t = $('form').serializeArray();
        $.each(t, function() {
        d[this.name] = this.value;
        });
        if(d.password_confirmation!=''&&d.password=='')
        {
            eeor('请输入密码','bg-red-2')
            return false;
        }
        if(d.password_confirmation!=''&&d.password!=''&&d.password!=d.password_confirmation){
            eeor('两次密码不一致','bg-red-2')
            return false;
        }
    }
</script>
@stop