# mTab - 浏览器新标签页 (Hugging Face + TiDB 版本)

## 📋 项目说明

这是 mTab 浏览器新标签页应用的优化版本，专为 **Hugging Face Spaces** 部署设计，使用 **TiDB Cloud** 作为数据库。

### ✨ 主要改进

1. **🚀 Hugging Face 优化部署**
   - 优化 Dockerfile，避免构建卡住
   - 非阻塞式启动脚本，带超时保护
   - 自动读取 Hugging Face Secrets 配置

2. **🔒 TiDB Cloud 集成**
   - 支持 SSL 加密连接
   - 自动从 Secrets 读取数据库配置
   - 兼容 TiDB 雪花 ID 算法

3. **🐛 Bug 修复**
   - ✅ 修复管理员登录显示不全问题
   - ✅ 修复首页添加标签重复显示问题
   - ✅ 修复"关于我们"模块显示问题

4. **🎯 功能优化**
   - ✅ 保留免费核心功能：记事本、热搜、天气日历
   - ✅ 将"今日诗词"替换为"热搜"（支持百度、B站、微博、知乎等）
   - ✅ 将"木鱼"替换为"天气日历"模块
   - ✅ 移除所有付费模块
   - ✅ 移除备案信息

## 🚀 快速部署

### 1. 创建 Hugging Face Space

