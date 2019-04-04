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
                            <h3 class="panel-title">编辑地区</h3>
                        </div>
                        <div class="panel-body">

                            @include('admin.partials.errors')
                            @include('admin.partials.success')
                            <form class="form-horizontal">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" name="id" value="{{$result->id}}">
                                @include('videos.tvwall._form')
                                <div class="form-group">
                                    <div class="col-md-7 col-md-offset-3">
                                        <button type="button" id="btn-primary" class="btn btn-primary btn-md" onclick="setdata()">
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
<script type="text/javascript">
   function setdata() {
            var data=$('.form-horizontal').serialize();
            $.ajax({
                url:"{{route('videos.tvwall.update')}}", //你的路由地址
                type:"post",
                dataType:"json",
                data:data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                success:function(data){
                    if(data.error=='1000'){
                        var messages='保存成功';
                        var icon=1;
                    }else{
                        var messages='保存失败';
                        var icon=2;
                    }
                    layer.alert(messages, {
                        icon: icon,
                        skin: 'layer-ext-moon'
                    },function(){
                        layer.close();
                        parent.location.reload();
                    })
                }
            });
    };
</script>