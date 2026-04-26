---
title: mTab - 精简版新标签页
emoji: 🔖
colorFrom: blue
colorTo: indigo
sdk: docker
app_port: 7860
pinned: false
license: mit
---

# mTab - 精简版浏览器新标签页

一个专为 Hugging Face Spaces 优化的美观实用浏览器新标签页/书签管理器。

## ✨ 功能特性

### 🎯 核心功能
- 📝 **记事本** - 快捷记录您的灵感和想法
- 🔥 **热搜** - 聚合百度、哔站、微博、知乎、头条等热搜（管理员可设置RSS源）
- 🌤️ **天气日历** - 实时天气信息和完整日历功能
- 📅 **日历/倒计时/纪念日** - 个性化事件管理
- 📋 **待办事项** - 快捷添加和管理待办任务
- 🎨 **壁纸系统** - 自定义首页背景
- 📱 **移动端适配** - 完美支持移动设备

### ❌ 已移除功能
- 🐟 **电子木鱼** - 已替换为天气日历功能
- 📜 **每日诗词** - 已替换为热搜功能  
- 💰 **付费模块** - 完全移除所有付费功能
- 📋 **备案信息** - 完全移除备案相关内容

## 🚀 Hugging Face Spaces 部署

### 快速部署（3步完成）

#### 1. Fork 本项目
```bash
# Fork 到你的 GitHub 仓库
git clone https://github.com/yourusername/mtab-hf.git
cd mtab-hf
```

