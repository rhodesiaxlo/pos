@extends('admin.layouts.basetoop')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
    <div class="main animsition">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">添加业务员</h3>
                        </div>
                        <div class="panel-body">

                            @include('admin.partials.errors')
                            @include('admin.partials.success')

                            <form class="form-horizontal" role="form" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="cove_image"/>
                                <!-- <input type="hidden" name="_method" value="POST"> -->

                                @include('salesman.sales._form')
                                <div class="form-group">
                                    <div class="col-md-7 col-md-offset-3">
                                        <button type="reset" class="btn btn-primary btn-md reset">
                                            <i class="fa fa-plus-circle"></i>
                                            取消
                                        </button>
                                        <button type="button" style="margin-left: 148px;" id="btn-primary" class="btn btn-primary btn-md">
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
    <script>
        window.onload = function() {
            $('#btn-primary').on('click',function(){
                var inputid=$('#mobile').val();
                if(isPhoneNo($.trim(inputid)) == false){
                    layer.msg('手机号码不正确');
                    return false;
                }
                if(isChinaName($.trim($('#name').val())) == false){
                    layer.msg('用户名要为中文');
                    return false;
                }
               var data=$('.form-horizontal').serialize();
                $.ajax({
                    url:"{{route('salesman.sales.create')}}", //你的路由地址
                    type:"post",
                    dataType:"json",
                    data:data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    success:function(data){
                        if(data.error_code == 1)
                        {
                            var messages=data.message;
                            var icon=1;
                        } else {
                            var messages=data.message;
                            var icon=2;
                        }
                        // if(data.message=='success'){
                        //  var messages='添加成功';
                        //   var icon=1;
                        // }else{
                        //     var messages='添加失败';
                        //     var icon=2;
                        // }
                        layer.alert(messages, {
                            icon: icon,
                            skin: 'layer-ext-moon'
                        },function(){
                            layer.close();
                            parent.location.reload();
                        })
                }
             });
            });
            //手机号
            function isPhoneNo(phone) {
                var pattern = /^1[345789]\d{9}$/;
                return pattern.test(phone);
            }
            //用户名
            function isChinaName(name) {
                var pattern = /^[\u4E00-\u9FA5]{1,6}$/;
                return pattern.test(name);
            }
        };
    </script>