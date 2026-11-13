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
                        <label class="layui-form-label required">{{ __('admin.choose_site') }}</label>
                        <div class="layui-input-block">
                            <select name="site_id" id="site_id" >
                                @foreach($siteConfigList as $site)
                                    <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.home_header') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="top_head" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_title') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="large_head" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_footer') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="top_head_bottom" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_content') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="header_content" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_qr') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_qr_code" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_qr_title') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_qr_title" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_middle_image') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_logo" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_middle_content') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_content" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">{{ __('admin.home_content') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="content" value=""  class="layui-input">
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

        </style>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>

            // 相关接口
            const INSERT_API = "{{ route('lpadmin.website.home.store') }}";
            const WEBSITE_LIST_API = "{{ route('lpadmin.website.config.webSiteList') }}";

            // 加载配置分组

            //提交事件
            layui.use(["form", "popup"], function () {
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
