@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
    <div class='f18 bold mg-bt-15 pos_str_msg' style="">新增店铺</div>
    <form class="" id='form' method="POST" action="{{url::route('pos.store.add')}}" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital'>店铺信息</p>
            <div class='fsb w100pc mg-t-15'  >
                <p style='width:50%;'><span class='w120px dslb'> 店铺名称</span>  <input id='nameStort' name='nameStort' style='width:60%;' type="text" value='' /></p>
                <div class='mg-b-10' style='width:50%;'> <span class='red'>*</span> 商家编号  <input  style='width:60%;' type="text" placeholder='无需填写，系统自动生成编码' value="{{$store_code}}" name="store_code" /></div>
            </div>
            <div class='fsb mg-b-10 mg-t-15'>
                <div class='w120px dslb'> 店铺地址  </div>
                <div class='fg2 mg-l-3'>
                        <select class='' style='width:10%;' onchange="citY(this.options[this.options.selectedIndex].value)" name="province" id="province"><option>省份</option></select>
                        <select class='mg-l-10' style='width:10%;' onchange="countY(this.options[this.options.selectedIndex].value)" name="city" id="city"><option>城市</option></select>
                        <select class='mg-l-10' style='width:10%;' name="county" id="county"><option>区</option></select>
                        <input class='mg-l-20' style='width:25%;' value='54' name='address' id='' type="text" placeholder='详细地址'>
                </div>
                
            </div>
            <div class='fsb mg-b-10 mg-t-15'>
                <div class='w50pc'><span class='w120px dslb'> 营业执照编号</span>  <input name='number' value='' type="text" class='w50pc' /></div>
                <div class='w50pc'>
                    店铺状态 &nbsp
                    <label for="staus1">启用</label> 
                    <input id='staus1' name="staus" type="radio" checked="checked" value='启用' />
                    <label for="staus2">禁用</label> 
                    <input id='staus2' name="staus" type="radio" value='禁用' />
                </div>
            </div>
        </div>
        <div>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital mg-t-15'>店主信息</p>
            <div class='fsb mg-b-10 mg-t-15'>
                <div class='w50pc'> <span class='w120px dslb'>店主姓名</span> <input name='name' value='' type="text" class='w50pc' /></div>
                <div class='w50pc'> 联系方式  <input name='phone' value='' type="text" class='w50pc' placeholder='' /></div>
            </div>
        </div>
        <div>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital mg-t-15'>收款信息</p>
            <div class='fsb  mg-b-10 mg-t-15'>
                <div class='w50pc'><span class='w120px dslb'> 收款账户名 </span> <input name='amuName' value='' type="text" class='w50pc' /></div>
                <div class='w50pc'> 收款账号  <input name='amuNum' value='' type="text" class='w50pc' placeholder='' /></div>
            </div>
            <div class='mg-t-15'><span class='w120px dslb'>开户行</span><select style=';' class='w50pc mg-b-10' name="place" id="place" ></select></div>
        </div>
        <div class='mg-t-15'>
            <p class='bg-skyblue white padd-tb-15 f17 pos_index_tital '>账号信息</p>
            <div class='fsb mg-b-10 mg-t-15'>
                <div class='w50pc'> <span class='w120px dslb'>登录用户名</span>  <input type="text" value='' name='username' class='w50pc' placeholder='文本'/></div>
                <div class='w50pc'> 登录密码  <input type="text" value='' name='userSub' class='w50pc' placeholder='数字、六位' /></div>
            </div>
        </div>
        
        <div class='fsa'>
            <button type="button" onclick='beforeSub()' class="mg-l-10 w80px bor-n white bg-blue">新增</button>
            <button type="button" onclick='delSub()' class="mg-l-10 w80px bor-n white bg-blue" >取消</button>
        </div>
        
        <div class='fixed bg-fff' id='eor' style='width:26%; left:40%; top:50%; display:none;'>
            <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
        </div>
        <div class='fixed bg-fff' id='or' style='width:26%; left:40%;top:50%;display:none;'>
            <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
            <div class='txal w100pc bold' style='height:100px;line-height:100px;'>
                <p>确定新增店铺信息？</p>
            </div>
            <div class='fsa mg-b-10'>
                <input type="submit" value="是" />
                <input type="button" class="" onclick="$('#or').hide()" value='否' />
            </div>
        </div>
        <div class='fixed bg-fff' id='del' style='width:26%; left:40%;top:50%;display:none;'>
            <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
            <div class='txal w100pc bold' style='height:100px;line-height:100px;'>
                <p>取消新增店铺信息？</p>
            </div>
            <div class='fsa mg-b-10'>
                <input type="button" onclick='return delyes()' value="是" />
                <input type="button" class="" onclick="$('#del').hide()" value='否' />
            </div>
        </div>
    </form>
       

  
 @stop

@section('js')
    <script>
        // 提交验证
        function beforeSub(){
            var d = {};
            var t = $('form').serializeArray();
            $.each(t, function() {
            d[this.name] = this.value;
            });
            if (d.address==''||d.amuName==''||d.amuNum==''||d.area=='区'||d.city=='城市'||d.name==''||d.nameStort==''||d.number==''||d.phone==''||d.place=='where'||d.province=='省份'||d.staus==''||d.userSub==''||d.username=='') {
                $('#eor').empty()
                $('#eor').show()
                $('#eor').append("<div class='txal w100pc bold' style='height:100px;line-height:100px;'>请填写完整信息！</div>")
                $("#eor").fadeOut(3000);
                return false;
            } else {
                $('#or').show()
            }
        }
        // 点击取消
        function delSub(){
            $('#del').show()
        }
        // 确认店铺添加
        function delyes(){
            window.location = "/pos/store/index";
        }

        newC()
        province()
    </script>

@stop