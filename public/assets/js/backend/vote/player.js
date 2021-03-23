define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'baidueditor'], function ($, undefined, Backend, Table, Form, UE) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vote/player',
                    add_url: 'vote/player/add',
                    edit_url: 'vote/player/edit',
                    del_url: 'vote/player/del',
                    detail_url: 'vote/player/detail',
                    multi_url: 'vote/player/multi',
                    table: 'vote'
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
                        {field: 'id', title: __('Id')},
                        {field: 'thumb', title: __('Thumb'), operate: false, formatter: Table.api.formatter.image},
                        {field: 'subject', title: __('Subject'), formatter: function (subject) {
                            if(subject) {
                                return subject.title;
                            }
                            return '';
                        }},
                        {field: 'user', title: __('User'), formatter: function (user) {
                                if(user) {
                                    return user.nickname;
                                }
                                return '';
                            }},
                        {field: 'nickname', title: __('Nickname'),  operate: false,formatter: Table.api.formatter.search},
                        {field: 'intro', title: __('Intro'), operate: false},
                        {field: 'number', title: __('Number'), operate: false, formatter: Table.api.formatter.search},
                        {field: 'votes', title: __('Votes'), operate: false, formatter: Table.api.formatter.search},
                        {field: 'views', title: __('Views'), operate: false, formatter: Table.api.formatter.search},
                        {field: 'votetime', title: __('Votetime'),formatter: Table.api.formatter.datetime},
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