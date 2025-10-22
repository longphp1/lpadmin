<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <title>管理员管理</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css"/>
    <link rel="stylesheet" href="/static/admin/css/reset.css"/>
    <link rel="stylesheet" href="/static/admin/css/table-common.css"/>
</head>
<body class="pear-container">

<!-- 顶部查询表单 -->
<div class="layui-card">
    <div class="layui-card-body">
        <form class="layui-form top-search-from">

            <div class="layui-form-item">
                <label class="layui-form-label">网站名称</label>
                <div class="layui-input-block">
                    <input type="text" name="websiteName" value="" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">创建时间</label>
                <div class="layui-input-block">
                    <div class="layui-input-block" id="created_at">
                        <input type="text" autocomplete="off" name="created_at[]" id="created_at-date-start"
                               class="layui-input inline-block" placeholder="开始时间">
                        -
                        <input type="text" autocomplete="off" name="created_at[]" id="created_at-date-end"
                               class="layui-input inline-block" placeholder="结束时间">
                    </div>
                </div>
            </div>

            <div class="layui-form-item layui-inline">
                <label class="layui-form-label"></label>
                <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="table-query">
                    <i class="layui-icon layui-icon-search"></i>查询
                </button>
                <button type="reset" class="pear-btn pear-btn-md" lay-submit lay-filter="table-reset">
                    <i class="layui-icon layui-icon-refresh"></i>重置
                </button>
            </div>
            <div class="toggle-btn">
                <a class="layui-hide">展开<i class="layui-icon layui-icon-down"></i></a>
                <a class="layui-hide">收起<i class="layui-icon layui-icon-up"></i></a>
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
        <i class="layui-icon layui-icon-add-1"></i>新增
    </button>
    <button class="pear-btn pear-btn-normal pear-btn-sm" lay-event="updatePlatform">
        <i class="layui-icon layui-icon-export"></i>
        更新平台网站
    </button>
    <button class="pear-btn pear-btn-warm pear-btn-sm" lay-event="pushPlatform">
        <i class="layui-icon layui-icon-upload"></i>
        推送平台网站
    </button>
</script>

<!-- 表格行工具栏 -->
<script type="text/html" id="table-bar">
    <div style="white-space: nowrap; display: flex; gap: 4px; justify-content: center;">
        <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
            <i class="layui-icon layui-icon-edit"></i>
        </button>
        <button class="table-action-btn table-action-delete" lay-event="remove" title="删除">
            <i class="layui-icon layui-icon-delete"></i>
        </button>
    </div>
</script>

