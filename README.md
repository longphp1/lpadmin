# LPadmin - Laravel后台管理系统

## 🚀 简介

LPadmin是基于Laravel框架开发的现代化后台管理系统，完全复刻webmanadmin的功能特性，采用PearAdminLayui前端框架，提供完整的RBAC权限管理、用户管理、系统配置等功能。

## ✨ 特性

- 🚀 **高性能**: 基于Laravel 10+，性能优异，响应迅速
- 🎨 **美观界面**: PearAdminLayui UI，界面美观，用户体验佳
- 🔐 **权限系统**: 完整的RBAC权限系统，支持角色、权限精细化管理
- 📱 **响应式设计**: 支持PC、平板、手机等多端访问
- 🔧 **高度可配置**: 支持动态路由配置，系统参数灵活调整
- 📊 **数据统计**: 丰富的数据统计和图表展示功能
- 🛡️ **安全可靠**: 多层安全防护，操作日志完整记录
- 🔌 **易于扩展**: 模块化设计，支持插件式开发

## 📋 功能模块

### 核心功能
- **仪表盘**: 系统概览、数据统计、快捷操作
- **权限管理**: 管理员管理、角色管理、菜单权限管理
- **用户管理**: 前台用户管理、用户状态控制
- **系统管理**: 系统配置、字典管理、操作日志
- **文件管理**: 文件上传、附件管理、存储配置

### 开发工具
- **数据库管理**: 数据表查看、结构分析
- **表单构建**: 可视化表单设计器
- **代码生成**: 自动生成CRUD代码
- **组件开发**: 示例组件：系统日志
- **API文档**: 在线API文档查看

## 🛠️ 技术栈

- **后端框架**: Laravel 10+
- **前端框架**: PearAdminLayui
- **数据库**: MySQL 8.0+
- **缓存**: Redis (可选)
- **队列**: Laravel Queue
- **搜索**: Laravel Scout (可选)


## 🚀 快速开始

### 环境要求
- PHP >= 8.1
- Laravel >= 10.0
- MySQL >= 8.0
- Composer
- Node.js & NPM

### 安装步骤

1. **克隆项目**
```bash
git clone git@github.com:longphp1/lpadmin.git
cd lpadmin
```

2. **环境配置**
```bash
cp .env.example .env
php artisan key:generate
```

3. **数据库配置**
```bash
编辑 .env 文件配置数据库
php artisan migrate --seed
```

4. **设置运行目录**

设置 public 为运行目录


5. **设置伪静态**
```bash
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

访问 `http://localhost:8000/lpadmin` 即可进入后台管理系统，默认账号密码：`admin/123456`。

## 📖 版本信息

- **当前版本**: v1.0.0
- **Laravel版本**: 10.x
- **PHP版本**: 8.1+
- **发布日期**: 2024-01-01

## 🤝 贡献指南

欢迎提交Issue和Pull Request来帮助改进LPadmin。

### 代码规范
- 遵循PSR-12编码规范
- 使用Laravel最佳实践
- 编写单元测试
- 更新相关文档

## 📄 许可证

本项目采用 [MIT License] 许可证。

## 🙏 致谢

感谢以下开源项目的支持：
- [Laravel](https://laravel.com/) - 优雅的PHP框架
- [PearAdminLayui](https://github.com/pearadmin/pear-admin-layui) - 优秀的后台UI框架
- [Layui](https://layui.dev/) - 经典的前端UI框架

## 📞 联系我们

- 项目主页: https://github.com/longphp1/lpadmin
- 问题反馈: https://github.com/longphp1/lpadmin/issues
- 邮箱: 2209442773@qq.com
---

<div align="center">
  <p>Made with ❤️ by LPadmin Team</p>
  <p>Copyright © 2026 LPadmin. All rights reserved.</p>
</div>
