<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('admin.upload_config') }}</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body class="pear-container">

    <div class="layui-card">
        <div class="layui-card-header">
            <h2 class="header-title">{{ __('admin.upload_config') }}</h2>
        </div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="upload-config-form">
                
                <!-- 基础配置 -->
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>{{ __('admin.basic_config') }}</legend>
                </fieldset>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.option_upload_max_size') }}</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="number" name="max_size" placeholder="{{ __('admin.enter_file_size') }}" class="layui-input" min="1" max="102400">
                    </div>
                    <div class="layui-form-mid layui-word-aux">KB (1KB - 100MB)</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.option_upload_disk') }}</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <select name="disk">
                            <option value="public">{{ __('admin.option_upload_disk_public') }}</option>
                            <option value="local">{{ __('admin.option_upload_disk_local') }}</option>
                        </select>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin.storage_location_hint') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.option_upload_path') }}</label>
                    <div class="layui-input-inline" style="width: 300px;">
                        <input type="text" name="path" placeholder="{{ __('admin.enter_storage_path') }}" class="layui-input">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin.option_desc_upload_path') }}</div>
                </div>

                <!-- 文件类型配置 -->
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>{{ __('admin.allowed_types') }}</legend>
                </fieldset>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.image_files') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_image" placeholder="jpg,jpeg,png,gif,webp,bmp,svg" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.comma_example', ['example' => 'jpg,png,gif']) }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.document_files') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_document" placeholder="pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.comma_example', ['example' => 'pdf,doc,docx']) }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.video_files') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_video" placeholder="mp4,avi,mov,wmv,flv,mkv,webm" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.comma_example', ['example' => 'mp4,avi,mov']) }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.audio_files') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_audio" placeholder="mp3,wav,flac,aac,ogg,wma" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.comma_example', ['example' => 'mp3,wav,flac']) }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.archive_files') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_archive" placeholder="zip,rar,7z,tar,gz" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.comma_example', ['example' => 'zip,rar,7z']) }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.code_files') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_code" placeholder="js,css,html,php,py,java,cpp,c,json,xml" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.comma_example', ['example' => 'js,css,html']) }}</div>
                    </div>
                </div>

                <!-- 安全配置 -->
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>{{ __('admin.security_config') }}</legend>
                </fieldset>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <input type="checkbox" name="enable_security_check" title="{{ __('admin.enable_security_check') }}" >
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.security_check_hint') }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <input type="checkbox" name="enable_duplicate_check" title="{{ __('admin.enable_duplicate_check') }}" >
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.duplicate_check_hint') }}</div>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="pear-btn pear-btn-primary" lay-submit lay-filter="config-submit">
                            <i class="layui-icon layui-icon-ok"></i>
                            {{ __('admin.save_config') }}
                        </button>
                        <button type="reset" class="pear-btn pear-btn-warm">
                            <i class="layui-icon layui-icon-refresh"></i>{{ __('admin.reset') }}</button>
                        <button type="button" class="pear-btn pear-btn-normal" id="load-current">
                            <i class="layui-icon layui-icon-download-circle"></i>
                            {{ __('admin.load_current_config') }}
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
        layui.use(['form', 'jquery'], function () {
            let form = layui.form;
            let $ = layui.jquery;

            // 加载当前配置
            function loadCurrentConfig() {
                $.get('{{ route("lpadmin.upload.config") }}', function(res) {
                    if (res.code === 0) {
                        let config = res.data;
                        
                        // 基础配置
                        $('input[name="max_size"]').val(Math.floor(config.max_size / 1024));
                        $('select[name="disk"]').val(config.disk);
                        $('input[name="path"]').val(config.path);
                        
                        // 文件类型配置
                        $('input[name="extensions_image"]').val(config.allowed_extensions.image ? config.allowed_extensions.image.join(',') : '');
                        $('input[name="extensions_document"]').val(config.allowed_extensions.document ? config.allowed_extensions.document.join(',') : '');
                        $('input[name="extensions_video"]').val(config.allowed_extensions.video ? config.allowed_extensions.video.join(',') : '');
                        $('input[name="extensions_audio"]').val(config.allowed_extensions.audio ? config.allowed_extensions.audio.join(',') : '');
                        $('input[name="extensions_archive"]').val(config.allowed_extensions.archive ? config.allowed_extensions.archive.join(',') : '');
                        $('input[name="extensions_code"]').val(config.allowed_extensions.code ? config.allowed_extensions.code.join(',') : '');

                        // 安全配置
                        $('input[name="enable_security_check"]').prop('checked', config.enable_security_check);
                        $('input[name="enable_duplicate_check"]').prop('checked', config.enable_duplicate_check);

                        form.render();
                    } else {
                        layer.msg('{{ __('admin.load_config_failed') }}', {icon: 2});
                    }
                });
            }

            // 页面加载时自动加载当前配置
            loadCurrentConfig();

            // 手动加载配置按钮
            $('#load-current').click(function() {
                loadCurrentConfig();
                layer.msg('{{ __('admin.config_reloaded') }}', {icon: 1, time: 1500});
            });

            // 表单提交
            form.on('submit(config-submit)', function(data) {
                let formData = data.field;
                
                // 处理文件扩展名
                let allowedExtensions = [];
                ['image', 'document', 'video', 'audio', 'archive', 'code'].forEach(function(type) {
                    let extensions = formData['extensions_' + type];
                    if (extensions) {
                        extensions.split(',').forEach(function(ext) {
                            ext = ext.trim();
                            if (ext && allowedExtensions.indexOf(ext) === -1) {
                                allowedExtensions.push(ext);
                            }
                        });
                    }
                });

                let submitData = {
                    max_size: parseInt(formData.max_size),
                    disk: formData.disk,
                    path: formData.path,
                    allowed_extensions: allowedExtensions,
                    _token: '{{ csrf_token() }}'
                };

                // 处理布尔值字段
                if (formData.enable_security_check !== undefined) {
                    submitData.enable_security_check = formData.enable_security_check === 'on' ? 1 : 0;
                }
                if (formData.enable_duplicate_check !== undefined) {
                    submitData.enable_duplicate_check = formData.enable_duplicate_check === 'on' ? 1 : 0;
                }
                if (!formData.enable_security_check) {
                    submitData.enable_security_check = 0;
                }
                if (!formData.enable_duplicate_check) {
                    submitData.enable_duplicate_check = 0;
                }

                $.ajax({
                    url: '{{ route("lpadmin.upload.config_update") }}',
                    type: 'POST',
                    data: submitData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.code === 0) {
                            layer.msg('{{ __('admin.config_saved') }}', {icon: 1, time: 2000});
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    },
                    error: function() {
                        layer.msg('{{ __('admin.network_retry') }}', {icon: 2});
                    }
                });

                return false;
            });
        });
    </script>
</body>
</html>
