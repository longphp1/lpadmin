<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ __('admin.import_product') }}</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form" action="" lay-filter="uploadForm">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.website') }}</label>
                        <div class="layui-input-block">
                            <select name="site_id" id="site_id">
                                @foreach($siteConfigList as $site)
                                    <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.category') }}</label>
                        <div class="layui-input-block">
                            <select name="menu_id" id="menu_id">
                                @foreach($menuList as $menu)
                                    <option value="{{ $menu['id'] }}">{{ $menu['menu_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.file_note') }}</label>
                        <div class="layui-input-block" style="color:#666;line-height:1.8;padding-top:8px;">{{ __('admin.import_keep') }}<br>{{ __('admin.import_url_only') }}</div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.product_file') }}</label>
                        <div class="layui-input-block">
                            <input type="file" name="file" id="upload-btn" class="layui-input">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="button" class="pear-btn pear-btn-primary pear-btn-md" id="upload-all">{{ __('admin.submit') }}</button>
                    <button type="reset" class="pear-btn pear-btn-md">{{ __('admin.reset') }}</button>
                </div>
            </div>

        </form>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>
            const UPLOAD_API = "{{ route('lpadmin.website.product.import') }}";

            layui.use(["form", "popup"], function () {
                var form = layui.form;
                var $ = layui.jquery;
                var csrfToken = '{{ csrf_token() }}';
                form.render();

                $('#upload-all').on('click', function () {
                    var fileInput = document.getElementById('upload-btn');
                    if (!fileInput.files.length) {
                        layui.popup.failure('{{ __('admin.choose_file') }}');
                        return;
                    }

                    var siteId = $('#site_id').val();
                    var menuId = $('#menu_id').val();
                    console.log('upload site_id:', siteId, 'menu_id:', menuId);

                    var formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    formData.append('site_id', siteId);
                    formData.append('menu_id', menuId);

                    $.ajax({
                        url: UPLOAD_API,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function (res) {
                            console.log('upload response:', res);
                            if (res.code === 0) {
                                layui.popup.success(res.message || "{{ __('admin.import_ok') }}", function () {
                                    parent.refreshTable();
                                    parent.layer.close(parent.layer.getFrameIndex(window.name));
                                });
                            } else {
                                layui.popup.failure(res.message || "{{ __('admin.upload_failed') }}");
                            }
                        },
                        error: function (xhr, status, err) {
                            console.error('upload error:', err);
                            layui.popup.failure('{{ __('admin.request_failed') }}' + err);
                        }
                    });
                });
            });
        </script>

    </body>
</html>
