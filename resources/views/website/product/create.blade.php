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
                        <label class="layui-form-label">{{ __('admin.source_url') }}</label>
                        <div class="layui-input-block" style="display:flex;gap:8px;">
                            <input type="text" id="fetch_url" placeholder="{{ __('admin.source_url_placeholder') }}" class="layui-input">
                            <button type="button" class="pear-btn pear-btn-primary pear-btn-md" id="fetch-product">{{ __('admin.fetch_product') }}</button>
                        </div>
                        <div class="layui-input-block" style="color:#888;padding-top:6px;">{{ __('admin.fetch_hint') }}</div>
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
                        <label class="layui-form-label">{{ __('admin.product_id') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="source_product_id" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.product_url') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="source_product_url" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.sub_images') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="img" value="" class="layui-input">
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
            const INSERT_API = "{{ route('lpadmin.website.product.store') }}";
            const FETCH_SOURCE_API = "{{ route('lpadmin.website.product.fetch_source') }}";


            //提交事件
            layui.use(["form", "popup", "layer"], function () {
                let $ = layui.$;

                $('#fetch-product').on('click', function () {
                    let url = $.trim($('#fetch_url').val());
                    if (!url) {
                        return layui.popup.warning("{{ __('admin.enter_url') }}");
                    }
                    let loading = layui.layer.load();
                    $.ajax({
                        url: FETCH_SOURCE_API,
                        type: "POST",
                        dataType: "json",
                        data: {
                            url: url,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            layui.layer.close(loading);
                            if (res.code !== 0) {
                                return layui.popup.failure(res.message);
                            }
                            let product = res.data || {};
                            $.each(product, function (key, value) {
                                $('input[name="' + key + '"]').val(value == null ? '' : value);
                            });
                            return layui.popup.success("{{ __('admin.fetched') }}");
                        },
                        error: function () {
                            layui.layer.close(loading);
                            return layui.popup.failure("{{ __('admin.fetch_failed') }}");
                        }
                    });
                });

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
