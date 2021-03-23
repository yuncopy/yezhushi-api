define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'baidueditor'], function ($, undefined, Backend, Table, Form, UE) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vote/subject',
                    add_url: 'vote/subject/add',
                    edit_url: 'vote/subject/edit',
                    del_url: 'vote/subject/del',
                    detail_url: 'vote/subject/detail',
                    table: 'vote',
                    //设置不同操作下的弹窗宽高
                    area: {
                        add:['800px','450px'],
                        edit:['800px','450px'],
                        detail:['800px','450px']
                    }
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
                        {field: 'community.name', title: __('Community'), formatter: Table.api.formatter.search},
                        {field: 'title', title: __('Title'), operate: false,formatter: Table.api.formatter.search},
                        {field: 'players', title: __('Players'), operate: false},
                        {field: 'votes', title: __('Votes'), operate: false},
                        {field: 'views', title: __('Views'), operate: false},
                        {field: 'voters', title: __('voters'), operate: false},
                        {field: 'status', title: __('Status'), operate: false, formatter: function (value) {
                                var statuses = ['InValid','Valid','Expired'];
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
            var editor = UE.getEditor('container');
            Controller.api.bindevent();
        },
        add: function () {
            var editor = UE.getEditor('container');
            Controller.api.bindevent();
        },
        edit: function () {
            var editor = UE.getEditor('container');
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