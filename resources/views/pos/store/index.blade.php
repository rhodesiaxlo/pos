@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')

@section('content')
<style>
    td{
        padding:10px;
    }
</style>

<form action="{{url::route('pos.store.index')}}" method="POST">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div>
        <div class='f18 pos_str_msg' style="">店铺信息</div>
        <div class='fsb pos_index_tital' style="">
            <div>
                <select class='rel bor-4' name='class' style='height:30px;top:1px;' name="" id="selectName" value='' ></select>
                <input id='keyword' name='Keyword' class='mg-l-10 bor-4 inputPlace' style='height:30px;' type="text" placeholder="请输入关键字查找">
                <button class='mg-l-10 w80px bor-n white bg-blue' type='submit' style='' onclick='return n()'>查找</button>
            </div>
            <div>
                <!-- <button style='border-radius: 14px;' id="new" onclick="window.location = '/pos/store/add';return false;">+新增店铺</button> -->
                <button type='button' class='mg-l-10 w80px bor-n white bg-blue' style='' id="new" onclick="return add()">+新增店铺</button>
                <button type='button' class='mg-l-10 w80px bor-n white bg-blue' style='' onclick='return newPage()'>刷新</button>
            </div>
        </div>
    </div>
</form>
    <div  style="margin-top:30px;">
        <table id="box" class="tableA f12 w100pc" style="text-align: center;" border='1' rules='all' >
            <tr class='pos_tr'>
                <td>店铺名称</td>
                <td>店主姓名</td>
                <td>商家编号</td>
                <td>营业执照编号</td>
                <td>店铺地址</td>
                <td>联系方式</td>
                <td>收款账户名</td>
                <td>收款账号</td>
                <td>开户行</td>
                <td>创建人</td>
                <td>创建时间</td>
                <td>操作</td>
                <td>启用状态</td>
            </tr>
            @foreach($users as $user)
            <tr>
                <td>{{$user->store_name}}</td>
                <td>{{$user->realname}}</td>
                <td>{{$user->store_code}}</td>
                <td>{{$user->business_licence_no}}</td>
                <td>{{$user->address}}</td>
                <td>{{$user->phone}}</td>
                <td>{{$user->account_name}}</td>
                <td>{{$user->account_no}}</td>
                <td>{{$user->bank->name}}</td>
                <td>{{$user->creator->name}}</td>
                <td>{{date('Y-m-d', $user->create_time)}}</td>
                <td><a href='/pos/store/edit?id={{$user->local_id}}'>编辑</a></td>
                <!-- <td id='is_a'><span onclick='is_a({{$user->local_id}},{{$user->is_active==1?0:1}})'>{{$user->is_active==1?"正常":"禁用"}}</span></td> -->
                <td id='is_a'><img onclick='is_a({{$user->local_id}},{{$user->is_active==1?0:1}})' src='{{$user->is_active==1?"/img/indexOne.png":"/img/indexTwo.png"}}' alt=""></td>
            </tr>
            @endforeach
        </table>
        <div id='' class='fsb mg-t-20' style='width:260px;'>
            {{$users->links()}}
            <!-- <div>
                <span>去 &nbsp<input type="text" style='width:25px;' />&nbsp 页</span>
                &nbsp<button type='submit'>GO</button>
            </div> -->
        </div>
    </div>
  
 @stop

@section('js')
    <script>
        let selectName=[
            {name:'按店铺名称',id:0},
            {name:'按店主姓名',id:1},
            {name:'按商铺编号',id:2},
            {name:'按营业执照编号',id:3},
            {name:'按店铺地址',id:4},
            {name:'按联系方式',id:5},
            {name:'按收款账户名',id:6},
            {name:'按收款账户号',id:7},
            {name:'按开户行',id:8}
        ]
        for(let item of selectName){
            $('#selectName').append(`<option value ="${item.id}">${item.name}</option>`)
        }
        function add(){
            //debugger;
            window.location = "/pos/store/add";
            return false;
        }

        function n(){
            var value=$('#keyword').val()
            var type=$('#selectName').val()
            var url='/pos/store/index'
            var data={
                type,
                value,
            }
            ajaxs(url,data,res=>{
                if(res.code==1){
                    window.location.reload()//刷新当前页面.
                    // $('#is_a').empty()
                    // $('#is_a').append(`<span onclick='is_a({{$user->is_active}},{{$user->local_id}})'>{{$user->is_active==1?"正常":"禁用"}}</span>`)
                }else{
                    eeor(res.message,'bg-red-2')
                }
            })
        }

        // console.log(timeLV({{$user->create_time}}))
        function newPage(){
            window.location.reload()//刷新当前页面.
        }
        function is_a(e,a){
            var url='/pos/store/edit'
            var data={
                local_id:e,
                status:a,
                type:'index'
            }
            ajaxs(url,data,res=>{
                if(res.code==1){
                    // window.location.reload()//刷新当前页面.
                    // console.log({{$user->is_active}})
                    $('#is_a').empty()
                    $('#is_a').append(`<img onclick='is_a({{$user->local_id}},{{$user->is_active==1?0:1}})' src='{{$user->is_active==1?"/img/indexOne.png":"/img/indexTwo.png"}}' alt="">`)
                }else{
                    eeor(res.message,'bg-red-2')
                }
            })
        }

    </script>

@stop