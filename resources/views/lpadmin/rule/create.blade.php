<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
    <head>
        <meta charset="UTF-8">
        <title>新增权限规则</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <style>
        .menu-tree-item {
            padding: 5px 8px;
            cursor: pointer;
            border-radius: 2px;
            margin: 1px 0;
            display: flex;
            align-items: center;
            font-size: 13px;
        }
        .menu-tree-item:hover {
            background-color: #f2f2f2;
        }
        .menu-tree-item.selected {
            background-color: #1E9FFF;
            color: white;
        }
        .menu-tree-item .menu-icon {
            margin-right: 5px;
            width: 16px;
            text-align: center;
        }
        .menu-tree-item .menu-title {
            flex: 1;
        }
        .menu-tree-item .menu-name {
            color: #999;
            font-size: 11px;
            margin-left: 8px;
        }
        .menu-tree-item.selected .menu-name {
            color: #ccc;
        }
        .menu-tree-item.level-0 {
            font-weight: bold;
            /* background-color: #f8f9fa; */
        }
        .menu-tree-item.level-0 .menu-title {
            color: #333;
        }
        .menu-tree-item.level-1 .menu-title {
            color: #555;
        }
        .menu-tree-item.level-2 .menu-title {
            color: #777;
        }
        .menu-tree-item.level-3 .menu-title {
            color: #999;
        }
        .menu-type-badge {
            font-size: 10px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 5px;
        }
        .menu-type-directory {
            background: #1E9FFF;
            color: white;
        }
        .menu-type-menu {
            background: #5FB878;
            color: white;
        }
        .menu-type-button {
            background: #FF5722;
            color: white;
        }
        .no-results {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 12px;
        }
    </style>
    <link rel="stylesheet" href="/static/admin/css/form-common.css" />
    </head>
    <body>

        <form class="layui-form" action="">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label">上级权限</label>
                        <div class="layui-input-block">
                            <select name="parent_id" id="parent_id">
                                <option value="0">顶级权限</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">权限名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="title" value="" required lay-verify="required" class="layui-input" placeholder="请输入权限名称">
                        </div>
                    </div>

                    <div class="layui-form-item" id="permission-name-item">
                        <label class="layui-form-label required">权限标识</label>
                        <div class="layui-input-block">
                            <div class="menu-selector-container">
                                <div class="menu-search-box" style="margin-bottom: 10px;">
                                    <input type="text" id="menu-search-input" class="layui-input" placeholder="搜索菜单标题或标识..." style="height: 32px;">
                                </div>
                                <div class="menu-tree-container" style="border: 1px solid #e6e6e6; border-radius: 2px; max-height: 200px; overflow-y: auto; padding: 5px;">
                                    <div id="menu-tree-list">
                                        <div class="loading-text" style="text-align: center; padding: 20px; color: #999;">加载中...</div>
                                    </div>
                                </div>
                                <input type="hidden" name="name" id="selected-menu-name" lay-verify="required">
                                <div class="selected-menu-display" style="margin-top: 8px; padding: 5px; background: #f8f8f8; border-radius: 2px; min-height: 20px;">
                                    <span id="selected-menu-text" style="color: #666;">请选择菜单或 <a href="javascript:void(0)" id="show-custom-input" style="color: #1E9FFF;">输入自定义标识</a></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item" id="custom-name-item" style="display: none;">
                        <label class="layui-form-label">自定义标识</label>
                        <div class="layui-input-block">
                            <input type="text" id="custom-name-input" class="layui-input" placeholder="输入自定义权限标识，如：user.export">
                            <div class="layui-form-mid layui-word-aux">
                                <a href="javascript:void(0)" id="use-custom-name">确认使用</a> |
                                <a href="javascript:void(0)" id="back-to-menu-select">返回菜单选择</a>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">权限类型</label>
                        <div class="layui-input-block">
                            <select name="type" lay-verify="required" lay-filter="rule-type">
                                <option value="">请选择权限类型</option>
                                <option value="menu">菜单权限 - 控制页面访问</option>
                                <option value="button">按钮权限 - 控制操作功能</option>
                                <option value="api">接口权限 - 控制API调用</option>
                            </select>
                            <div class="layui-form-mid layui-word-aux">
                                <span id="type-help-text">选择权限类型以确定权限的作用范围</span>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item" id="url-item">
                        <label class="layui-form-label">路由/URL</label>
                        <div class="layui-input-block">
                            <input type="text" name="url" value="" class="layui-input" placeholder="请输入路由或URL">
                        </div>
                    </div>

                    <div class="layui-form-item" id="method-item" style="display: none;">
                        <label class="layui-form-label">请求方法</label>
                        <div class="layui-input-block">
                            <select name="method">
                                <option value="">请选择请求方法</option>
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                                <option value="PATCH">PATCH</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item" id="icon-item">
                        <label class="layui-form-label">图标</label>
                        <div class="layui-input-block">
                            <input type="text" name="icon" id="icon" value="" class="layui-input" placeholder="请选择图标">
                            <div class="layui-form-mid layui-word-aux">
                                点击输入框选择图标
                                <span id="icon-preview"></span>
                            </div>
                        </div>
                    </div>



                    <div class="layui-form-item">
                        <label class="layui-form-label">描述</label>
                        <div class="layui-input-block">
                            <textarea name="remark" class="layui-textarea" placeholder="请输入权限描述"></textarea>
                        </div>
                    </div>

                    {{-- 使用通用单选框组件 --}}
                    @include('lpadmin.components.radio-group', [
                        'type' => 'status',
                        'name' => 'status',
                        'label' => __('admin.status'),
                        'required' => false,
                        'default' => '1'
                    ])

                    <div class="layui-form-item">
                        <label class="layui-form-label">排序</label>
                        <div class="layui-input-block">
                            <input type="number" name="sort" value="0" class="layui-input" placeholder="数字越小排序越靠前">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-sm" lay-submit lay-filter="save">
                        <i class="layui-icon layui-icon-ok"></i>{{ __('admin.submit') }}</button>
                    <button type="reset" class="pear-btn pear-btn-sm">
                        <i class="layui-icon layui-icon-refresh"></i>{{ __('admin.reset') }}</button>
                </div>
            </div>

        </form>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>

            // 相关接口
            const INSERT_API = "{{ route('lpadmin.rule.store') }}";
            const PARENT_API = "{{ route('lpadmin.rule.index') }}?format=tree";
            const MENU_API = "{{ route('lpadmin.api.menu_tree') }}";

            layui.use(["form", "popup", "iconPicker"], function () {
                let form = layui.form;
                let iconPicker = layui.iconPicker;
                let $ = layui.$;

                // 初始化单选框
                if (window.RadioHelper) {
                    RadioHelper.init('status');
                }

                // 图标选择器
                iconPicker.render({
                    elem: '#icon',
                    type: 'fontClass',
                    page: true,
                    limit: 12,
                    search: true,
                    click: function(data) {
                        // 更新预览图标
                        $('#icon-preview').html('<i class="layui-icon ' + data.icon + '" style="margin-left: 10px; font-size: 16px; color: #1890ff;"></i>');
                    }
                });

                // 加载上级权限选项
                $.ajax({
                    url: PARENT_API,
                    type: 'GET',
                    success: function(res) {
                        if (res.code === 0 && res.data) {
                            let html = '<option value="0">顶级权限</option>';
                            function buildOptions(data, level = 0) {
                                data.forEach(function(item) {
                                    let prefix = '';
                                    // 添加层级指示符
                                    if (level > 0) {
                                        for (let i = 0; i < level; i++) {
                                            prefix += i === level - 1 ? '├─ ' : '│　';
                                        }
                                    }
                                    html += '<option value="' + item.id + '">' + prefix + item.title + '</option>';
                                    if (item.children && item.children.length > 0) {
                                        buildOptions(item.children, level + 1);
                                    }
                                });
                            }
                            buildOptions(res.data);
                            $('#parent_id').html(html);

                            // 设置默认选中的父级
                            let parentId = new URLSearchParams(window.location.search).get('parent_id');
                            if (parentId) {
                                $('#parent_id').val(parentId);
                            }

                            form.render('select');
                        }
                    }
                });

                // 全局变量存储菜单数据
                let menuData = [];
                let filteredMenuData = [];

                // 加载菜单数据
                $.ajax({
                    url: MENU_API,
                    type: 'GET',
                    success: function(res) {
                        if (res.code === 0 && res.data) {
                            menuData = flattenMenuData(res.data);
                            filteredMenuData = [...menuData];
                            renderMenuTree(filteredMenuData);
                        } else {
                            $('#menu-tree-list').html('<div class="no-results">加载菜单失败</div>');
                        }
                    },
                    error: function() {
                        $('#menu-tree-list').html('<div class="no-results">加载菜单失败</div>');
                    }
                });

                // 将树形数据扁平化，添加层级信息
                function flattenMenuData(treeData, level = 0, parentPath = '') {
                    let result = [];
                    treeData.forEach(function(item) {
                        let currentPath = parentPath ? parentPath + ' > ' + item.title : item.title;
                        let menuItem = {
                            id: item.id,
                            name: item.name,
                            title: item.title,
                            type: item.type,
                            level: level,
                            icon: item.icon || '',
                            path: currentPath,
                            parent_id: item.parent_id || 0
                        };
                        result.push(menuItem);

                        if (item.children && item.children.length > 0) {
                            result = result.concat(flattenMenuData(item.children, level + 1, currentPath));
                        }
                    });
                    return result;
                }

                // 渲染菜单树
                function renderMenuTree(data) {
                    if (data.length === 0) {
                        $('#menu-tree-list').html('<div class="no-results">没有找到匹配的菜单</div>');
                        return;
                    }

                    let html = '';
                    data.forEach(function(item) {
                        let typeText = '';
                        let typeClass = '';
                        switch(item.type) {
                            case 0:
                                typeText = '目录';
                                typeClass = 'menu-type-directory';
                                break;
                            case 1:
                                typeText = '菜单';
                                typeClass = 'menu-type-menu';
                                break;
                            case 2:
                                typeText = '按钮';
                                typeClass = 'menu-type-button';
                                break;
                        }

                        html += '<div class="menu-tree-item level-' + item.level + '" data-name="' + item.name + '" data-title="' + item.title + '" data-type="' + item.type + '" style="padding-left: ' + (8 + item.level * 20) + 'px;">';
                        html += '<span class="menu-icon">' + (item.icon ? '<i class="layui-icon ' + item.icon + '"></i>' : (item.level === 0 ? '📁' : (item.type === 2 ? '🔘' : '📄'))) + '</span>';

                        // 添加层级指示符
                        let levelIndicator = '';
                        if (item.level > 0) {
                            for (let i = 0; i < item.level; i++) {
                                levelIndicator += i === item.level - 1 ? '├─ ' : '│　';
                            }
                        }

                        html += '<span class="menu-title">' + levelIndicator + item.title + '</span>';
                        html += '<span class="menu-name">(' + item.name + ')</span>';
                        html += '<span class="menu-type-badge ' + typeClass + '">' + typeText + '</span>';
                        html += '</div>';
                    });
                    $('#menu-tree-list').html(html);
                }

                // 菜单搜索功能
                $('#menu-search-input').on('input', function() {
                    let searchText = $(this).val().toLowerCase().trim();

                    if (searchText === '') {
                        filteredMenuData = [...menuData];
                    } else {
                        filteredMenuData = menuData.filter(function(item) {
                            return item.title.toLowerCase().indexOf(searchText) > -1 ||
                                   item.name.toLowerCase().indexOf(searchText) > -1;
                        });
                    }

                    renderMenuTree(filteredMenuData);
                });

                // 菜单项点击事件
                $(document).on('click', '.menu-tree-item', function() {
                    $('.menu-tree-item').removeClass('selected');
                    $(this).addClass('selected');

                    let name = $(this).data('name');
                    let title = $(this).data('title');
                    let type = $(this).data('type');

                    $('#selected-menu-name').val(name);

                    let typeText = type == 0 ? '目录' : (type == 1 ? '菜单' : '按钮');
                    $('#selected-menu-text').html('已选择：<strong>' + title + '</strong> (' + name + ') [' + typeText + '] <a href="javascript:void(0)" id="clear-selection" style="color: #FF5722; margin-left: 10px;">{{ __('admin.clear') }}</a>');
                });

                // 清除选择
                $(document).on('click', '#clear-selection', function() {
                    $('.menu-tree-item').removeClass('selected');
                    $('#selected-menu-name').val('');
                    $('#selected-menu-text').html('请选择菜单或 <a href="javascript:void(0)" id="show-custom-input" style="color: #1E9FFF;">输入自定义标识</a>');
                });

                // 显示自定义输入
                $(document).on('click', '#show-custom-input', function() {
                    $('#permission-name-item').hide();
                    $('#custom-name-item').show();
                    $('#custom-name-input').focus();
                });

                // 使用自定义标识
                $('#use-custom-name').click(function() {
                    let customName = $('#custom-name-input').val().trim();
                    if (customName) {
                        $('#selected-menu-name').val(customName);
                        $('#selected-menu-text').html('已选择：<strong>自定义标识</strong> (' + customName + ') [自定义] <a href="javascript:void(0)" id="clear-selection" style="color: #FF5722; margin-left: 10px;">{{ __('admin.clear') }}</a>');

                        // 隐藏自定义输入，显示选择框
                        $('#custom-name-item').hide();
                        $('#permission-name-item').show();

                        // 清空搜索框和选择状态
                        $('#menu-search-input').val('');
                        $('.menu-tree-item').removeClass('selected');
                        filteredMenuData = [...menuData];
                        renderMenuTree(filteredMenuData);
                    } else {
                        layer.msg('请输入自定义权限标识', {icon: 2});
                    }
                });

                // 返回菜单选择
                $('#back-to-menu-select').click(function() {
                    $('#custom-name-item').hide();
                    $('#permission-name-item').show();
                    $('#custom-name-input').val('');
                });

                // 权限类型变化事件
                form.on('select(rule-type)', function(data) {
                    let type = data.value;
                    let helpText = '';

                    if (type === 'api') {
                        $('#method-item').show();
                        $('#icon-item').hide();
                        helpText = 'API权限：控制接口调用权限，通常用于后端API访问控制';
                    } else if (type === 'menu') {
                        $('#method-item').hide();
                        $('#icon-item').show();
                        helpText = '菜单权限：控制用户能否访问某个页面，对应左侧菜单项';
                    } else if (type === 'button') {
                        $('#method-item').hide();
                        $('#icon-item').show();
                        helpText = '按钮权限：控制页面内的操作功能，如添加、编辑、删除等按钮';
                    } else {
                        helpText = '选择权限类型以确定权限的作用范围';
                    }

                    $('#type-help-text').text(helpText);
                });

                //提交事件
                form.on("submit(save)", function (data) {
                    // 修复单选框数据
                    if (window.RadioHelper) {
                        RadioHelper.fixFormData(data.field, ['status']);
                    }

                    // 添加CSRF token
                    data.field._token = '{{ csrf_token() }}';

                    layui.$.ajax({
                        url: INSERT_API,
                        type: "POST",
                        dateType: "json",
                        data: data.field,
                        success: function (res) {
                            if (res.code !== 0) {
                                return layui.popup.failure(res.message);
                            }
                            return layui.popup.success("{{ __('admin.success') }}", function () {
                                parent.refreshTable();
                                parent.layer.close(parent.layer.getFrameIndex(window.name));
                            });
                        }
                    });
                    return false;
                });
            });

        </script>

    </body>

</html>