#### 2. 创建 Hugging Face Space
访问 [Hugging Face Spaces](https://huggingface.co/new-space) 创建新空间：
- **Space name**: `mtab` (或你喜欢的名称)
- **SDK**: Docker
- **Hardware**: CPU basic (免费) 或更高配置
- **Visibility**: Public (推荐) 或 Private

#### 3. 配置环境变量
在 Space 的 **Settings → Secrets** 中添加以下环境变量：

| 变量名 | 说明 | 必填 | 示例 |
|--------|------|------|--------|
| `DB_HOST` | TiDB 主机地址 | ✅ | `xxx.tidbcloud.com` |
| `DB_PORT` | TiDB 端口 | ❌ | `4000` |
| `DB_USER` | 数据库用户名 | ✅ | `your_username` |
| `DB_PASSWORD` | 数据库密码 | ✅ | `your_password` |
| `DB_NAME` | 数据库名称 | ❌ | `mtab` |
| `DB_SSL_CA_PEM` | SSL 证书内容 | ❌ | `-----BEGIN CERTIFICATE-----...` |
| `ADMIN_USER` | 管理员账号 | ❌ | `admin` |
| `ADMIN_PASSWORD` | 管理员密码 | ❌ | `123456` |

### 📊 获取 TiDB Cloud 连接信息

1. 访问 [TiDB Cloud](https://tidbcloud.com/)
2. 注册账号并创建 **Serverless Tier** 集群（免费）
3. 在集群详情页找到：
   - **Host**: 主机地址 (如: `xxx.tidbcloud.com`)
   - **Port**: 端口 (默认: `4000`)
   - **User**: 用户名
   - **Password**: 密码

## 🔧 技术架构

### 后端技术栈
- **PHP 8.2** - 核心后端语言
- **ThinkPHP 6.x** - 企业级 PHP 框架
- **TiDB Cloud** - 分布式 SQL 数据库
- **Redis** - 缓存和会话存储

### 前端技术栈  
- **Vue.js 3** - 现代前端框架
- **Element UI** - 企业级 UI 组件库
- **Vite** - 快速构建工具

### 部署架构
```
Hugging Face Spaces
├── Docker Container
│   ├── Nginx (Web 服务器)
│   ├── PHP-FPM (应用服务器)
│   └── Redis (缓存服务)
├── TiDB Cloud (数据库)
│   ├── 自动 SSL 连接
│   ├── 雪花 ID 算法
│   └── 分布式存储
└── CDN (静态资源加速)
```

## 🛠️ 本地开发

### 环境要求
- PHP >= 8.2
- MySQL >= 8.0 或 TiDB Cloud
- Redis >= 6.0
- Composer
- Node.js >= 16 (前端开发)

### 快速启动
```bash
# 克隆项目
git clone https://github.com/yourusername/mtab-hf.git
cd mtab-hf

# 安装依赖
composer install --no-dev --optimize-autoloader

# 配置环境变量
cp .env.example .env
# 编辑 .env 文件配置数据库

# 启动开发服务器
php think run
```

### Docker 本地部署
```bash
# 构建镜像
docker build -f Dockerfile.huggingface -t mtab-hf .

# 运行容器
docker run -d -p 7860:7860 \
  -e DB_HOST=your-tidb-host \
  -e DB_USER=your-username \
  -e DB_PASSWORD=your-password \
  -e DB_NAME=mtab \
  mtab-hf
```

## 🔒 安全特性

### TiDB 兼容性
- ✅ **SSL 强制连接** - TiDB Cloud 安全要求
- ✅ **雪花 ID 算法** - 分布式环境严格递增
- ✅ **事务优化** - 分批次提交避免超限
- ✅ **字符集统一** - 全表 utf8mb4_general_ci
- ✅ **外键移除** - 应用层实现数据完整性

### 自动化特性
- 🔄 **自动安装** - 首次启动自动建表
- 🔄 **配置同步** - Secrets 更新自动同步
- 🔄 **证书续期** - SSL 证书自动更新
- 🔄 **管理员创建** - 自动创建管理员账号

## 📋 已修复问题

### ✅ 管理员显示问题
- 修复管理员账号登录后显示不全的问题
- 优化管理后台权限验证逻辑

### ✅ 标签重复问题  
- 修复首页"添加标签-在线添加"中标签重复三个的问题
- 优化标签数据获取和显示逻辑

### ✅ 关于我们模块
- 修复"关于我们"模块显示异常问题
- 完善页面布局和内容展示

## 🔧 配置说明

### 环境变量详解
```bash
# 数据库配置
DB_HOST=tidb.xxxx.clusters.tidbcloud.com  # TiDB 主机
DB_PORT=4000                              # 数据库端口  
DB_USER=your_username                       # 数据库用户
DB_PASSWORD=your_password                   # 数据库密码
DB_NAME=mtab                              # 数据库名

# SSL 配置 (可选)
DB_SSL_CA_PEM="-----BEGIN CERTIFICATE-----..."  # SSL 证书内容

# 管理员配置 (可选)
ADMIN_USER=admin                             # 管理员账号
ADMIN_PASSWORD=123456                        # 管理员密码

# 应用配置
APP_DEBUG=false                              # 调试模式
APP_ENV=production                         # 运行环境
```

## 📄 许可证

本项目基于 [MIT License](LICENSE.txt) 开源协议。

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

### 贡献指南
1. Fork 本项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建 Pull Request

## 📞 支持

如果你觉得这个项目有用，请：
- ⭐ 给个 Star
- 🍴 Fork 到你的仓库
- 🐛 报告 Bug 和建议
- 📝 分享给更多人

---

**mTab - 让你的新标签页更实用、更美观！** 🚀

## 📋 TiDB 兼容性说明

本项目已针对 TiDB 进行优化适配：

### ✅ 已实现的功能

1. **SSL 连接支持**
   - TiDB Cloud 强制要求 SSL 连接
   - 支持从 Secrets 读取 SSL 证书（`DB_SSL_CA_PEM`）
   - 如果不提供证书，自动使用系统证书池 `/etc/ssl/certs/ca-certificates.crt`
   - **证书自动续期**: 禁用服务器证书验证 (`MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false`)，支持证书自动更新

2. **雪花ID算法**
   - TiDB 的 AUTO_INCREMENT 在多并发下不是严格递增的
   - 使用雪花算法生成严格递增的分布式 ID
   - 所有数据表主键使用 `BIGINT` + 雪花算法

3. **事务优化**
   - TiDB 对事务大小有限制
   - SQL 执行采用分批次提交（每50条）

4. **字符集统一**
   - 所有表使用 `utf8mb4` 字符集
   - 统一使用 `utf8mb4_general_ci` 排序规则

5. **移除外键约束**
   - TiDB 语法上支持外键但默认不生效
   - 数据完整性逻辑在应用层实现

### 🔐 自动安装与持久化

当 HuggingFace Spaces 重启时，系统会自动：

1. **检测 Secrets 配置**: 读取 `DB_HOST`、`DB_PORT` 等环境变量
2. **更新配置文件**: 自动更新 `.env` 文件
3. **跳过安装向导**: 如果已安装，直接进入主页
4. **自动续期证书**: 如果 `DB_SSL_CA_PEM` 更新，自动更新本地证书

## 🐳 本地开发

```bash
# 构建 Docker 镜像
docker build -t mtab .

# 运行容器
docker run -d -p 7860:7860 \
  -e DB_HOST=your-host \
  -e DB_PORT=4000 \
  -e DB_USER=your-user \
  -e DB_PASSWORD=your-password \
  -e DB_NAME=mtab \
  mtab
```

## 📦 功能特性

- 🔖 书签管理 - 跨设备同步、文件夹分类
- 📝 笔记功能 - 快捷记录灵感
- 🌤️ 天气卡片 - 实时天气信息
- 🔍 聚合搜索 - 百度、B站、微博等热搜
- 📅 日历/倒计时/纪念日
- 🤖 AI 助手 - 智能对话
- 🎨 壁纸系统 - 自定义背景
- 📱 适配移动端

## 📄 许可证

MIT License
