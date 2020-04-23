# 小工具 基于 Laravel 框架

# 安装

1. 下载本项目,然后在项目根目录执行 `composer install`
2. 包安装完成后,复制.env.example 文件为.env
3. 执行 `php artisan key:generate`

## 工具
1. 微信本地dat文件(图片)解码 (不保证100%成功)
```php
php artisan dat2img:run
输入你的路径
输入你的输出路径(linux,mac注意读写权限)
```