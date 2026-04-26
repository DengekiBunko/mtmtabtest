# mTab - Hugging Face Spaces 部署指南

## 📋 部署步骤

### 1. 创建 Hugging Face Space

1. 登录 [Hugging Face](https://huggingface.co/)
2. 点击 "New Space"
3. 选择 "Docker" 作为 Space SDK
4. 设置 Space 名称（例如：mtab-browser）
5. 选择 "Public" 或 "Private"

### 2. 配置 Secrets（连接 TiDB）

在 Space 的 "Settings" → "Repository secrets" 中添加以下环境变量：

| 变量名 | 说明 | 示例 |
|--------|------|------|
| `DB_HOST` | TiDB 主机地址 | `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` |
| `DB_PORT` | TiDB 端口（默认 4000） | `4000` |
| `DB_USER` | TiDB 用户名 | `xxxxxxxx.root` |
| `DB_PASSWORD` | TiDB 密码 | `your_password_here` |
| `DB_NAME` | 数据库名称（默认 mtab） | `mtab` |
| `ADMIN_USER` | 管理员账号（默认 admin） | `admin` |
| `ADMIN_PASSWORD` | 管理员密码（默认 123456） | `your_admin_password` |

**可选：** 如果需要 SSL 证书：
| `DB_SSL_CA_PEM` | SSL 证书内容（PEM 格式） | `-----BEGIN CERTIFICATE-----...` |

### 3. 上传代码

将项目文件推送到 Space 仓库：

```bash
git clone https://huggingface.co/spaces/your-username/mtab-browser
cd mtab-browser
# 复制项目文件到此目录
git add .
git commit -m "Initial commit"
git push
```

### 4. 等待部署

Space 会自动构建 Docker 镜像并启动服务。首次部署可能需要 3-5 分钟。

## 🔧 TiDB 数据库设置

### 1. 创建 TiDB Cloud 集群

1. 登录 [TiDB Cloud](https://tidbcloud.com/)
2. 创建免费集群
3. 获取连接信息：
   - Host: `gateway01.xxx.tidbcloud.com`
   - Port: `4000`
   - User: `xxxxxxxx.root`
   - Password: 你设置的密码

### 2. 创建数据库（可选）

项目会自动创建数据库和表，你也可以手动创建：

```sql
CREATE DATABASE mtab CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

## 📝 功能说明

### ✅ 已包含功能
- 📝 记事本 - 快捷记录灵感
- 🔥 热搜 - 聚合百度、B站、微博、知乎等热搜
- 🌤️ 天气日历 - 实时天气信息和日历功能
- 📅 日历/倒计时/纪念日
- 📋 待办事项
- 🎨 壁纸系统 - 自定义背景
- 📱 移动端适配

### ❌ 已移除功能
- 🐟 电子木鱼 - 已替换为天气日历
- 📜 每日诗词 - 已替换为热搜
- 💰 付费模块 - 完全移除
- 📋 备案信息 - 完全移除

## 🚀 访问应用

部署完成后，访问：
```
https://your-username-mtab-browser.hf.space
```

## 🐛 常见问题

### 1. 部署卡住
- 检查 Secrets 是否正确配置
- 查看构建日志是否有错误
- 尝试重新部署

### 2. 数据库连接失败
- 确认 TiDB 集群已激活
- 检查网络连接（TiDB Cloud 需要公网访问）
- 验证用户名和密码是否正确

### 3. 管理员登录显示不全
- 已修复此问题，请使用最新版本
- 清除浏览器缓存后重试

### 4. 标签重复显示
- 已修复此问题，数据库会自动去重
- 如果仍有问题，可在管理员后台重置标签

## 📄 许可证

MIT License

## 🤝 支持

如有问题，请提交 Issue 或联系开发者。
