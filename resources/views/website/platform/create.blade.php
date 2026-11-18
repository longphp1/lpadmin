<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ __('admin.add_site') }}</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form" action="">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.site_name') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="site_name" required lay-verify="required" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_domain') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="domain" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_keywords') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_keyword" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_description') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_description" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_title') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_title" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.site_logo') }}</label>
                        <div class="layui-input-block">
                            <img id="avatar-preview" src="/static/images/default-avatar.png" style="width:64px;height:64px;border:1px solid #e6e6e6;cursor:pointer;" onclick="selectAdminAvatar()">
                            <div style="margin-top:8px;">
                                <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" onclick="selectAdminAvatar()">{{ __('admin.choose_image') }}</button>
                                <button type="button" class="pear-btn pear-btn-sm" onclick="clearAdminAvatar()">{{ __('admin.clear') }}</button>
                            </div>
                            <input type="hidden" name="min_logo" id="avatar-input" value="">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.platform_name') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="platform_name" id="platform_name" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.platform_url') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="platform_url" id="platform_url" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">Discord URL</label>
                        <div class="layui-input-block">
                            <input type="text" name="discord_url" id="discord_url" value=""  class="layui-input">
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
            const INSERT_API = "{{ route('lpadmin.website.platform.store') }}";

            function selectAdminAvatar() {
                layui.layer.open({
                    type: 2,
                    title: "{{ __('admin.choose_image') }}",
                    area: ['80%', '70%'],
                    content: '/lpadmin/upload/selector?type=image&mode=single&callback=setAdminAvatar'
                });
            }
            function setAdminAvatar(selectedFiles) {
                if (selectedFiles.length > 0) {
                    layui.$('#avatar-preview').attr('src', selectedFiles[0].url);
                    layui.$('#avatar-input').val(selectedFiles[0].url);
                }
            }
            function clearAdminAvatar() {
                layui.$('#avatar-preview').attr('src', '/static/images/default-avatar.png');
                layui.$('#avatar-input').val('');
            }


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
