# 快速部署检查清单

## ✅ 部署前准备

- [ ] 已注册 Hugging Face 账号
- [ ] 已创建 TiDB Cloud 免费集群
- [ ] 已获取 TiDB 连接信息（Host, Port, User, Password）

## 📝 Hugging Face Space 配置

### 1. 创建 Space
- [ ] 访问 https://huggingface.co/new-space
- [ ] Space 名称：`mtab-browser`（或其他名称）
- [ ] SDK 选择：**Docker**
- [ ] 可见性：Public 或 Private

### 2. 配置 Secrets
在 Space → Settings → Repository secrets 中添加：

```
DB_HOST=gateway01.xxx.tidbcloud.com
DB_PORT=4000
DB_USER=xxxxxxxx.root
DB_PASSWORD=your_password_here
DB_NAME=mtab
ADMIN_USER=admin
ADMIN_PASSWORD=your_secure_password
```

- [ ] DB_HOST 已填写
- [ ] DB_USER 已填写
- [ ] DB_PASSWORD 已填写
- [ ] ADMIN_PASSWORD 已修改为安全密码

### 3. 上传代码
```bash
git clone https://huggingface.co/spaces/your-username/mtab-browser
cd mtab-browser
# 复制所有项目文件到此目录
git add .
git commit -m "Deploy mTab with TiDB"
git push
```

- [ ] 代码已推送

## 🚀 部署监控

### 1. 查看构建日志
- [ ] 访问 Space 页面
- [ ] 点击 "Logs" 标签
- [ ] 等待构建完成（约 3-5 分钟）

### 2. 检查启动日志
应该看到类似输出：
```
========================================
mTab - 浏览器新标签页启动中...
========================================
检测到数据库配置，开始自动安装...
数据库主机: gateway01.xxx.tidbcloud.com:4000
数据库名称: mtab
数据库连接成功
...
管理员账号创建完毕: admin / your_password
========================================
mTab 服务已启动
访问地址: http://localhost:7860
========================================
```

- [ ] 数据库连接成功
- [ ] 管理员账号创建成功
- [ ] 服务正常启动

## ✨ 部署后验证

### 1. 访问应用
- [ ] 打开 `https://your-username-mtab-browser.hf.space`
- [ ] 页面正常加载

### 2. 测试功能
- [ ] 搜索框正常工作
- [ ] 记事本功能正常
- [ ] 热搜功能正常（百度、微博、知乎等）
- [ ] 天气日历正常显示

### 3. 管理员登录
- [ ] 点击设置图标
- [ ] 使用 admin / your_password 登录
- [ ] 管理员面板显示完整
- [ ] 没有显示不全的问题

### 4. 检查标签
- [ ] 首页标签无重复
- [ ] 添加标签功能正常
- [ ] 标签排序正常

### 5. 验证卡片
- [ ] 记事本卡片存在
- [ ] 热搜卡片存在
- [ ] 天气日历卡片存在
- [ ] 无诗词卡片
- [ ] 无木鱼卡片
- [ ] 无付费模块卡片

## 🐛 问题排查

### 构建失败
1. 检查 Secrets 是否有拼写错误
2. 查看 Logs 中的错误信息
3. 确认 Dockerfile 存在且正确

### 数据库连接失败
1. 确认 TiDB 集群状态为 Running
2. 检查 Host/Port/User/Password 是否正确
3. 测试本地连接：`mysql -h HOST -P 4000 -u USER -p`

### 页面空白
1. 查看浏览器控制台（F12）
2. 检查 Space Logs 是否有 PHP 错误
3. 尝试刷新或清除缓存

### 管理员登录问题
1. 确认 ADMIN_USER 和 ADMIN_PASSWORD 已设置
2. 查看启动日志中的管理员账号信息
3. 清除浏览器缓存后重试

## 📊 性能检查

- [ ] 页面加载时间 < 3 秒
- [ ] API 响应时间 < 1 秒
- [ ] 无内存溢出错误
- [ ] 数据库连接稳定

## 🎉 完成！

如果所有检查项都通过，恭喜！你的 mTab 已成功部署到 Hugging Face Spaces！

**访问地址：** `https://your-username-mtab-browser.hf.space`

---

**提示：** 建议将此检查清单保存，以便后续部署时参考。
