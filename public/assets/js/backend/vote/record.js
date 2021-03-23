define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'baidueditor'], function ($, undefined, Backend, Table, Form, UE) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vote/record',
                    add_url: 'vote/record/add',
                    edit_url: 'vote/record/edit',
                    del_url: 'vote/record/del',
                    detail_url: 'vote/record/detail',
                    multi_url: 'vote/record/multi',
                    table: 'service'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                escape: false,
                pk: 'id',
                sortName: 'createtime',
                sortOrder: 'desc',
                pagination: true,
                pageSize: 10,
                commonSearch: false,
                queryParams: function queryParams(params) {
                    var searchForm = $("form[role=form]");
                    if(searchForm.length){
                        var searchFields = searchForm.serializeArray();
                        for(var i=0;i<searchFields.length;i++) {
                            if(searchFields[i]['value']) {
                                params[searchFields[i]['name']] = searchFields[i]['value'];
                            }
                        }
                    }
                    return params;
                },
                queryParamsType : "limit",
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),formatter: Table.api.formatter.search},
                        {field: 'subject.title', title: __('Subject'), formatter: Table.api.formatter.search},
                        {field: 'player.nickname', title: __('Player'), formatter: Table.api.formatter.search},
                        {field: 'user.nickname', title: __('User'), formatter: Table.api.formatter.search},
                        {field: 'ipaddr', title: __('IpAddr'), operate: false},
                        {field: 'status', title: __('Status'), operate: false, formatter: function (value) {
                            var statuses = ['InValid','Valid'];
                            return Table.api.formatter.status(statuses[value]);
                        }},
                        {field: 'createtime', title: __('CreateTime'),formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            Controller.api.bindevent();
            $("#common_search").bind("click",function () {
                table.bootstrapTable('refresh',{
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pageNumber: 1
                });
            });
        },
        detail: function () {
            Controller.api.bindevent();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});