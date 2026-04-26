# mTab 一键部署配置
# 将此文件保存为 app.py 在 Hugging Face Space 根目录

import os

# 打印启动信息
print("=" * 50)
print("mTab - 浏览器新标签页")
print("Hugging Face Spaces + TiDB Cloud 版本")
print("=" * 50)

# 检查必要的环境变量
required_vars = ['DB_HOST', 'DB_USER', 'DB_PASSWORD']
missing_vars = [var for var in required_vars if not os.getenv(var)]

if missing_vars:
    print(f"\n⚠️  警告：缺少以下必要的环境变量：")
    for var in missing_vars:
        print(f"   - {var}")
    print("\n请在 Space Settings → Repository secrets 中配置这些变量。")
    print("详细配置指南请查看 README_HF.md")
else:
    print("\n✅ 检测到必要的数据库配置")
    print(f"   数据库主机: {os.getenv('DB_HOST')}:{os.getenv('DB_PORT', '4000')}")
    print(f"   数据库名称: {os.getenv('DB_NAME', 'mtab')}")

print("\n📋 功能说明：")
print("   ✅ 记事本 - 快捷记录灵感")
print("   ✅ 热搜 - 聚合百度、B站、微博、知乎等")
print("   ✅ 天气日历 - 实时天气和农历日历")
print("   ❌ 已移除：诗词、木鱼、付费模块")

print("\n🚀 正在启动 Docker 容器...")
print("   首次启动需要 3-5 分钟，请耐心等待。")

# 这个文件只是占位符，实际启动由 Dockerfile 处理
# Hugging Face Spaces 会自动检测并运行 Docker 容器
