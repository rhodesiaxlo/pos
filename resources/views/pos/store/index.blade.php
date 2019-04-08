<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.js"></script>
<link href="/css/globle-px.css" rel="stylesheet">
    <div>
        <div class='f18' style="">店铺信息</div>
        <div class='fsb' style="margin:10px 20px;">
            <div>
                <select class='rel' style='height:30px;top:1px;' name="" id="selectName" ></select>
                <input style='height:30px;' type="text" placeholder="请输入关键字查找">
                <button class='' style='border-radius: 14px;'>查找</button>
            </div>
            <div>
                <button style='border-radius: 14px;'>+新增店铺</button>
                <button style='border-radius: 14px;'>刷新</button>
            </div>
        </div>
    </div>
    <div  style="margin-top:30px;">
        <table id="box" class="tableA f12 w100pc" style="text-align: center;" border='1' rules='all' cellpadding='10'>
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
        </table>
        <div id='' class='fsb mg-t-20' style='width:260px;'>
            <div class='fsa' style='width:160px;'>
                <button onclick='del()'>-</button>
                <span id='one' class='dslb border_00'>1</span>
                <span id='two' class='border_00'>2</span>
                <span id='three' class='border_00'>3</span>
                <span>...</span>
                <span id='totalNum'></span>
                <button onclick='add()'>+</button>
            </div>
            <div>
                <span>去 &nbsp<input type="text" style='width:25px;' />&nbsp 页</span>
            </div>
        </div>
    </div>

  
    <script>
        let num=1
        let list=[
            {
                time:'11',
                time2:33,
                msg:'用户如何放大',
                name:'规范化',
                nameNum:4576373,
                myAmount:45763,
                hisAmount:45763,
                staus1:'none',
                staus2:'none',
                staus3:'none',
                staus3:'none',
                staus3:'none',
                staus3:'none',
            },
            {
                time:12,
                time2:33,
                msg:'用户如何放大',
                name:'规范化',
                nameNum:4576373,
                myAmount:45763,
                hisAmount:45763,
                staus1:'none',
                staus2:'none',
                staus3:'none',
                staus3:'none',
                staus3:'none',
                staus3:'none',
            },
            {
                time:13,
                time2:33,
                msg:'用户如何放大',
                name:'规范化',
                nameNum:4576373,
                myAmount:45763,
                hisAmount:45763,
                staus1:'none',
                staus2:'none',
                staus3:'none',
                staus3:'none',
                staus3:'none',
                staus3:'none',
            },
        ]
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
        for(let item of list){
            $('#box').append(`<tr style=''>
                    <td>${item.time}</td>
                    <td>${item.time2}</td>
                    <td>${item.msg}</td>
                    <td>${item.name}</td>
                    <td>${item.nameNum}</td>
                    <td>${item.myAmount}</td>
                    <td>${item.hisAmount}</td>
                    <td>${item.staus1}</td>
                    <td>${item.staus2}</td>
                    <td>${item.staus3}</td>
                    <td>${item.staus3}</td>
                    <td>${item.staus3}</td>
                    <td>${item.staus3}</td>
                </tr>`)
        }
        for(let item of selectName){
            $('#selectName').append(`<option value ="${item.name}">${item.name}</option>`)
        }
        function numI(){
            $('#one').text(num)
            $('#two').text(num+1)
            $('#three').text(num+2)
            $('#totalNum').text(10)
        }
        numI()
        function del(){
            num>1?num--:''
            numI()
        }
        function add(){
            num+2<10?num++:''
            numI()
        }
    </script>