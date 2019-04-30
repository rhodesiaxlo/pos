@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')

    <form enctype="multipart/form-data" id="uploadImg" class='txal' style='margin:200px 0;'>
        <select id='other' name='local_id' class='bor-4' style='width:300px;border:1px solid #e4f9ba;display:inline-block;margin:10px 0;'>
            <option value=''>选择店铺</option>
        </select><br/>
        <div class='rel'>
            <input name="file" type="file" id="file" class='abs' style='width:300px;border:1px solid #c5bdbd;display:inline-block;opacity:0;'>
            <input id='see' class='bor-4' placeholder='选择上传文件' style='width:300px;border:1px solid #baf9de;display:inline-block;z-index:-1;'>
        </div>
        <input type="button" onclick='upload1()' id="btn" class='bor-n bg_green_1 white bor-4' value="上传" style='margin:10px 0;width:100px;'>
    </form>
    <div class='fixed bg-fff' id='eor' style='width:26%; left:40%; top:50%; display:none;'>
        <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
    </div>
    
@stop

@section('js')
    <script>
        let file1;
        $(function(){
            var url='/api/apipos/storelist',data={};
            ajaxs(url,data,(res)=>{
                if(res.code=='1'){
                    console.log(res.data)
                    for(let item of res.data){
                        $('#other').append(`<option value='${item.local_id}'>${item.store_name}</option>`)
                    }
                }else{
                    eeor(res.message,'bg-red-2')
                }
            })
        })
        $('input[type="file"]').on('change', function(){
            var loc=$('#other').val()
            if(!loc){
                eeor('请先选择上传店铺','bg-red-2')
                return false
            }
            var file = this.files[0];
            var formData = new FormData($('#uploadImg')[0]);
            formData.append('file', file);
            formData.append('local_id', $('#other').val());
            file1=formData
            $('#see').val(formData.get('file').name)
        });
        function upload1(){
            var loc=$('#other').val(),file0=$('#see').val()
            if(loc&&file0){
                $.ajax({
                    url: '/pos/excel/index',
                    type: 'POST',
                    cache: false,
                    data:file1,
                    processData: false,
                    contentType: false,
                    success: function(response, status, xhr){
                        eeor('上传成功','bg-red-2')
                    },
                    error:function(XMLHttpRequest, textStatus, errorThrown){
                        eeor((errorThrown+'：'+XMLHttpRequest.status),'bg-red-2')
                    }})
            }else{
                eeor('请完整选择上传内容','bg-red-2')
            }
        }
    </script>
@stop