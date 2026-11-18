<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('admin.file_mgmt') }}</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <link rel="stylesheet" href="/static/admin/css/table-common.css" />
    <style>
        /* 统计信息样式 */
        .stats-container {
            padding: 10px 0;
        }

        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-item {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 20px 15px;
            background: #fff;
            border: 1px solid #e6e6e6;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }

        .stat-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 500;
        }

        .category-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-stat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            min-width: 120px;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .category-name {
            color: #495057;
            margin-right: 10px;
            font-weight: 500;
        }

        .category-count {
            background: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }

        .layui-card-header-extra {
            float: right;
        }

        @media (max-width: 768px) {
            .stats-row {
                flex-direction: column;
            }

            .stat-item {
                min-width: auto;
            }

            .category-stats {
                justify-content: center;
            }
        }
    </style>
</head>
<body class="pear-container">

    <!-- 统计信息展示 -->
    <div class="layui-card">
        <div class="layui-card-header">
            <span>{{ __('admin.file_stats') }}</span>
            <div class="layui-card-header-extra">
                <button type="button" class="layui-btn layui-btn-xs" id="refresh-stats">
                    <i class="layui-icon layui-icon-refresh"></i> {{ __('admin.refresh_stats') }}
                </button>
            </div>
        </div>
        <div class="layui-card-body">
            <div class="stats-container">
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-number" id="total-files">-</div>
                        <div class="stat-label">{{ __('admin.total_files') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" id="total-size">-</div>
                        <div class="stat-label">{{ __('admin.total_size') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" id="today-files">-</div>
                        <div class="stat-label">{{ __('admin.today_uploads') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" id="this-week-files">-</div>
                        <div class="stat-label">{{ __('admin.week_uploads') }}</div>
                    </div>
                </div>
                <div class="stats-row">
                    <div class="category-stats" id="category-stats">
                        <!-- 分类统计将通过JavaScript动态加载 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 顶部查询表单 -->
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" action="">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">{{ __('admin.file_name') }}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="original_name" placeholder="{{ __('admin.enter_filename') }}" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">{{ __('admin.file_type') }}</label>
                        <div class="layui-input-inline">
                            <select name="type">
                                <option value="">{{ __('admin.all_types') }}</option>
                                <option value="image">{{ __('admin.image_file') }}</option>
                                <option value="document">{{ __('admin.document') }}</option>
                                <option value="video">{{ __('admin.video') }}</option>
                                <option value="audio">{{ __('admin.audio') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">{{ __('admin.file_category') }}</label>
                        <div class="layui-input-inline">
                            <select name="category" id="category-select">
                                <option value="">{{ __('admin.all_categories') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">{{ __('admin.extension') }}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="extension" placeholder="{{ __('admin.enter_extension') }}" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button type="submit" class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="upload-query">
                            <i class="layui-icon layui-icon-search"></i>{{ __('admin.search') }}</button>
                        <button type="reset" class="pear-btn pear-btn-md">
                            <i class="layui-icon layui-icon-refresh"></i>{{ __('admin.reset') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="upload-table" lay-filter="upload-table"></table>

            <script type="text/html" id="upload-toolbar">
                <div class="layui-btn-group">
                    <button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="add">
                        <i class="layui-icon layui-icon-add-1"></i>
                        {{ __('admin.upload_file') }}
                    </button>
                    <button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="batchRemove">
                        <i class="layui-icon layui-icon-delete"></i>
                        {{ __('admin.batch_delete') }}
                    </button>
                    <button class="pear-btn pear-btn-warm pear-btn-sm" lay-event="refresh">
                        <i class="layui-icon layui-icon-refresh"></i>{{ __('admin.refresh') }}</button>
                </div>
            </script>

            <script type="text/html" id="upload-preview">
                @{{# if(d.mime_type && d.mime_type.indexOf('image/') === 0) { }}
                    <img src="@{{d.url}}" alt="@{{d.original_name}}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer;" onclick="layer.photos({photos: {data: [{src: '@{{d.url}}', alt: '@{{d.original_name}}'}]}, anim: 5});">
                @{{# } else { }}
                    <div style="width: 60px; height: 60px; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #999;">
                        @{{d.extension.toUpperCase()}}
                    </div>
                @{{# } }}
            </script>

            <script type="text/html" id="upload-size">
                @{{d.formatted_size}}
            </script>

            <script type="text/html" id="upload-toolbar-right">
                <div style="white-space: nowrap; display: flex; gap: 3px; justify-content: center;">
                    <button class="table-action-btn table-action-view" lay-event="preview" title="{{ __('admin.preview') }}">
                        <i class="layui-icon layui-icon-eye"></i>
                    </button>
                    <button class="table-action-btn table-action-edit" lay-event="download" title="{{ __('admin.download') }}">
                        <i class="layui-icon layui-icon-download-circle"></i>
                    </button>
                    <button class="table-action-btn table-action-delete" lay-event="remove" title="{{ __('admin.delete') }}">
                        <i class="layui-icon layui-icon-delete"></i>
                    </button>
                </div>
            </script>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>

        // 相关常量
        const PRIMARY_KEY = "id";
        const SELECT_API = "{{ route('lpadmin.upload.index') }}";
        const REMOVE_API = "{{ route('lpadmin.upload.destroy', ':id') }}";
        const BATCH_REMOVE_API = "{{ route('lpadmin.upload.batch_delete') }}";
        const UPLOAD_URL = "{{ route('lpadmin.upload.create') }}";

        layui.use(['table', 'form', 'jquery', 'popup'], function () {
            let table = layui.table;
            let form = layui.form;
            let $ = layui.jquery;

            // 加载分类选项
            $.get('{{ route("lpadmin.upload.categories") }}', function(res) {
                if (res.code === 0) {
                    let categorySelect = $('#category-select');
                    $.each(res.data.categories, function(key, value) {
                        categorySelect.append('<option value="' + key + '">' + value + '</option>');
                    });
                    form.render('select');
                }
            });

            // 表格列配置
            let cols = [
                [
                    {type: 'checkbox', fixed: 'left'},
                    {title: 'ID', field: 'id', width: 80, align: 'center', sort: true},
                    {title: '{{ __('admin.preview') }}', field: 'preview', width: 100, align: 'center', templet: '#upload-preview'},
                    {title: '{{ __('admin.file_name') }}', field: 'original_name', width: 180, align: 'left'},
                    {title: '{{ __('admin.col_category') }}', field: 'category_label', width: 100, align: 'center'},
                    {title: '{{ __('admin.type') }}', field: 'type_label', width: 80, align: 'center'},
                    {title: '{{ __('admin.size') }}', field: 'size', width: 100, align: 'center', templet: '#upload-size'},
                    {title: '{{ __('admin.tags') }}', field: 'tags_string', width: 120, align: 'center'},
                    {title: '{{ __('admin.uploader') }}', field: 'admin_name', width: 100, align: 'center'},
                    {title: '{{ __('admin.uploaded_at') }}', field: 'created_at', width: 160, align: 'center'},
                    {title: '{{ __('admin.actions_col') }}', width: 120, align: 'center', toolbar: '#upload-toolbar-right', fixed: 'right'}
                ]
            ];

            table.render({
                elem: '#upload-table',
                url: SELECT_API,
                method: 'GET',
                toolbar: '#upload-toolbar',
                defaultToolbar: [{
                    title: '{{ __('admin.refresh') }}',
                    layEvent: 'refresh',
                    icon: 'layui-icon-refresh',
                }, 'filter', 'print', 'exports'],
                cols: cols,
                skin: 'line',
                size: 'lg',
                page: true,
                limits: [10, 20, 30, 50, 100],
                limit: 20,
                autoSort: false,
                done: function () {
                    // 启用图片预览
                    layer.photos({photos: 'div[lay-id="upload-table"]', anim: 5});
                }
            });

            // 搜索表单提交
            form.on('submit(upload-query)', function (data) {
                table.reload('upload-table', {
                    where: data.field,
                    page: {curr: 1}
                });
                return false;
            });

            // 头部工具栏事件
            table.on('toolbar(upload-table)', function (obj) {
                if (obj.event === 'add') {
                    layer.open({
                        type: 2,
                        title: '{{ __('admin.upload_file') }}',
                        shade: 0.1,
                        area: ['800px', '600px'],
                        content: UPLOAD_URL
                    });
                } else if (obj.event === 'batchRemove') {
                    let checkStatus = table.checkStatus('upload-table');
                    let data = checkStatus.data;
                    if (data.length === 0) {
                        layer.msg('{{ __('admin.select_to_delete') }}');
                        return;
                    }
                    let ids = data.map(item => item[PRIMARY_KEY]);
                    layer.confirm('{{ __('admin.confirm_delete_selected') }}', function (index) {
                        $.ajax({
                            url: BATCH_REMOVE_API,
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                if (res.code === 0) {
                                    layer.msg(res.message, {icon: 1});
                                    table.reload('upload-table');
                                } else {
                                    layer.msg(res.message, {icon: 2});
                                }
                            }
                        });
                        layer.close(index);
                    });
                } else if (obj.event === 'refresh') {
                    table.reload('upload-table');
                    loadStatistics(); // 刷新时也更新统计信息
                }
            });

            // 行工具事件
            table.on('tool(upload-table)', function (obj) {
                let data = obj.data;
                if (obj.event === 'preview') {
                    // 使用新的预览API
                    $.get('{{ route("lpadmin.upload.preview", ":id") }}'.replace(':id', data.id), function(res) {
                        if (res.code === 0) {
                            let previewData = res.data;
                            if (previewData.preview_type === 'image') {
                                layer.photos({
                                    photos: {
                                        data: [{
                                            src: previewData.url,
                                            alt: previewData.original_name
                                        }]
                                    },
                                    anim: 5
                                });
                            } else if (previewData.preview_type === 'video') {
                                layer.open({
                                    type: 1,
                                    title: '{{ __('admin.video_preview') }} - ' + previewData.original_name,
                                    area: ['800px', '600px'],
                                    content: '<video controls style="width:100%;height:100%;"><source src="' + previewData.url + '" type="' + previewData.mime_type + '">{{ __('admin.video_unsupported') }}</video>'
                                });
                            } else if (previewData.preview_type === 'audio') {
                                layer.open({
                                    type: 1,
                                    title: '{{ __('admin.audio_preview') }} - ' + previewData.original_name,
                                    area: ['400px', '200px'],
                                    content: '<audio controls style="width:100%;"><source src="' + previewData.url + '" type="' + previewData.mime_type + '">{{ __('admin.audio_unsupported') }}</audio>'
                                });
                            } else if (previewData.preview_type === 'text') {
                                layer.open({
                                    type: 1,
                                    title: '{{ __('admin.text_preview') }} - ' + previewData.original_name,
                                    area: ['800px', '600px'],
                                    content: '<pre style="padding:20px;max-height:500px;overflow:auto;">' + (previewData.content || '{{ __('admin.cannot_read_content') }}') + '</pre>'
                                });
                            } else {
                                window.open(previewData.url, '_blank');
                            }
                        } else {
                            layer.msg('{{ __('admin.preview_failed') }}：' + res.message, {icon: 2});
                        }
                    });
                } else if (obj.event === 'download') {
                    // 使用新的下载API
                    window.open('{{ route("lpadmin.upload.download", ":id") }}'.replace(':id', data.id), '_blank');
                } else if (obj.event === 'remove') {
                    layer.confirm('{{ __('admin.confirm_delete_one') }}', function (index) {
                        $.ajax({
                            url: REMOVE_API.replace(':id', data[PRIMARY_KEY]),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                if (res.code === 0) {
                                    layer.msg(res.message, {icon: 1});
                                    table.reload('upload-table');
                                } else {
                                    layer.msg(res.message, {icon: 2});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }
            });

            // 表格排序事件
            table.on('sort(upload-table)', function (obj) {
                table.reload('upload-table', {
                    initSort: obj,
                    where: {
                        field: obj.field,
                        order: obj.type
                    }
                });
            });

            // 加载统计信息
            function loadStatistics() {
                $.get('{{ route("lpadmin.upload.statistics") }}', function(res) {
                    if (res.code === 0) {
                        let stats = res.data;

                        // 更新基础统计
                        $('#total-files').text(stats.total_files || 0);
                        $('#total-size').text(stats.total_size_formatted || '0 B');
                        $('#today-files').text(stats.today_files || 0);
                        $('#this-week-files').text(stats.this_week_files || 0);

                        // 更新分类统计
                        let categoryHtml = '';
                        if (stats.categories) {
                            $.each(stats.categories, function(key, data) {
                                categoryHtml += '<div class="category-stat-item">';
                                categoryHtml += '<span class="category-name">' + data.label + '</span>';
                                categoryHtml += '<span class="category-count">' + (data.count || 0) + '</span>';
                                categoryHtml += '</div>';
                            });
                        }
                        $('#category-stats').html(categoryHtml);
                    } else {
                        console.error('获取统计信息失败:', res.message);
                    }
                }).fail(function() {
                    console.error('统计信息请求失败');
                });
            }

            // 刷新统计按钮事件
            $('#refresh-stats').on('click', function() {
                loadStatistics();
                layer.msg('{{ __('admin.stats_refreshed') }}', {icon: 1, time: 1000});
            });

            // 页面加载时获取统计信息
            loadStatistics();
        });
    </script>
</body>
</html>
