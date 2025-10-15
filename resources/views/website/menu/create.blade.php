<!DOCTYPE html>
<html lang="zh-cn">
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
                        <label class="layui-form-label required">网站</label>
                        <div class="layui-input-block">
                            <select name="site_id" id="site_id" >
                                @foreach($siteConfigList as $site)
                                    <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label required">网站分类名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_name" value="" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">网站分类名称EN</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_name_en" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">网站分类URL</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_url" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">网站分类顺序</label>
                        <div class="layui-input-block">
                            <input type="text" name="range" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">网站分类类型</label>
                        <div class="layui-input-block">
                            <select name="type" id="type" >
                                @foreach($menuType as $menu)
                                <option value="{{ $menu['type'] }}">{{ $menu['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站分类关键字</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_keyword" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站分类描述</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_description" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站分类标题</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_title" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label required">状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" data-value="0" title="启用" lay-filter="status" checked>
                            <input type="radio" name="status" data-value="1" title="禁用" lay-filter="status">
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
