@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
    <div class='f18 bold mg-bt-15' style="">编辑</div>
    <form class="" id='form' method="POST" action="{{url::route('pos.store.edit')}}" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div>
            <p class='bg-gray padd-tb-15 f17'>店铺信息</p>
            <div class='fsb w100pc'  >
                <p style='width:50%;'><span class='w120px dslb'> 店铺名称</span>  <input id='nameStort' name='nameStort' style='width:60%;' type="text" value='54' /></p>
                <div class='mg-b-10' style='width:50%;'> <span class='red'>*</span> 商家编号  <input  style='width:60%;' type="text" placeholder='无需填写，系统自动生成编码' /></div>
            </div>
            <div class='fsb mg-b-10'>
                <div class='w120px dslb'> 店铺地址  </div>
                <!-- <select style='width:15%;' name="province" id=""><option>份</option><option>省份</option></select>
                <select style='width:15%;' name="city" id="city"><option>省份</option><option>城市</option></select>
                <select style='width:15%;' name="area" id="area"><option>省份</option><option>区</option></select> -->
                <div class='fsb fg2 mg-l-3'>
                    <select style='width:15%;' name="province" id="province"></select>
                    <select style='width:15%;' name="city" id="city"></select>
                    <select style='width:15%;' name="county" id="county"></select>
                    <input style='width:25%;' value='54' name='address' id='' type="text" placeholder='详细地址'>
                </div>
                
            </div>
            <div class='fsb mg-b-10'>
                <div class='w50pc'><span class='w120px dslb'> 营业执照编号</span>  <input name='number' value='54' type="text" class='w50pc' /></div>
                <div class='w50pc'>
                    店铺状态 &nbsp
                    <label for="staus1">启用</label> 
                    <input id='staus1' name="staus" type="radio" value='启用' />
                    <label for="staus2">禁用</label> 
                    <input id='staus2' name="staus" type="radio" value='禁用' />
                    <!-- <label for="male">Male</label>
                    <input type="radio" name="sex" id="male" />
                    <label for="female">Female</label>
                    <input type="radio" name="sex" id="female" /> -->
            </div>
            </div>
        </div>
        <div>
            <p class='bg-gray padd-tb-15 f17'>店主信息</p>
            <div class='fsb mg-b-10'>
                <div class='w50pc'> <span class='w120px dslb'>店主姓名</span> <input name='name' value='54' type="text" class='w50pc' /></div>
                <div class='w50pc'> 联系方式  <input name='phone' value='54' type="text" class='w50pc' placeholder='' /></div>
            </div>
        </div>
        <div>
            <p class='bg-gray padd-tb-15 f17'>收款信息</p>
            <div class='fsb  mg-b-10'>
                <div class='w50pc'><span class='w120px dslb'> 收款账户名 </span> <input name='amuName' value='54' type="text" class='w50pc' /></div>
                <div class='w50pc'> 收款账号  <input name='amuNum' value='54' type="text" class='w50pc' placeholder='' /></div>
            </div>
            <div><span class='w120px dslb'>开户行</span><select style=';' class='w50pc mg-b-10' name="place" id=""><option>省份</option><option>where</option></select></div>
        </div>
        <div>
            <p class='bg-gray padd-tb-15 f17'>账号信息</p>
            <div class='fsb mg-b-10'>
                <div class='w50pc'> <span class='w120px dslb'>登录用户名</span>  <input type="text" value='54' name='username' class='w50pc' placeholder='文本'/></div>
                <div class='w50pc'> 登录密码  <input type="text" value='54' name='userSub' class='w50pc' placeholder='数字、六位' /></div>
            </div>
        </div>
        
        <div class='fsa'>
            <button type="button" onclick='beforeSub()' class="">修改</button>
            <button type="button" onclick='delSub()' class="" >删除</button>
            <button type="button" onclick='return delyes()' class="" >取消</button>
        </div>
        
        <div class='fixed bg-fff' id='eor' style='width:26%; left:40%; top:50%; display:none;'>
            <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
        </div>
        <div class='fixed bg-fff' id='or' style='width:26%; left:40%;top:50%;display:none;'>
            <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
            <div class='txal w100pc bold' style='height:100px;line-height:100px;'>
                <p>确定修改店铺信息？</p>
            </div>
            <div class='fsa mg-b-10'>
                <input type="submit" value="是" />
                <input type="button" class="" onclick="$('#or').hide()" value='否' />
            </div>
        </div>
        <div class='fixed bg-fff' id='del' style='width:26%; left:40%;top:50%;display:none;'>
            <p class='txal bold w100pc' style='border-bottom:1px solid #999;'>警告</p>
            <div class='txal w100pc bold' style='height:100px;line-height:100px;'>
                <p>确定删除店铺信息？</p>
            </div>
            <div class='fsa mg-b-10'>
                <input type="button" onclick='' value="是" />
                <input type="button" class="" onclick="$('#del').hide()" value='否' />
            </div>
        </div>
    </form>       
@stop

@section('js')
    <script>
        setup() 
        let selectName=[
            {name:'按店铺名称'},
            {name:'按店主姓名'},
            {name:'按商铺编号'},
            {name:'按营业执照编号'},
            {name:'按店铺地址'},
            {name:'按联系方式'},
            {name:'按收款账户名'},
            {name:'按收款账户号'},
            {name:'按开户行'}
        ]
        for(let item of selectName){
            $('#selectName').append(`<option value ="${item.name}">${item.name}</option>`)
        }
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

        function delSub(){
            $('#del').show()
        }

        function delyes(){
            window.location = "/pos/store/index";
        }

        function newC(){
            // ajaxs(url,(res)=>{

            // })
        }
    </script>

@stop