1. 访问 [Hugging Face](https://huggingface.co/)
2. 点击 "New Space"
3. 选择 **Docker** 作为 SDK
4. 设置 Space 名称（如：`mtab-browser`）
5. 选择可见性（Public/Private）

### 2. 配置 Secrets

在 Space 的 **Settings → Repository secrets** 中添加：

| 变量名 | 必填 | 说明 | 示例 |
|--------|------|------|------|
| `DB_HOST` | ✅ | TiDB 主机地址 | `gateway01.xxx.tidbcloud.com` |
| `DB_PORT` | ❌ | TiDB 端口（默认 4000） | `4000` |
| `DB_USER` | ✅ | TiDB 用户名 | `xxxxxxxx.root` |
| `DB_PASSWORD` | ✅ | TiDB 密码 | `your_password` |
| `DB_NAME` | ❌ | 数据库名（默认 mtab） | `mtab` |
| `ADMIN_USER` | ❌ | 管理员账号（默认 admin） | `admin` |
| `ADMIN_PASSWORD` | ❌ | 管理员密码 | `your_admin_pwd` |

**可选 SSL 证书：**
| `DB_SSL_CA_PEM` | ❌ | SSL 证书内容（PEM 格式） | `-----BEGIN CERTIFICATE-----...` |

### 3. 获取 TiDB 连接信息

1. 登录 [TiDB Cloud](https://tidbcloud.com/)
2. 创建免费集群（Serverless 免费层）
3. 点击 "Connect" 获取连接信息：
   - Host: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`
   - Port: `4000`
   - User: `xxxxxxxx.root`
   - Password: 你设置的密码

### 4. 上传代码

```bash
# 克隆你的 Hugging Face Space
git clone https://huggingface.co/spaces/your-username/mtab-browser
cd mtab-browser

# 复制项目文件
cp -r /path/to/mtmtabtest/* .

# 提交并推送
git add .
git commit -m "Initial commit - mTab with TiDB"
git push
```

### 5. 等待部署

Space 会自动构建并启动，首次部署约需 **3-5 分钟**。

访问地址：`https://your-username-mtab-browser.hf.space`

## 📦 功能列表

### ✅ 已包含功能

| 功能 | 说明 |
|------|------|
| 📝 记事本 | 快捷记录灵感和待办事项 |
| 🔥 热搜 | 聚合百度、B站、微博、知乎、抖音等热搜 |
| 🌤️ 天气日历 | 实时天气信息 + 农历日历 |
| 📅 日历功能 | 节假日、二十四节气 |
| 📋 待办事项 | 任务管理 |
| 🎨 壁纸系统 | 自定义背景壁纸 |
| 🔍 聚合搜索 | 多搜索引擎支持 |
| 📱 移动端适配 | 响应式设计 |
| 🔖 书签管理 | 跨设备同步书签 |

### ❌ 已移除功能

- 🐟 电子木鱼 → 已替换为天气日历
- 📜 每日诗词 → 已替换为热搜
- 💰 AI 助手 → 付费模块已移除
- 💰 纪念日 → 付费模块已移除
- 💰 倒计时 → 付费模块已移除
- 💰 图片转换 → 付费模块已移除
- 💰 金额转换 → 付费模块已移除
- 📋 备案信息 → 完全移除

## 🔧 技术架构

```
┌─────────────────────────────────────┐
│     Hugging Face Spaces             │
│  ┌───────────────────────────────┐  │
│  │   Docker Container            │  │
│  │                               │  │
│  │  Nginx (Port 7860)           │  │
│  │       ↓                       │  │
│  │  PHP-FPM 8.2                 │  │
│  │       ↓                       │  │
│  │  ThinkPHP 6.x                │  │
│  └───────────────────────────────┘  │
└─────────────────┬───────────────────┘
                  │ SSL
                  ↓
┌─────────────────────────────────────┐
│     TiDB Cloud                      │
│  - MySQL 兼容                       │
│  - 自动扩展                         │
│  - 免费层 5GB                      │
└─────────────────────────────────────┘
```

## 🛠️ 维护脚本

项目包含以下维护脚本，会在启动时自动运行：

### 1. fix_duplicate_tabs.php
清理重复的标签数据，修复首页标签重复显示问题。

### 2. init_cards.php
初始化卡片配置，确保：
- 启用：记事本、热搜、天气日历
- 禁用：诗词、木鱼、付费模块

### 3. env.php
自动安装脚本，负责：
- 创建数据库表结构
- 插入默认数据
- 创建管理员账号
- 同步 Secrets 配置

## 🐛 常见问题

### 1. 部署卡住怎么办？

**解决方案：**
- 检查 Secrets 是否正确配置
- 查看构建日志（Space → Logs）
- 确认 TiDB 集群已激活
- 尝试重新部署（Space → Settings → Factory Rebuild）

### 2. 数据库连接失败？

**检查清单：**
- ✅ TiDB 集群状态为 "Running"
- ✅ 用户名密码正确
- ✅ Host 地址完整（包含 `.tidbcloud.com`）
- ✅ 端口为 4000
- ✅ 网络连接正常（TiDB Cloud 需要公网访问）

### 3. 管理员登录显示不全？

**已修复！** 此问题已在最新版本中解决。如果仍有问题：
- 清除浏览器缓存
- 使用无痕模式测试
- 检查浏览器控制台是否有错误

### 4. 标签重复显示？

**已修复！** 启动脚本会自动运行去重脚本。如需手动修复：
```bash
php fix_duplicate_tabs.php
```

### 5. 如何修改管理员密码？

在 Hugging Face Space Secrets 中修改 `ADMIN_PASSWORD`，然后重启 Space。

### 6. 如何自定义"关于我们"？

在管理员后台 → 系统设置 → 关于我们 中编辑内容。

## 📊 性能优化

### Docker 优化
- 使用 `--no-install-recommends` 减少镜像体积
- 分层构建利用 Docker 缓存
- 非交互模式避免卡住

### 启动优化
- 安装脚本带超时保护（30秒）
- 后台运行修复脚本
- 延长进程检查间隔（60秒）

### 数据库优化
- 断线重连机制
- SSL 连接缓存
- 字段缓存禁用（云数据库兼容）

## 📄 许可证

MIT License

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📞 支持

如有问题，请：
1. 查看本文档的常见问题部分
2. 查看 Hugging Face Space 日志
3. 提交 Issue

---

**祝您使用愉快！** 🎉
