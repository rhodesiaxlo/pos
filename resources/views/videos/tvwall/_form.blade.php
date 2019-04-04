<div class="form-group">
    <label for="tag" class="col-md-3 control-label"></label>
    <div class="col-md-6">
       <span id="distpicker5">
           <label class="sr-only" for="province10">Province</label>
           <select class="form-control" name="province" id="province" style="display: inherit; padding: 0px 0px;width: 28%;" onchange="Msg(this)">
               <option value="">-- 请选择省 --</option>
               @foreach($region as $list)
                   <option value="{{$list->region_id}}" @if($result->province==$list->region_id) selected @endif>{{$list->region_name}}</option>
               @endforeach
           </select>
           <label class="sr-only" for="city10">City</label>
           <select class="form-control" name="city" style="display: inherit; padding: 0px 0px;width: 28%;"  id="city10" onchange="MsgCity(this)">

           </select>
           <label class="sr-only" for="district10">District</label>
           <select class="form-control" name="region" style="display: inherit; padding: 0px 0px;width: 28%;"  id="district10" onchange="MsgArea(this)">
           </select>
       </span>
    </div>
</div>
<input type="hidden" name="province" value="{{$result->province}}">
<input type="hidden" name="city" value="{{$result->city}}" >
<input type="hidden" name="area" value="{{$result->area}}">
<div class="form-group">
    <label for="tag" class="col-md-3 control-label">状态</label>
    <div class="col-md-6">
        <input type="radio" name="type" @if($result->type==0) checked @endif value="0">已开发
        <input type="radio" name="type" style="margin-left: 40px;" @if($result->type==1) checked @endif value="1" >待开发
        <input type="radio" name="type" style="margin-left: 40px;" @if($result->type==2) checked @endif value="2" >开发中
    </div>
</div>
@if($cid = 99 )
{{--图标修改--}}
  @section('js')
    <script type="text/javascript" src="/plugins/bootstrap-iconpicker/bootstrap-iconpicker/js/iconset/iconset-fontawesome-4.3.0.min.js"></script>
    <script type="text/javascript" src="/plugins/bootstrap-iconpicker/bootstrap-iconpicker/js/bootstrap-iconpicker.js"></script>
    <script src="https://cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>
    <script>
        window.onload = function(){
            var province=$('input[name=province]').val();
            var city=$('input[name=city]').val();
            if(province!=''){
                Msg(province,1);
            }
            if(city!=''){
                MsgCity(city,1);
            }
        }
           function Msg(obj,i) {
               var regiser_id='';
               if(i==1){
                   regiser_id=obj;
               }else{
                   regiser_id = obj.options[obj.selectedIndex].value;
                   $('input[name=province]').val(regiser_id);
                   $('input[name=city]').val('');
               }
                var url = "{{route('videos.tvwall.AjaxIndexs')}}";
                $.ajax({
                    type: "POST", //提交方式
                    url: url,//路径
                    data: {
                        regiser_id
                    },
                    success: function (result) {
                        var city=$('input[name=city]').val();
                        var datas=JSON.parse(result);
                        var restag=[];
                        restag+='<option value="">-- 请选择市 --</option>';
                        if(datas.error==0){
                            for(var i=0;i<datas.message.length;i++){
                                if(city==datas.message[i].region_id){
                                    restag+='<option value="'+datas.message[i].region_id+'" selected>'+datas.message[i].region_name+'</option>';
                                }else{
                                    restag+='<option value="'+datas.message[i].region_id+'">'+datas.message[i].region_name+'</option>';
                                }
                            }
                            $('#city10').html(restag);
                        }else{
                            $('#city10').html(restag);
                        }
                    }
                })
            }
        function MsgCity(obj,i){
            var regiser_id='';
            if(i==1){
                regiser_id=obj;
            }else{
                regiser_id = obj.options[obj.selectedIndex].value;
                $('input[name=city]').val(regiser_id);
                $('input[name=area]').val('');
            }
            var url = "{{route('videos.tvwall.AjaxIndexs')}}";
            $.ajax({
                type: "POST", //提交方式
                url: url,//路径
                data: {
                    regiser_id
                },
                success: function (result) {
                    var area=$('input[name=area]').val();
                    var datas=JSON.parse(result);
                    var restag=[];
                    restag+='<option value="">-- 请选择区/县 --</option>';
                    if(datas.error==0){
                        for(var i=0;i<datas.message.length;i++){
                            if(area==datas.message[i].region_id){
                                restag+='<option value="'+datas.message[i].region_id+'" selected>'+datas.message[i].region_name+'</option>';
                            }else{
                                restag+='<option value="'+datas.message[i].region_id+'">'+datas.message[i].region_name+'</option>';
                            }}
                        $('#district10').html(restag);
                    }else{
                        $('#district10').html(restag);
                    }
                }
            })
        }
        function MsgArea(obj){
            var regiser_id = obj.options[obj.selectedIndex].value;
            $('input[name=area]').val(regiser_id);
        }
    </script>
@stop
@endif
