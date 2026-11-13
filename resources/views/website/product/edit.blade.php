<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'zh-CN' }}">
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
                        <label class="layui-form-label required">{{ __('admin.category') }}</label>
                        <div class="layui-input-block">
                            <select name="menu_id" id="menu_id" >
                                @foreach($menuList as $menu)
                                    <option value="{{ $menu['id'] }}">{{ $menu['menu_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label required">{{ __('admin.product_name') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="name" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.brand') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="brand" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.price') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="price" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.source') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="source" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.product_url') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="source_product_url" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.image') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="main_img" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.buy_platform') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="main_buy_platform" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.buy_link') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="main_buy_link" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">taoobuy</label>
                        <div class="layui-input-block">
                            <input type="text" name="tao_buy_platform" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.tao_link') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="tao_buy_link" value="" class="layui-input">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">{{ __('admin.submit') }}</button>
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
            const PRIMARY_KEY = "id";
            const SELECT_API = "{{ route('lpadmin.website.product.show', $product->id ?? ':id') }}";
            const UPDATE_API = "{{ route('lpadmin.website.product.update', $product->id ?? ':id') }}";



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

                        }
                    });
                }
            });

            //提交事件
            layui.use(["form", "popup"], function () {
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
                        type: "PUT",
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
