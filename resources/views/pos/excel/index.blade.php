@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
    <!-- <form id="f" enctype="multipart/form-data" class='txal' style='margin:200px 0;'>
        <div class='rel'>
            <input type="file" name="file" class='abs' style='width:300px;border:1px solid #c5bdbd;display:inline-block;opacity:0;'>
            <input type="" name="" class='bor-4' placeholder='选择上传文件' style='width:300px;border:1px solid #baf9de;display:inline-block;z-index:-1;'>
        </div>
        <input type="button" id="btn" class='bor-n bg_green_1 white bor-4' value="上传" style='margin:10px 0;width:100px;'>
    </form> -->

    <form enctype="multipart/form-data" id="uploadImg">
        上传文件:  
        <input name="file" type="file" id="file"> 
    </form>
    <div onclick='upload1()'>upload</div>
@stop

@section('js')
    <script>
        // $(function () {
        //     $("#btn").on("click",function () {
        //         var formData=new FormData(document.getElementById("f"));
        //         debugger
        //         $.ajax("/pos/excel/index",{
        //             type:"POST",
        //             data:formData,
        //             processData:false,
        //             contentType:false,
        //             success:function (data) {
        //                 console.log(data);
        //             }
        //         });
        //     })
        // })
        let file1;
        $(function(){
            $('input[type="file"]').on('change', function(){
                var file = this.files[0];
                var formData = new FormData($('#uploadImg')[0]);
                formData.append('file', file);
                console.log(formData.get('file'))
                file1=formData
            });
        })
        function upload1(){
            $.ajax({
                url: '/pos/excel/index',
                type: 'POST',
                cache: false,
                data: file1,
                //dataType: 'json',
                //async: false,
                processData: false,
                contentType: false,
            }).done(function(res) {
                console.log(res)
            }).fail(function(res) {
                console.log(res)
            });
        }
    </script>
@stop