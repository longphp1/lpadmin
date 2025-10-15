<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>新增网站</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form" action="">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">网站名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="name" id="name" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">网站域名</label>
                        <div class="layui-input-block">
                            <input type="text" name="domain" id="domain" value=""  class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">网站关键字</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_keyword" value=""  class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">网站描述</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_description" value=""  class="layui-input" placeholder="">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">网站标题</label>
                        <div class="layui-input-block">
                            <input type="text" name="title" value=""  class="layui-input" placeholder="">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">网站LOGO</label>
                        <div class="layui-input-block">
                            <div class="avatar-selector">
                                <div class="avatar-preview-container" style="margin-bottom: 10px;">
                                    <img id="avatar-preview" src="/static/images/default-avatar.png"
                                         style="width: 100px; height: 100px;  border: 2px solid #e6e6e6; cursor: pointer;"
                                         onclick="selectAdminAvatar()" title="点击选择头像">
                                </div>
                                <div class="avatar-actions">
                                    <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" onclick="selectAdminAvatar()">
                                        <i class="layui-icon layui-icon-picture"></i> 选择网站LOGO
                                    </button>
                                    <button type="button" class="pear-btn pear-btn-warm pear-btn-sm" onclick="clearAdminAvatar()">
                                        <i class="layui-icon layui-icon-delete"></i> 清除
                                    </button>
                                </div>
                                <input type="hidden" name="min_logo" id="avatar-input" value="/static/images/default-avatar.png">
                                <div class="layui-form-mid layui-word-aux">点击图片或按钮选择头像，支持jpg、png格式</div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit=""
                        lay-filter="save">
                        提交
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        重置
                    </button>
                </div>
            </div>

        </form>

        <style>
            .avatar-container {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .avatar-preview {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border: 3px solid #f0f0f0;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }

            .avatar-preview:hover {
                border-color: #1890ff;
                box-shadow: 0 4px 12px rgba(24,144,255,0.3);
            }

            .avatar-container button {
                margin-left: 10px;
            }
        </style>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>

            // 相关接口
            const INSERT_API = "{{ route('lpadmin.website.config.store') }}";

            // 选择管理员头像
            function selectAdminAvatar() {
                layui.layer.open({
                    type: 2,
                    title: '选择网站logo',
                    area: ['80%', '70%'],
                    content: '/lpadmin/upload/selector?type=image&mode=single&callback=setAdminAvatar'
                });
            }

            // 设置管理员头像
            function setAdminAvatar(selectedFiles) {
                if (selectedFiles.length > 0) {
                    let file = selectedFiles[0];
                    layui.$('#avatar-preview').attr('src', file.url);
                    layui.$('#avatar-input').val(file.url);
                    layui.layer.msg('网站logo设置成功', {icon: 1});
                }
            }

            // 清除管理员头像
            function clearAdminAvatar() {
                layui.$('#avatar-preview').attr('src', '/static/images/default-avatar.png');
                layui.$('#avatar-input').val('/static/images/default-avatar.png');
                layui.layer.msg('网站logo已清除', {icon: 1});
            }

            // 字段 角色 roles
            layui.use(["jquery", "xmSelect", "popup"], function() {
                layui.$.ajax({
                    url: "{{ route('lpadmin.website.config.index') }}?format=tree",
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        if (res.code && res.code !== 0) {
                            layui.popup.failure(res.message);
                        }
                    }
                });
            });

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
                            return layui.popup.success("操作成功", function () {
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
