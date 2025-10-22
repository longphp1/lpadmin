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
                        <label class="layui-form-label required">商品分类</label>
                        <div class="layui-input-block">
                            <select name="menu_id" id="menu_id" >
                                @foreach($menuList as $menu)
                                    <option value="{{ $menu['id'] }}">{{ $menu['menu_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">商品文件</label>
                        <div class="layui-input-block">
                            <input type="file" name="file"  id="upload-btn"  lay-options="{accept: 'file'}" class="layui-input">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="button" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" id="upload-all"
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
            const UPLOAD_API = "{{ route('lpadmin.website.product.import') }}";


            //提交事件
            layui.use(["form", "popup","upload"], function () {

                // 表单提交处理
                var form = layui.form;
                var upload = layui.upload;
                let $ = layui.jquery;
                let csrfToken = '{{ csrf_token() }}';
                // 初始化文件上传实例

                let uploadListIns = upload.render({
                    elem: '#upload-btn',
                    url: UPLOAD_API,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    accept: 'file',
                    auto: false,
                    bindAction: '#upload-all',
                    size: 10240, // 10MB限制
                    data: function(){
                        return {
                            'site_id': $('#site_id').val(), // 使用ID选择器确保获取正确值
                            'menu_id': $('#menu_id').val()
                        };
                    },
                    done: function(res){
                        // 上传完成处理
                        if(res.code === 0){
                            layui.popup.success("导入成功", function(){
                                parent.refreshTable();
                                parent.layer.close(parent.layer.getFrameIndex(window.name));
                            });
                        } else {
                            layui.popup.failure(res.message || "上传失败");
                        }
                    }
                });
            });

        </script>

    </body>
</html>
