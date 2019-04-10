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
    <!-- <?php echo json_encode($users); ?> -->

<form action="{{url::route('pos.store.index')}}" method="POST">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div>
        <div class='f18' style="">店铺信息</div>
        <div class='fsb' style="margin:10px 20px;">
            <div>
                <select class='rel' style='height:30px;top:1px;' name="" id="selectName" ></select>
                <input style='height:30px;' type="text" placeholder="请输入关键字查找">
                <button class='' style='border-radius: 14px;'>查找</button>
            </div>
            <div>
                <!-- <button style='border-radius: 14px;' id="new" onclick="window.location = '/pos/store/add';return false;">+新增店铺</button> -->
                <button style='border-radius: 14px;' id="new" onclick="return add()">+新增店铺</button>
                <button style='border-radius: 14px;'>刷新</button>
            </div>
        </div>
    </div>
    <div  style="margin-top:30px;">
        <table id="box" class="tableA f12 w100pc" style="text-align: center;" border='1' rules='all' >
            <tr>
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
                <td>{{$user->uname}}</td>
                <td>{{$user->realname}}</td>
                <td>{{$user->store_code}}</td>
                <td>{{$user->business_licence_no}}</td>
                <td>{{$user->address}}</td>
                <td>{{$user->phone}}</td>
                <td>{{$user->account_name}}</td>
                <td>{{$user->account_no}}</td>
                <td>{{$user->bank->name}}</td>
                <td>{{$user->creator->name}}</td>
                <td>{{$user->create_time}}</td>
                <td><a href='/pos/store/edit?id={{$user->id}}'>编辑</a></td>
                <td>{{$user->is_active}}</td>
            </tr>
            @endforeach
        </table>
        <div id='' class='fsb mg-t-20' style='width:260px;'>
            {{$users->links()}}
            <div>
                <span>去 &nbsp<input type="text" style='width:25px;' />&nbsp 页</span>
                &nbsp<button type='submit'>GO</button>
            </div>
        </div>
    </div>
</form>
  
 @stop

@section('js')
    <script>
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
        function add(){
            //debugger;
            window.location = "/pos/store/add";
            return false;
        }
    </script>

@stop