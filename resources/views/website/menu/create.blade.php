<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
    <head>
        <meta charset="UTF-8">
        <title>新增管理员</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form" action="">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.website') }}</label>
                        <div class="layui-input-block">
                            <select name="site_id" id="site_id" >
                                @foreach($siteConfigList as $site)
                                    <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.site_category_name') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_name" value="" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_category_name_en') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_name_en" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_category_url') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_url" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_category_sort') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="range" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_category_type') }}</label>
                        <div class="layui-input-block">
                            <select name="type" id="type" >
                                @foreach($menuType as $menu)
                                <option value="{{ $menu['type'] }}">{{ $menu['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.site_category_keywords') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_keyword" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.site_category_description') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_description" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.site_category_title') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_title" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.status') }}</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" data-value="0" title="{{ __('admin.enabled') }}" lay-filter="status" checked>
                            <input type="radio" name="status" data-value="1" title="{{ __('admin.disabled') }}" lay-filter="status">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit=""
                        lay-filter="save">{{ __('admin.submit') }}</button>
                    <button type="reset" class="pear-btn pear-btn-md">{{ __('admin.reset') }}</button>
                </div>
            </div>

        </form>

        <style>
            .avatar-container button {
                margin-left: 10px;
            }
        </style>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>

            // 相关接口
            const INSERT_API = "{{ route('lpadmin.website.menu.store') }}";



            //提交事件
            layui.use(["form", "popup"], function () {
                // 初始化单选框
                if (window.RadioHelper) {
                    RadioHelper.init('status');
                }

                // 表单提交处理
                layui.form.on("submit(save)", function (data) {
                    // 添加CSRF token
                    data.field._token = '{{ csrf_token() }}';

                    // 修复单选框数据
                    RadioHelper.fixFormData(data.field, ['status']);

                    console.log('提交的表单数据:', data.field);

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
