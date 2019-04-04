@extends('admin.layouts.base')

@section('title','控制面板')

@section('pageHeader','控制面板')

@section('pageDesc','DashBoard')
@section('content')

    <div class="row page-title-row" id="dangqian" style="margin:5px;">
        <div class="col-md-6">
            @if($cid==0)
                <span style="margin:3px;" id="cid" attr="" class="btn-flat text-info"> 顶级菜单</span>
            @else
                <span style="margin:3px;" id="cid" attr="" class="text-info"> 
                        </span>
                <a style="margin:3px;" href="/admin/permission"
                   class="btn btn-warning btn-md animation-shake reloadBtn"><i class="fa fa-mail-reply-all"></i> 返回顶级菜单
                </a>
            @endif
        </div>

        <div class="col-md-6 text-right">
    @if(Gate::forUser(auth('admin')->user())->check('admin.permission.create'))
                {{-- <a href="{{route('salesman.sales.create',['cid'=>$cid])}}" class="btn btn-success btn-md"><i class="fa fa-plus-circle"></i> 添加业务员 </a>--}}
                {{--<a href="#" class="btn btn-success btn-md"><i class="fa fa-plus-circle"></i> 添加业务员 </a>--}}
        <input type="hidden" id="cid" value="{{$cid}}"/>
    @endif
</div>
</div>
<div class="row page-title-row" style="margin:5px;">
<div class="col-md-6">
</div>
<div class="col-md-6 text-right">
</div>
</div>

<div class="row">
<div class="col-sm-12">
    <div class="box">
        @include('admin.partials.errors')
        @include('admin.partials.success')
        <div class="box-body">
            <table id="tags-table" class="table table-striped table-bordered">
                <thead>
                <tr>
                   {{-- <th data-sortable="false" class="hidden-sm"></th>--}}
                    <th class="hidden-sm">序号</th>
                    <th class="hidden-sm">订单编号</th>
                    <th class="hidden-sm">下单门店</th>
                    <th class="hidden-md">促销员</th>
                    <th class="hidden-md">下单时间</th>
                    <th class="hidden-md">订单总金额</th>
                    <th class="hidden-md">实付总金额</th>
                    <th class="hidden-md">结算状态</th>
                    <th class="hidden-md">备注</th>
                    <th data-sortable="false">操作</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</div>
</div>
<div class="modal fade" id="modal-delete" tabIndex="-1">
<div class="modal-dialog modal-warning">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
                ×
            </button>
            <h4 class="modal-title">提示</h4>
        </div>
        <div class="modal-body">
            <p class="lead">
                <i class="fa fa-question-circle fa-lg"></i>
                确认要删除这个订单吗?
            </p>
        </div>
        <div class="modal-footer">
            <form class="deleteForm" method="POST" action="">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-times-circle"></i> 确认
                </button>
            </form>
        </div>


    </div>
    @stop

    @section('js')
        <script>
            $(function () {
                var cid = $('#cid').attr('attr');
                var table = $("#tags-table").DataTable({
                    language: {
                        "sProcessing": "处理中...",
                        "sLengthMenu": "显示 _MENU_ 项结果",
                        "sZeroRecords": "没有匹配结果",
                        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                        "sInfoPostFix": "",
                        "sSearch": "搜索:",
                        "sUrl": "",
                        "sEmptyTable": "表中数据为空",
                        "sLoadingRecords": "载入中...",
                        "sInfoThousands": ",",
                        "oPaginate": {
                            "sFirst": "首页",
                            "sPrevious": "上页",
                            "sNext": "下页",
                            "sLast": "末页"
                        },
                        "oAria": {
                            "sSortAscending": ": 以升序排列此列",
                            "sSortDescending": ": 以降序排列此列"
                        }
                    },
                    order: [[5, "asc"]],
                    serverSide: true,

                    ajax: {
                        url: "{{route('salesman.order.index')}}",
                        type: 'GET',
                        data: function (d) {
                            d.cid = cid;
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    },
                    "columns": [
                        {"data": "order_id"},
                        {"data": "order_sn"},
                        {"data": "user_name"},
                        {"data": "promoters"},
                        {"data": "add_time"},
                        {"data": "order_amount"},
                        {"data": "pay_fee"},
                        {"data": "order_status"},
                        {"data": "pay_name"},
                        {"data": "order_sn"}
                    ],
                    columnDefs: [
                        {
                            'targets': -1, "render": function (data, type, row) {
                            var row_edit = {{Gate::forUser(auth('admin')->user())->check('admin.permission.edit') ? 1 : 0}};
                            var row_delete = {{Gate::forUser(auth('admin')->user())->check('admin.permission.destroy') ? 1 :0}};
                            var str = '';
                           /* //编辑
                            if (row_edit) {
                                str += '<a style="margin:3px;" href="/salesman/sales/' + row['order_id'] + '/edit" class="X-Small btn-xs text-success "><i class="fa fa-edit"></i> 编辑</a>';
                            }*/

                            //删除
                            if (row_delete) {
                                str += '<a style="margin:3px;" href="#" attr="' + row['order_id'] + '" class="delBtn X-Small btn-xs text-danger"><i class="fa fa-times-circle"></i> 删除</a>';
                            }

                            return str;

                        }
                        }
                    ]
                });

                table.on('preXhr.dt', function () {
                    loadShow();
                });

                table.on('draw.dt', function () {
                    loadFadeOut();
                });

                table.on('order.dt search.dt', function () {
                    table.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }).draw();

                $("table").delegate('.delBtn', 'click', function () {
                    var id = $(this).attr('attr');
                    $('.deleteForm').attr('action', '/salesman/order/destroy/'+ id);
                    $("#modal-delete").modal();
                });
               $('.btn-md').on('click',function(){
                    var url="{{route('salesman.sales.create',['cid'=>$cid])}}";
                    layer.open({
                        type: 2,
                        shadeClose: false,
                        shade: false,
                        maxmin: true, //开启最大化最小化按钮
                        area: ['400px', '600px'],
                        content:url
                    });
                });
            });
        </script>
@stop