<script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
<script src="/static/admin/component/pear/pear.js"></script>
<script>

    // 相关常量
    const PRIMARY_KEY = "id";
    const SELECT_API = "{{ route('lpadmin.website.platform.index') }}";
    const PUSH_PLATFORM_API = "{{ route('lpadmin.website.platform.pushPlatform') }}";
    const UPDATE_PLATFORM_API = "{{ route('lpadmin.website.platform.updatePlatform') }}";
    const UPDATE_API = "{{ route('lpadmin.website.platform.update', ':id') }}";
    const DELETE_API = "{{ route('lpadmin.website.platform.destroy', ':id') }}";
    const INSERT_URL = "{{ route('lpadmin.website.platform.create') }}";
    const UPDATE_URL = "{{ route('lpadmin.website.platform.edit', ':id') }}";

    // 字段 创建时间 created_at
    layui.use(["laydate"], function () {
        layui.laydate.render({
            elem: "#created_at",
            range: ["#created_at-date-start", "#created_at-date-end"],
        });
    })

    // 表格渲染
    layui.use(["table", "form", "common", "popup", "util"], function () {
        let table = layui.table;
        let form = layui.form;
        let $ = layui.$;
        let common = layui.common;
        let util = layui.util;

        // 表头参数
        let cols = [
            {
                type: "checkbox"
            }, {
                title: "ID",
                field: "id",
                width: 100,
                sort: true,
            }, {
                title: "网站名称",
                field: "site_name",
            }, {
                title: "平台",
                field: "platform",
            }, {
                title: "平台名称",
                field: "platform_name",
            }, {
                title: "平台URL",
                field: "platform_url",
            }, {
                title: "状态",
                field: "status",
            }, {
                title: "推送时间",
                field: "push_at",
            }, {
                title: "Discord URL",
                field: "discord_url",
            }, {
                title: "平台名称",
                field: "meta_keyword",
            }, {
                title: "平台名称",
                field: "meta_description",
            }, {
                title: "平台名称",
                field: "meta_title",
            }, {
                title: "创建时间",
                field: "created_at",
            }, {
                title: "更新时间",
                field: "updated_at",
                hide: true,
            }, {
                title: "操作",
                toolbar: "#table-bar",
                align: "center",
                fixed: "right",
                width: 80,
            }
        ];

        // 渲染表格
        function render() {
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
                    title: "刷新",
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
        apis.push(["roles", "{{ route('lpadmin.website.config.index') }}?format=select"]);
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
        table.on("tool(data-table)", function (obj) {
            if (obj.event === "remove") {
                remove(obj);
            } else if (obj.event === "edit") {
                edit(obj);
            }
        });

        // 表格顶部工具栏事件
        table.on("toolbar(data-table)", function (obj) {
            if (obj.event === "add") {
                add();
            } else if (obj.event === "refresh") {
                refreshTable();
            } else if (obj.event === "updatePlatform") {
                updatePlatform(obj);
            } else if (obj.event === "pushPlatform") {
                pushPlatform(obj);
            }
        });

        // 表格顶部搜索事件
        form.on("submit(table-query)", function (data) {
            table.reload("data-table", {
                page: {
                    curr: 1
                },
                where: data.field
            })
            return false;
        });

        // 表格顶部搜索重置事件
        form.on("submit(table-reset)", function (data) {
            table.reload("data-table", {
                where: []
            })
        });

        // 表格排序事件
        table.on("sort(data-table)", function (obj) {
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
        let add = function () {
            layer.open({
                type: 2,
                title: "新增",
                shade: 0.1,
                area: [common.isModile() ? "100%" : "70%", common.isModile() ? "100%" : "80%"],
                content: INSERT_URL
            });
        }

        // 表格编辑数据
        let edit = function (obj) {
            let value = obj.data[PRIMARY_KEY];
            let url = UPDATE_URL.replace(':id', value);
            layer.open({
                type: 2,
                title: "修改",
                shade: 0.1,
                area: [common.isModile() ? "100%" : "70%", common.isModile() ? "100%" : "80%"],
                content: url + '?id=' + value
            });
        }

        // 表格新增数据
        let updatePlatform = function (obj) {
            let checkIds = common.checkField(obj, PRIMARY_KEY);
            if (checkIds === "") {
                layui.popup.warning("未选中数据");
                return false;
            }
            checkIds = checkIds.split(",");
            let loading = layer.load();
            $.ajax({
                url: UPDATE_PLATFORM_API,
                data: {
                    ids: checkIds,
                    '_token': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: "json",
                type: "post",
                success: function (res) {
                    layer.close(loading);
                    if (res.code !== 0) {
                        return layui.popup.failure(res.message);
                    }
                    return layui.popup.success("操作成功", refreshTable);
                }
            });
        }
        // 表格新增数据
        let pushPlatform = function (obj) {
            let checkIds = common.checkField(obj, PRIMARY_KEY);
            if (checkIds === "") {
                layui.popup.warning("未选中数据");
                return false;
            }
            checkIds = checkIds.split(",");
            let loading = layer.load();
            $.ajax({
                url: PUSH_PLATFORM_API,
                data: {
                    ids: checkIds,
                    '_token': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: "json",
                type: "post",
                success: function (res) {
                    layer.close(loading);
                    if (res.code !== 0) {
                        return layui.popup.failure(res.message);
                    }
                    return layui.popup.success("操作成功", refreshTable);
                }
            });
        }
        // 删除一行
        let remove = function (obj) {
            return doRemove(obj.data[PRIMARY_KEY]);
        }


        // 执行删除
        let doRemove = function (ids) {
            layer.confirm("确定删除?", {
                icon: 3,
                title: "提示"
            }, function (index) {
                layer.close(index);
                let loading = layer.load();

                // 处理单个或批量删除
                if (Array.isArray(ids)) {
                    // 批量删除
                    return layui.popup.failure('批量删除暂不支持');
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
                        success: function (res) {
                            layer.close(loading);
                            if (res.code !== 0) {
                                return layui.popup.failure(res.message);
                            }
                            return layui.popup.success("操作成功", refreshTable);
                        }
                    });
                }
            });
        }

        // 刷新表格数据
        window.refreshTable = function () {
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
