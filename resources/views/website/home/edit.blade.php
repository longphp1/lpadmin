<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>编辑管理员</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form">

            <div class="mainBox">
                <div class="main-container mr-5">
                    <div class="layui-form-item">
                        <label class="layui-form-label required">网站选择</label>
                        <div class="layui-input-block">
                            <select name="site_id" id="site_id" >
                                @foreach($siteConfigList as $site)
                                    <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label required">网站首页头部</label>
                        <div class="layui-input-block">
                            <input type="text" name="top_head" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站首页标题</label>
                        <div class="layui-input-block">
                            <input type="text" name="large_head" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站首页底部</label>
                        <div class="layui-input-block">
                            <input type="text" name="top_head_bottom" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站首页内容</label>
                        <div class="layui-input-block">
                            <input type="text" name="header_content" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">完整首页二维码</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_qr_code" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站首页二维码标题</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_qr_title" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站中间图片</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_logo" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站中间内容</label>
                        <div class="layui-input-block">
                            <input type="text" name="middle_content" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">网站首页内容</label>
                        <div class="layui-input-block">
                            <input type="text" name="content" value=""  class="layui-input">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">
                        提交
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        重置
                    </button>
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
            const PRIMARY_KEY = "id";
            const SELECT_API = "{{ route('lpadmin.website.home.show', $admin->id ?? ':id') }}";
            const UPDATE_API = "{{ route('lpadmin.website.home.update', $admin->id ?? ':id') }}";



            // 获取数据库记录
            layui.use(["form", "util", "popup"], function () {
                let $ = layui.$;

                // 从URL获取ID
                let urlParams = new URLSearchParams(window.location.search);
                let adminId = urlParams.get('id') || '{{ $admin->id ?? "" }}';

                if (adminId) {
                    let apiUrl = SELECT_API.replace(':id', adminId);

                    $.ajax({
                        url: apiUrl,
                        dataType: "json",
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            let adminData = res.data || res;
                            // 给表单初始化数据
                            layui.each(adminData, function (key, value) {
                                let obj = $('*[name="'+key+'"]');
                                if (typeof obj[0] === "undefined" || !obj[0].nodeName) return;
                                if (obj[0].nodeName.toLowerCase() === "textarea") {
                                    obj.val(layui.util.escape(value));
                                } else {
                                    obj.attr("value", value);
                                }
                            });
                            // 重新渲染表单
                            layui.form.render();
                            // ajax产生错误
                            if (res.code && res.code !== 0) {
                                layui.popup.failure(res.message);
                            }

                        }
                    });
                }
            });

            //提交事件
            layui.use(["form", "popup"], function () {
                // 自定义验证规则
                layui.form.on("submit(save)", function (data) {
                    let urlParams = new URLSearchParams(window.location.search);
                    let adminId = urlParams.get('id') || '{{ $admin->id ?? "" }}';

                    data.field._token = '{{ csrf_token() }}';
                    data.field._method = 'PUT';

                    // 修复单选框数据
                    RadioHelper.fixFormData(data.field, ['status']);

                    console.log('提交的表单数据:', data.field);

                    let apiUrl = UPDATE_API.replace(':id', adminId);

                    layui.$.ajax({
                        url: apiUrl,
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
