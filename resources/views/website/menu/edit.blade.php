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
                        <label class="layui-form-label required">分类名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_name" value="" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">分类名称EN</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_name_en" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">分类URL</label>
                        <div class="layui-input-block">
                            <input type="text" name="menu_url" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">分类顺序</label>
                        <div class="layui-input-block">
                            <input type="text" name="range" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">分类类型</label>
                        <div class="layui-input-block">
                            <select name="type" id="type" >
                                @foreach($menuType as $menu)
                                    <option value="{{ $menu['type'] }}">{{ $menu['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">分类关键字</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_keyword" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">分类描述</label>
                        <div class="layui-input-block">
                            <input type="text" name="meta_description" value=""  class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label ">分类标题</label>
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
            .avatar-container button {
                margin-left: 10px;
            }
        </style>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>

            // 相关接口
            const PRIMARY_KEY = "id";
            const SELECT_API = "{{ route('lpadmin.website.menu.show', $admin->id ?? ':id') }}";
            const UPDATE_API = "{{ route('lpadmin.website.menu.update', $admin->id ?? ':id') }}";


            // 获取数据库记录
            layui.use(["form", "util", "popup"], function () {
                let $ = layui.$;

                // 初始化单选框
                if (window.RadioHelper) {
                    RadioHelper.init('status');
                }

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

                            // 设置状态单选框（使用简化的RadioHelper）
                            RadioHelper.setValue('status', adminData.status);

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


                // 监听状态变化（使用简化的RadioHelper）
                RadioHelper.onChange('status', function(value, element, data) {
                    console.log('状态改变为:', value);
                });

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
