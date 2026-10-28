# SleekDouban

为 Sleek 主题提供豆瓣电影与书籍页面。

## 功能

- 启用后自动创建「豆瓣」页面，并自动出现在顶部导航
- 页面分「电影」「书籍」两个标签
- 配置豆瓣用户 ID 后，显示该用户最近的书影音收藏
- 未配置 ID 时，显示豆瓣热门电影和豆瓣新书速递
- 豆瓣图片通过插件内置代理加载，避免图片 CDN 拒绝外站 Referer
- 数据与图片均本地缓存

## 安装

1. 将 `SleekDouban` 目录上传到 `usr/plugins/`
2. 在 Typecho 后台启用插件
3. 插件会自动创建 `/douban.html` 页面
4. 主题需包含 `page-douban.php` 模板（Sleek 2.9.9+ 已包含）

## 配置

后台路径：**插件 → SleekDouban → 设置**。

也可以直接访问：`/admin/options-plugin.php?config=SleekDouban`。

在插件设置中填写：

- **豆瓣用户 ID**：可填 `xxxx`，也可直接粘贴 `https://www.douban.com/people/xxxx/` 完整地址
- 绑定用户后若没有公开的书影音记录，页面会显示空状态，不再用热门内容冒充个人数据
- **每类显示数量**：默认 12
- **缓存分钟数**：默认 360

留空豆瓣 ID 时展示豆瓣热门内容，不会显示他人的个人收藏。

## 数据来源

- 用户收藏：`https://www.douban.com/feed/people/{id}/interests`
- 热门电影：豆瓣电影热门接口
- 新书：豆瓣新书速递

## 环境

- Typecho 1.2+
- PHP 7.4+
- curl、DOM、SimpleXML 扩展

## 版本

1.0.2
