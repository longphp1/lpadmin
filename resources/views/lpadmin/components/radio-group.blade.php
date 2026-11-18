{{--
通用单选框组件
使用方法：
@include('lpadmin.components.radio-group', [
    'name' => 'status',
    'label' => '状态',
    'required' => true,
    'default' => '1',
    'help' => '选择管理员状态'
])
--}}

@php
    // 基础变量设置
    $name = $name ?? 'radio_field';
    $label = $label ?? '选项';
    $required = $required ?? false;
    $default = $default ?? null;
    $help = $help ?? null;
    $inline = $inline ?? true;
    $disabled = $disabled ?? false;
    $class = $class ?? '';

    // 处理预定义类型
    if (isset($type) && (!isset($options) || empty($options))) {
        switch ($type) {
            case 'status':
                $options = [
                    ['value' => '1', 'title' => __('admin.enabled')],
                    ['value' => '0', 'title' => __('admin.disabled')]
                ];
                $label = $label ?: __('admin.status');
                $required = $required ?: true;
                $default = $default ?: '1';
                break;

            case 'gender':
                $options = [
                    ['value' => '2', 'title' => __('admin.unspecified')],
                    ['value' => '1', 'title' => __('admin.male')],
                    ['value' => '0', 'title' => __('admin.female')]
                ];
                $label = $label ?: __('admin.gender');
                break;

            case 'show':
                $options = [
                    ['value' => '1', 'title' => __('admin.visible')],
                    ['value' => '0', 'title' => __('admin.hidden')]
                ];
                $label = $label ?: __('admin.visible');
                $default = $default ?: '1';
                break;

            case 'menu_type':
                $options = [
                    ['value' => '1', 'title' => __('admin.menu_type')],
                    ['value' => '2', 'title' => __('admin.button')]
                ];
                $label = $label ?: __('admin.type');
                $required = $required ?: true;
                $default = $default ?: '1';
                break;

            case 'yes_no':
                $options = [
                    ['value' => '1', 'title' => '是'],
                    ['value' => '0', 'title' => '否']
                ];
                $label = $label ?: '选项';
                $default = $default ?: '1';
                break;
        }
    }

    // 确保options是数组
    if (!isset($options) || !is_array($options)) {
        $options = [];
    }
@endphp

<div class="layui-form-item {{ $class }}">
    <label class="layui-form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
    <div class="layui-input-block">
        @if($inline)
            {{-- 内联显示 --}}
            @foreach($options as $index => $option)
                <input type="radio"
                       name="{{ $name }}"
                       value="{{ $option['value'] }}"
                       data-value="{{ $option['value'] }}"
                       title="{{ $option['title'] }}"
                       lay-filter="{{ $name }}"
                       {{ $disabled ? 'disabled' : '' }}
                       {{ ($default !== null && $default == $option['value']) ? 'checked' : '' }}
                       {{ ($default === null && $index === 0) ? 'checked' : '' }}>
            @endforeach
        @else
            {{-- 垂直显示 --}}
            @foreach($options as $index => $option)
                <div style="margin-bottom: 8px;">
                    <input type="radio"
                           name="{{ $name }}"
                           value="{{ $option['value'] }}"
                           data-value="{{ $option['value'] }}"
                           title="{{ $option['title'] }}"
                           lay-filter="{{ $name }}"
                           {{ $disabled ? 'disabled' : '' }}
                           {{ ($default !== null && $default == $option['value']) ? 'checked' : '' }}
                           {{ ($default === null && $index === 0) ? 'checked' : '' }}>
                </div>
            @endforeach
        @endif
        
        @if($help)
            <div class="layui-form-mid layui-word-aux">{{ $help }}</div>
        @endif
    </div>
</div>


