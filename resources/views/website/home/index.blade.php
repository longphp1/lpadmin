<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
    <head>
        <meta charset="utf-8">
        <title>管理员管理</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
        <link rel="stylesheet" href="/static/admin/css/table-common.css" />
    </head>
    <body class="pear-container">

        <!-- 顶部查询表单 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form top-search-from">

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.created_at') }}</label>
                        <div class="layui-input-block">
                            <div class="layui-input-block" id="created_at">
                                <input type="text" autocomplete="off" name="created_at[]" id="created_at-date-start" class="layui-input inline-block" placeholder="{{ __('admin.start_time') }}">
                                -
                                <input type="text" autocomplete="off" name="created_at[]" id="created_at-date-end" class="layui-input inline-block" placeholder="{{ __('admin.end_time') }}">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label"></label>
                        <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="table-query">
                            <i class="layui-icon layui-icon-search"></i>{{ __('admin.search') }}</button>
                        <button type="reset" class="pear-btn pear-btn-md" lay-submit lay-filter="table-reset">
                            <i class="layui-icon layui-icon-refresh"></i>{{ __('admin.reset') }}</button>
                    </div>
                    <div class="toggle-btn">
                        <a class="layui-hide">{{ __('admin.expand') }}<i class="layui-icon layui-icon-down"></i></a>
                        <a class="layui-hide">{{ __('admin.collapse') }}<i class="layui-icon layui-icon-up"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <table id="data-table" lay-filter="data-table"></table>
            </div>
        </div>

        <!-- 表格顶部工具栏 -->
        <script type="text/html" id="table-toolbar">
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add">
                <i class="layui-icon layui-icon-add-1"></i>{{ __('admin.create') }}</button>
            <button class="pear-btn pear-btn-danger pear-btn-md" lay-event="batchRemove">
                <i class="layui-icon layui-icon-delete"></i>{{ __('admin.delete') }}</button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            <div style="white-space: nowrap; display: flex; gap: 4px; justify-content: center;">
                <button class="table-action-btn table-action-edit" lay-event="edit" title="{{ __('admin.edit') }}">
                    <i class="layui-icon layui-icon-edit"></i>
                </button>
                <button class="table-action-btn table-action-delete" lay-event="remove" title="{{ __('admin.delete') }}">
                    <i class="layui-icon layui-icon-delete"></i>
                </button>
            </div>
        </script>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script>

            // 相关常量
            const PRIMARY_KEY = "id";
            const SELECT_API = "{{ route('lpadmin.website.home.index') }}";
            const UPDATE_API = "{{ route('lpadmin.website.home.update', ':id') }}";
            const DELETE_API = "{{ route('lpadmin.website.home.destroy', ':id') }}";
            const INSERT_URL = "{{ route('lpadmin.website.home.create') }}";
            const UPDATE_URL = "{{ route('lpadmin.website.home.edit', ':id') }}";

            // 字段 创建时间 created_at
            layui.use(["laydate"], function() {
                layui.laydate.render({
                    elem: "#created_at",
                    range: ["#created_at-date-start", "#created_at-date-end"],
                });
            })

            // 表格渲染
            layui.use(["table", "form", "common", "popup", "util"], function() {
                let table = layui.table;
                let form = layui.form;
                let $ = layui.$;
                let common = layui.common;
                let util = layui.util;

				// 表头参数
				let cols = [
					{
						type: "checkbox"
					},{
						title: "ID",
						field: "id",
                        width: 100,
                        sort: true,
					},{
						title: "{{ __('admin.site_name') }}",
						field: "name",
					},{
						title: "{{ __('admin.home_header') }}",
						field: "top_head",
					},{
                        title: "{{ __('admin.home_title') }}",
                        field: "large_head",
                    },{
                        title: "{{ __('admin.home_footer') }}",
                        field: "top_head_bottom",
                    },{
                        title: "{{ __('admin.home_content') }}",
                        field: "header_content",
                    },{
						title: "{{ __('admin.home_qr') }}",
						field: "middle_qr_code",
					},{
						title: "{{ __('admin.home_qr_title') }}",
						field: "middle_qr_title",
					},{
						title: "{{ __('admin.home_middle_image') }}",
						field: "middle_logo",
					},{
                        title: "{{ __('admin.home_middle_content') }}",
                        field: "middle_content",
                    },{
                        title: "{{ __('admin.home_content') }}",
                        field: "content",
                    },{
						title: "{{ __('admin.created_at') }}",
						field: "created_at",
					},{
						title: "{{ __('admin.updated_at') }}",
						field: "updated_at",
						hide: true,
					},{
						title: "{{ __('admin.actions_col') }}",
						toolbar: "#table-bar",
						align: "center",
						fixed: "right",
                        width: 80,
					}
				];

				// 渲染表格
				function render()
				{
				    table.render({
				        elem: "#data-table",
				        url: SELECT_API,
				        method: 'GET',
				        headers: {
				            'Accept': 'application/json',
				            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				        },
				        page: true,
				        cols: [cols],
				        skin: "line",
				        size: "lg",
				        toolbar: "#table-toolbar",
				        autoSort: false,
				        defaultToolbar: [{
				            title: "{{ __('admin.refresh') }}",
				            layEvent: "refresh",
				            icon: "layui-icon-refresh",
				        }, "filter", "print", "exports"],
				        done: function () {
				            layer.photos({photos: 'div[lay-id="data-table"]', anim: 5});
				        }
				    });
				}

				// 获取表格中下拉或树形组件数据
				let apis = [];
				apis.push(["roles", "{{ route('lpadmin.role.index') }}?format=select"]);
				let apiResults = {};
				apiResults["roles"] = [];
				let count = apis.length;
				layui.each(apis, function (k, item) {
				    let [field, url] = item;
				    $.ajax({
				        url: url,
				        dateType: "json",
				        success: function (res) {
                            if (res.code) {
                                return layui.popup.failure(res.msg);
                            }
				            function travel(items) {
				                for (let k in items) {
				                    let item = items[k];
				                    apiResults[field][item.value] = item.name;
				                    if (item.children) {
				                        travel(item.children);
				                    }
				                }
				            }
				            travel(res.data);
				        },
				        complete: function () {
				            if (--count === 0) {
				                render();
				            }
				        }
				    });
				});
				if (!count) {
				    render();
				}

                // 编辑或删除行事件
                table.on("tool(data-table)", function(obj) {
                    if (obj.event === "remove") {
                        remove(obj);
                    } else if (obj.event === "edit") {
                        edit(obj);
                    }
                });

                // 表格顶部工具栏事件
                table.on("toolbar(data-table)", function(obj) {
                    if (obj.event === "add") {
                        add();
                    } else if (obj.event === "refresh") {
                        refreshTable();
                    } else if (obj.event === "batchRemove") {
                        batchRemove(obj);
                    }
                });

                // 表格顶部搜索事件
                form.on("submit(table-query)", function(data) {
                    table.reload("data-table", {
                        page: {
                            curr: 1
                        },
                        where: data.field
                    })
                    return false;
                });

                // 表格顶部搜索重置事件
                form.on("submit(table-reset)", function(data) {
                    table.reload("data-table", {
                        where: []
                    })
                });

                // 表格排序事件
                table.on("sort(data-table)", function(obj){
                    table.reload("data-table", {
                        initSort: obj,
                        scrollPos: "fixed",
                        where: {
                            field: obj.field,
                            order: obj.type
                        }
                    });
                });

                // 表格新增数据
                let add = function() {
                    layer.open({
                        type: 2,
                        title: "{{ __('admin.create') }}",
                        shade: 0.1,
                        area: [common.isModile()?"100%":"70%", common.isModile()?"100%":"80%"],
                        content: INSERT_URL
                    });
                }

                // 表格编辑数据
                let edit = function(obj) {
                    let value = obj.data[PRIMARY_KEY];
                    let url = UPDATE_URL.replace(':id', value);
                    layer.open({
                        type: 2,
                        title: "{{ __('admin.update') }}",
                        shade: 0.1,
                        area: [common.isModile()?"100%":"70%", common.isModile()?"100%":"80%"],
                        content: url+"?id="+value
                    });
                }

                // 删除一行
                let remove = function(obj) {
                    return doRemove(obj.data[PRIMARY_KEY]);
                }

                // 删除多行
                let batchRemove = function(obj) {
                    let checkIds = common.checkField(obj, PRIMARY_KEY);
                    if (checkIds === "") {
                        layui.popup.warning("{{ __('admin.no_selection') }}");
                        return false;
                    }
                    doRemove(checkIds.split(","));
                }

                // 执行删除
                let doRemove = function (ids) {
                    layer.confirm("{{ __('admin.confirm_delete') }}", {
                        icon: 3,
                        title: "{{ __('admin.tip') }}"
                    }, function(index) {
                        layer.close(index);
                        let loading = layer.load();

                        // 处理单个或批量删除
                        if (Array.isArray(ids)) {
                            // 批量删除
                            $.ajax({
                                url: "{{ route('lpadmin.admin.batch_delete') }}",
                                data: {
                                    ids: ids,
                                    '_token': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType: "json",
                                type: "post",
                                success: function(res) {
                                    layer.close(loading);
                                    if (res.code !== 0) {
                                        return layui.popup.failure(res.message);
                                    }
                                    return layui.popup.success("{{ __('admin.success') }}", refreshTable);
                                }
                            });
                        } else {
                            // 单个删除
                            let url = DELETE_API.replace(':id', ids);
                            $.ajax({
                                url: url,
                                data: {
                                    '_method': 'DELETE',
                                    '_token': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType: "json",
                                type: "post",
                                success: function(res) {
                                    layer.close(loading);
                                    if (res.code !== 0) {
                                        return layui.popup.failure(res.message);
                                    }
                                    return layui.popup.success("{{ __('admin.success') }}", refreshTable);
                                }
                            });
                        }
                    });
                }

                // 刷新表格数据
                window.refreshTable = function() {
                    table.reloadData("data-table", {
                        scrollPos: "fixed",
                        done: function (res, curr) {
                            if (curr > 1 && res.data && !res.data.length) {
                                curr = curr - 1;
                                table.reloadData("data-table", {
                                    page: {
                                        curr: curr
                                    },
                                })
                            }
                        }
                    });
                }
            })

        </script>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </body>
</html>
