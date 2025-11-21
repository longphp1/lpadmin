@echo off

git pull

:: 更新代码
php artisan generate:website_new

:: 更新weidian代码
php artisan generate:weidian_site

:: 推送代码
php artisan push:website

:: 推送weidian代码
php artisan push:weidian

:: 等待用户按键退出
pause
