# mTab - 浏览器新标签页 (Hugging Face Spaces)

一个美观实用的浏览器新标签页/书签管理器，支持多种功能扩展。

## 🚀 一键部署到 Hugging Face Spaces

### 1. Fork 本项目到你的 GitHub 仓库

### 2. 在 Hugging Face Spaces 创建新 Space

访问 [Hugging Face Spaces](https://huggingface.co/new-space)，选择：
- **SDK**: Docker
- **Hardware**: 选择合适的硬件配置

### 3. 在 Hugging Face Spaces Secrets 中配置数据库

| 环境变量名 | 说明 | 示例 |
|-----------|------|------|
| `DB_HOST` | TiDB 主机地址 | `xxx.tidbcloud.com` |
| `DB_PORT` | TiDB 端口 | `4000` |
| `DB_USER` | TiDB 用户名 | `username` |
| `DB_PASSWORD` | TiDB 密码 | `password` |
| `DB_NAME` | 数据库名称 | `mtab` |
| `DB_SSL_CA_PEM` | SSL证书(可选) | 证书内容 |
| `ADMIN_USER` | 管理员账号(可选) | `admin` |
| `ADMIN_PASSWORD` | 管理员密码(可选) | `123456` |

### 4. 获取 TiDB 连接信息

1. 登录 [TiDB Cloud](https://tidbcloud.com/)
2. 创建一个 Serverless Tier 集群（免费）
3. 在连接信息中找到主机地址、端口、用户名和密码

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
