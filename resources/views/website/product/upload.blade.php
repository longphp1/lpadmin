<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>商品导入</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form" action="" lay-filter="uploadForm">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">网站</label>
                        <div class="layui-input-block">
                            <select name="site_id" id="site_id">
                                @foreach($siteConfigList as $site)
                                    <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">商品分类</label>
                        <div class="layui-input-block">
                            <select name="menu_id" id="menu_id">
                                @foreach($menuList as $menu)
                                    <option value="{{ $menu['id'] }}">{{ $menu['menu_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">文件说明</label>
                        <div class="layui-input-block" style="color:#666;line-height:1.8;padding-top:8px;">
                            保留原导入格式：分类、品牌、商品名称、价格、主图、子图、主购买链接、taoobuy链接。<br>
                            另支持只有一列的文件，表头为「来源商品URL(Source Product URL)」，单元格填写 1688、淘宝或微店商品链接。该格式按上方选择的网站和商品分类导入，并调用接口补全商品信息。
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">商品文件</label>
                        <div class="layui-input-block">
                            <input type="file" name="file" id="upload-btn" class="layui-input">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="button" class="pear-btn pear-btn-primary pear-btn-md" id="upload-all">
                        提交
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        重置
                    </button>
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
                        layui.popup.failure('请先选择商品文件');
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
                                layui.popup.success(res.message || "导入成功", function () {
                                    parent.refreshTable();
                                    parent.layer.close(parent.layer.getFrameIndex(window.name));
                                });
                            } else {
                                layui.popup.failure(res.message || "上传失败");
                            }
                        },
                        error: function (xhr, status, err) {
                            console.error('upload error:', err);
                            layui.popup.failure('请求失败：' + err);
                        }
                    });
                });
            });
        </script>

    </body>
</html>
