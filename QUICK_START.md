# 🚀 mTab 快速开始指南

## 5 分钟快速部署

### 步骤 1：创建 TiDB 数据库（2 分钟）
1. 访问 https://tidbcloud.com/
2. 点击 "Start Free"
3. 创建 Serverless 集群
4. 点击 "Connect" → 复制连接信息

### 步骤 2：创建 Hugging Face Space（1 分钟）
1. 访问 https://huggingface.co/new-space
2. 填写：
   - Space name: `mtab-browser`
   - SDK: **Docker** ⚠️ 重要
3. 点击 "Create Space"

### 步骤 3：配置 Secrets（1 分钟）
在 Space → Settings → Repository secrets 中添加：

```
DB_HOST=你的TiDB主机地址
DB_USER=你的TiDB用户名
DB_PASSWORD=你的TiDB密码
ADMIN_PASSWORD=设置管理员密码
```

### 步骤 4：上传代码（1 分钟）
```bash
# 克隆 Space
git clone https://huggingface.co/spaces/你的用户名/mtab-browser
cd mtab-browser

# 复制项目文件（所有文件）
cp -r /path/to/mtmtabtest/* .

# 提交
git add .
git commit -m "Initial commit"
git push
```

### 步骤 5：等待启动（3-5 分钟）
- 查看 Logs 标签
- 看到 "mTab 服务已启动" 即成功
- 访问你的 Space 链接

---

## 📋 必要的环境变量

| 变量 | 必填 | 示例 |
|------|------|------|
| DB_HOST | ✅ | gateway01.xxx.tidbcloud.com |
| DB_USER | ✅ | xxxxxxxx.root |
| DB_PASSWORD | ✅ | your_password |
| ADMIN_PASSWORD | ✅ | your_admin_pwd |
| DB_PORT | ❌ | 4000 |
| DB_NAME | ❌ | mtab |
| ADMIN_USER | ❌ | admin |

---

## ✅ 部署成功标志

看到以下日志表示成功：
```
========================================
mTab 服务已启动
访问地址: http://localhost:7860
管理员账号: admin
管理员密码: your_password
========================================
```

---

## 🎯 快速验证

1. **访问应用** - 打开 Space 链接
2. **测试搜索** - 输入关键词搜索
3. **管理员登录** - 点击设置 → 登录
4. **检查功能** - 记事本、热搜、天气日历

---

## 🐛 常见问题速查

### 构建失败
→ 检查 Secrets 是否有拼写错误

### 数据库连接失败
→ 确认 TiDB 集群状态为 Running

### 页面空白
→ 查看 Logs 中的错误信息

### 管理员无法登录
→ 检查 ADMIN_USER 和 ADMIN_PASSWORD

---

## 📚 详细文档

- **完整指南** → README_HF.md
- **检查清单** → DEPLOYMENT_CHECKLIST.md
- **修改记录** → CHANGES.md
- **项目总结** → PROJECT_SUMMARY.md

---

## 🆘 需要帮助？

1. 查看 README_HF.md 常见问题
2. 查看 Space Logs
3. 提交 Issue

---

**开始部署吧！** 🚀
