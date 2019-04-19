@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
    <div class='f18 bold mg-bt-15 pos_str_msg' style="">编辑</div>
    <form class="" id='form' method="POST" action="{{url::route('pos.store.edit')}}" onsubmit="return toVaild()">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="local_id" id="local_id" value="{{$userinfo->local_id}}">

        <div>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital'>店铺信息</p>
            <div class='fsb w100pc'  >
                <p style='width:50%;'><span class='w120px dslb'> 店铺名称</span>  <input id='nameStort' name='nameStort' style='width:60%;' type="text" value='{{$userinfo->store_name}}' /></p>
                <div class='mg-b-10' style='width:50%;'> <span class='red'>*</span> 商家编号  <input  style='width:60%;' type="text" placeholder='无需填写，系统自动生成编码'  value="{{$userinfo->store_code}}" /></div>
            </div>
            <div class='fsb mg-b-10'>
                <div class='w120px dslb'> 店铺地址  </div>
                <!-- <select style='width:15%;' name="province" id=""><option>份</option><option>省份</option></select>
                <select style='width:15%;' name="city" id="city"><option>省份</option><option>城市</option></select>
                <select style='width:15%;' name="area" id="area"><option>省份</option><option>区</option></select> -->
                <div class='fsb fg2 mg-l-3'>
                    <select style='width:15%;' onchange="citY(this.options[this.options.selectedIndex].value)" name="province" id="province"><option>省份</option></select>
                    <select style='width:15%;' onchange="countY(this.options[this.options.selectedIndex].value)" name="city" id="city"><option>城市</option></select>
                    <select style='width:15%;' name="county" id="county"><option>区</option></select>
                    <input style='width:25%;' value='{{$userinfo->address}}' name='address' id='' type="text" placeholder='详细地址'>
                </div>
            </div>
            <div class='fsb mg-b-10'>
                <div class='w50pc'><span class='w120px dslb'> 营业执照编号</span>  <input name='number' value='{{$userinfo->business_licence_no}}' type="text" class='w50pc' /></div>
                <div class='w50pc'>
                    店铺状态 &nbsp
                    <label for="staus1">启用</label> 
                    <input id='staus1' name="staus"  type="radio" @if ($userinfo['is_active'] == 1) checked="checked" @endif value='启用' />
                    <label for="staus2">禁用</label> 
                    <input id='staus2' name="staus" type="radio" @if ($userinfo['is_active'] !=1) checked="checked" @endif value='禁用' />
            </div>
            </div>
        </div>
        <div>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital'>店主信息</p>
            <div class='fsb mg-b-10'>
                <div class='w50pc'> <span class='w120px dslb'>店主姓名</span> <input name='name' value='{{$userinfo->realname}}' type="text" class='w50pc' /></div>
                <div class='w50pc'> 联系方式  <input name='phone' value='{{$userinfo->phone}}' type="text" class='w50pc' placeholder='' /></div>
            </div>
        </div>
        <div>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital'>收款信息</p>
            <div class='fsb  mg-b-10'>
                <div class='w50pc'><span class='w120px dslb'> 收款账户名 </span> <input name='account_name' value='{{$userinfo->account_name}}' type="text" class='w50pc' /></div>
                <div class='w50pc'> 收款账号  <input name='account_no' value='{{$userinfo->account_no}}' type="text" class='w50pc' placeholder='' /></div>
            </div>
            <div><span class='w120px dslb'>开户行</span><select style=';' class='w50pc mg-b-10' name="place" id="place" ></select></div>
        </div>
        <div>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital'>账号信息</p>
            <div class='fsb mg-b-10'>
                <div class='w50pc'> <span class='w120px dslb'>登录用户名</span>  <input type="text" value='{{$userinfo->uname}}' name='uname' class='w50pc' placeholder='文本'/></div>
                <div class='w50pc'> 登录密码  <input type="text" value='{{$userinfo->password}}' name='password' class='w50pc' placeholder='数字、六位' /></div>
            </div>
        </div>
        
        <div class='fsa'>
            <button type="button" onclick='beforeSub()' class="mg-l-10 w80px bor-n white bg-blue">修改</button>
            <button type="button" onclick='delSub()' class="mg-l-10 w80px bor-n white bg-blue" >删除</button>
            <button type="button" onclick='return delyes()' class="mg-l-10 w80px bor-n white bg-blue" >取消</button>
        </div>
        
        <div class='fixed bg-fff' id='eor' style='width:26%; left:40%; top:50%; display:none;'>
            <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
        </div>
        <div class='fixed bg-fff pos_aler' id='or' style='width:26%; left:40%;top:50%;display:none;'>
            <p class='txal bold w100pc pos_aler_title' style='border-bottom:1px solid #999;'>警告</p>
            <div class='txal w100pc bold' style='height:100px;line-height:100px;'>
                <p>确定修改店铺信息？</p>
            </div>
            <div class='fsa mg-b-10'>
                <input class="mg-l-10 w80px bor-n white " style='background: #d83138;' type="submit" value="是" />
                <input class="mg-l-10 w80px bor-n white " style='' type="button" class="" onclick="$('#or').slideToggle()" value='否' />
            </div>
        </div>
    </form>   
    <form class="" id='form' method="POST" action="{{url::route('pos.store.del')}}" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="local_id" id="local_id" value="{{$userinfo->local_id}}">
        <div class='fixed bg-fff pos_aler' id='del' style='width:26%; left:40%;top:50%;display:none;'>
            <p class='txal bold w100pc pos_aler_title' style='border-bottom:1px solid #999;'>警告</p>
            <div class='txal w100pc bold' style='height:100px;line-height:100px;'>
                <p>确定删除店铺信息？</p>
            </div>
            <div class='fsa mg-b-10'>
                <input class="mg-l-10 w80px bor-n white " style='background: #d83138;' type="submit" onclick='' value="是" />
                <input class="mg-l-10 w80px bor-n white " style='' type="button" class="" onclick="$('#del').slideToggle()" value='否' />
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        var local_id = $('#local_id').val();

        function beforeSub(){
            var d = {};
            var t = $('form').serializeArray();
            $.each(t, function() {
            d[this.name] = this.value;
            });
            if (d.address==''||d.amuName==''||d.amuNum==''||d.area=='区'||d.city=='城市'||d.name==''||d.nameStort==''||d.number==''||d.phone==''||d.place=='where'||d.province=='省份'||d.staus==''||d.userSub==''||d.username=='') {
                $('#eor').empty()
                $('#eor').show()
                $('#eor').append("<div class='txal w100pc bold' style='height:100px;line-height:100px;background: #44b793;'>请填写完整信息！</div>")
                $("#eor").fadeOut(3000);
                return false;
            } else {
                $('#or').slideToggle()
            }
        }
        // function toVaild(){
        //     var d = {};
        //     var t = $('form').serializeArray();
        //     $.each(t, function() {
        //     d[this.name] = this.value;
        //     });
        //     debugger
        // }
        function delSub(){
            $('#del').slideToggle()
        }

        function delyes(){
            window.location = "/pos/store/index";
        }
        function dalList(){
            let url='{{url::route('pos.store.del')}}'
            let data={
                id:local_id
            }
            ajaxGet(url,data,res=>{
                if(res.code==1){
                    // window.location = "/pos/store/index"
                }else{
                    alert(res.message)
                    $('#del').hide()
                }
            })
        }

        let url1='/api/apipos/banklist',data1={},msg1="{{$userinfo->bank_id}}",id1="place";
        let url2='/api/apipos/province',data2={},msg2="{{$userinfo->province_id}}",id2="province";
        let url3='/api/apipos/city',data3={id:"{{$userinfo->province_id}}"},msg3="{{$userinfo->city_id}}",id3="city";
        let url4='/api/apipos/area',data4={id:"{{$userinfo->city_id}}"},msg4="{{$userinfo->area_id}}",id4="county";
        
        lv(url2,data2,msg2,id2)
        
        
        function lv(url,data,msg,id){
            var list=[],one=[],a=[],url=url,data=data,msg=msg,id=id;
            ajaxs(url,data,(res)=>{
                $('#'+id).empty()
                if(id=='place'){
                    for(let item of res.data){
                        if(item.id==msg){
                            one.push(item)
                        }
                        if(item.id!=msg){
                            list.push(item)
                        }
                    }
                    a=one.concat(list)
                    for(let item of a){
                        $('#'+id).append(`<option value ="${item.id}">${item.name}</option>`)
                    }
                }else{
                    for(let item of res.data){
                        if(item.region_id==msg){
                            one.push(item)
                        }
                        if(item.region_id!=msg){
                            list.push(item)
                        }
                    }
                    a=one.concat(list)
                    for(let item of a){
                        $('#'+id).append(`<option value ="${item.region_id}" onclick=''>${item.region_name}</option>`)
                    }
                }
                if(id=='province'){
                    lv(url3,data3,msg3,id3)
                }
                if(id=='city'){
                    lv(url4,data4,msg4,id4)
                }
                if(id=='county'){
                    lv(url1,data1,msg1,id1)
                }
            })
        }
    </script>

@stop