@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
    <p> 导入数据页面1 </p>
    <form id="f" enctype="multipart/form-data">
        <input type="file" name="file"><br/>
        <input type="button" id="btn" value="提交">
    </form>
@stop

@section('js')
    <script>
        $(function () {
            $("#btn").on("click",function () {
                //使用FormData对象来提交整个表单，它支持文件的上传
                var formData=new FormData(document.getElementById("f"));
                //使用ajax提交
                $.ajax("/pos/excel/index",{
                    type:"POST",
                    data:formData,
                    processData:false,//告诉jquery不要去处理请求的数据格式
                    contentType:false,//告诉jquery不要设置请求头的类型
                    success:function (data) {
                        console.log(data);
                    }
                });
            })
        })
    </script>
@stop