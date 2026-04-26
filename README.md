# mTab - 浏览器新标签页 (Hugging Face Spaces)

一个美观实用的浏览器新标签页/书签管理器，支持多种功能扩展。

## 🚀 一键部署到 Hugging Face Spaces

### 1. Fork 本项目到你的 GitHub 仓库

### 2. 在 Hugging Face Spaces 创建新 Space

访问 [Hugging Face Spaces](https://huggingface.co/new-space)，选择：
- **SDK**: Docker
- **Hardware**: 选择合适的硬件配置

### 3. 连接 TiDB Cloud 数据库

在 Hugging Face Spaces 的 Settings 中添加以下环境变量：

| 环境变量名 | 说明 | 示例 |
|-----------|------|------|
| `TIDB_HOST` | TiDB 主机地址 | `xxx.tidbcloud.com` |
| `TIDB_PORT` | TiDB 端口 | `4000` |
| `TIDB_USER` | TiDB 用户名 | `username` |
| `TIDB_PASSWORD` | TiDB 密码 | `password` |
| `TIDB_DATABASE` | 数据库名称 | `mtab` |
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
   - 配置文件中已启用 `MYSQL_ATTR_SSL_CA`
   - 使用系统证书池 `/etc/ssl/certs/ca-certificates.crt`

2. **雪花ID算法**
   - TiDB 的 AUTO_INCREMENT 在多并发下不是严格递增的
   - 使用雪花算法生成严格递增的分布式 ID
   - 所有数据表主键使用 `BIGINT` + 雪花算法

3. **事务优化**
   - TiDB 对事务大小有限制
   - SQL 执行采用分批次提交（每50条提交一次）

4. **字符集统一**
   - 所有表使用 `utf8mb4` 字符集
   - 统一使用 `utf8mb4_general_ci` 排序规则

5. **移除外键约束**
   - TiDB 语法上支持外键但默认不生效
   - 数据完整性逻辑在应用层实现

### 🔧 配置参数说明

```yaml
# .env 配置示例
DATABASE:
  TYPE: mysql
  HOSTNAME: your-tidb-host.tidbcloud.com
  DATABASE: mtab
  USERNAME: your-username
  PASSWORD: your-password
  HOSTPORT: 4000
  CHARSET: utf8mb4
```

## 🐳 本地开发

```bash
# 构建 Docker 镜像
docker build -t mtab .

# 运行容器
docker run -d -p 7860:7860 \
  -e TIDB_HOST=your-host \
  -e TIDB_PORT=4000 \
  -e TIDB_USER=your-user \
  -e TIDB_PASSWORD=your-password \
  -e TIDB_DATABASE=mtab \
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
