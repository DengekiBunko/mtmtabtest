-- =====================================================
-- mTab TiDB兼容数据库表结构
-- 适配 TiDB Cloud:
-- 1. 强制SSL连接
-- 2. 使用雪花ID替代自增ID（严格递增）
-- 3. 移除外键约束（TiDB默认不生效）
-- 4. 批量插入分批次提交
-- 5. 统一使用 utf8mb4_general_ci
-- =====================================================

-- 创建Card数据表 (使用雪花ID替代自增ID)
CREATE TABLE IF NOT EXISTS card (
    id BIGINT NOT NULL COMMENT '卡片ID-雪花算法生成',
    name VARCHAR(200) DEFAULT NULL COMMENT '卡片名称',
    name_en VARCHAR(200) DEFAULT NULL COMMENT '英文标识',
    status INT DEFAULT 0 COMMENT '状态',
    version INT DEFAULT 0 COMMENT '版本号',
    tips VARCHAR(255) DEFAULT NULL COMMENT '说明',
    create_time DATETIME DEFAULT NULL COMMENT '添加时间',
    src TEXT DEFAULT NULL COMMENT 'logo图标',
    url VARCHAR(255) DEFAULT NULL COMMENT '卡片地址',
    `window` VARCHAR(255) DEFAULT NULL COMMENT '窗口地址',
    update_time DATETIME DEFAULT NULL COMMENT '更新时间',
    install_num INT DEFAULT 0 COMMENT '安装数量',
    setting VARCHAR(200) DEFAULT NULL COMMENT '设置页面URL',
    dict_option LONGTEXT DEFAULT NULL COMMENT '配置参数',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='卡片数据表';

CREATE UNIQUE INDEX card_name_en_unique ON card (name_en);
CREATE INDEX card_name_en_index ON card (name_en);

-- 创建config数据表
CREATE TABLE IF NOT EXISTS config (
    id BIGINT NOT NULL COMMENT '配置ID-雪花算法生成',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    config LONGTEXT DEFAULT NULL COMMENT '用户配置JSON',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户配置数据表';

CREATE INDEX config_user_id_index ON config (user_id);

-- 创建file数据表
CREATE TABLE IF NOT EXISTS file (
    id BIGINT NOT NULL COMMENT '文件ID-雪花算法生成',
    path VARCHAR(255) DEFAULT NULL COMMENT '文件路径',
    user_id INT DEFAULT NULL COMMENT '上传用户ID',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    size DOUBLE DEFAULT 0 COMMENT '文件尺寸',
    mime_type VARCHAR(100) DEFAULT NULL COMMENT '文件MIME类型',
    hash VARCHAR(100) DEFAULT NULL COMMENT '文件哈希',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='文件存储表';

CREATE INDEX file_user_id_index ON file (user_id);
CREATE INDEX file_hash_index ON file (hash);

-- 创建history数据表
CREATE TABLE IF NOT EXISTS link_history (
    id BIGINT NOT NULL COMMENT '历史ID-雪花算法生成',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    link LONGTEXT DEFAULT NULL COMMENT '书签数据JSON',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='书签历史记录表';

CREATE INDEX history_user_id_index ON link_history (user_id);

-- 创建link数据表 (用户书签主表)
CREATE TABLE IF NOT EXISTS `link` (
    id BIGINT NOT NULL COMMENT 'ID-雪花算法生成',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    link LONGTEXT DEFAULT NULL COMMENT '书签数据JSON',
    update_time DATETIME DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户书签表';

CREATE UNIQUE INDEX link_user_id_unique ON `link` (user_id);
CREATE INDEX link_user_id_index ON `link` (user_id);

-- 创建link_folder数据表
CREATE TABLE IF NOT EXISTS link_folder (
    id BIGINT NOT NULL COMMENT '文件夹ID-雪花算法生成',
    name VARCHAR(50) DEFAULT NULL COMMENT '分类名称',
    sort INT DEFAULT 0 COMMENT '排序',
    group_ids VARCHAR(200) DEFAULT '0' COMMENT '可见用户分组',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='书签分类表';

CREATE INDEX link_folder_sort_index ON link_folder (sort);

-- 创建linkstore数据表
CREATE TABLE IF NOT EXISTS linkstore (
    id BIGINT NOT NULL COMMENT 'ID-雪花算法生成',
    name VARCHAR(255) DEFAULT NULL COMMENT '名称',
    src VARCHAR(255) DEFAULT NULL COMMENT '图标',
    url TEXT DEFAULT NULL COMMENT 'URL地址',
    type VARCHAR(20) DEFAULT 'icon' COMMENT '类型',
    size VARCHAR(20) DEFAULT '1x1' COMMENT '尺寸',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    hot BIGINT DEFAULT 0 COMMENT '热度',
    area VARCHAR(20) DEFAULT '' COMMENT '专区',
    tips VARCHAR(255) DEFAULT NULL COMMENT '介绍',
    domain VARCHAR(255) DEFAULT NULL COMMENT '域名',
    app INT DEFAULT 0 COMMENT '是否APP',
    install_num INT DEFAULT 0 COMMENT '安装量',
    bgColor VARCHAR(30) DEFAULT NULL COMMENT '背景颜色',
    vip INT DEFAULT 0 COMMENT '是否会员可见',
    custom TEXT DEFAULT NULL COMMENT '自定义配置',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    status INT DEFAULT 1 COMMENT '状态 1=展示 0=待审核',
    group_ids VARCHAR(200) DEFAULT '0' COMMENT '可见用户分组',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='书签商店表';

CREATE INDEX linkstore_status_index ON linkstore (status);
CREATE INDEX linkstore_hot_index ON linkstore (hot DESC);
CREATE INDEX linkstore_user_id_index ON linkstore (user_id);

-- 创建note数据表
CREATE TABLE IF NOT EXISTS note (
    id BIGINT NOT NULL COMMENT '笔记ID-雪花算法生成',
    user_id BIGINT DEFAULT NULL COMMENT '用户ID',
    title VARCHAR(50) DEFAULT NULL COMMENT '标题',
    text LONGTEXT DEFAULT NULL COMMENT '内容',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    update_time DATETIME DEFAULT NULL COMMENT '更新时间',
    weight INT DEFAULT 0 COMMENT '优先级',
    sort INT DEFAULT 0 COMMENT '排序',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='笔记表';

CREATE INDEX note_user_id_index ON note (user_id);
CREATE INDEX note_sort_index ON note (sort DESC);

-- 创建search_engine数据表
CREATE TABLE IF NOT EXISTS search_engine (
    id BIGINT NOT NULL COMMENT '搜索引擎ID-雪花算法生成',
    name VARCHAR(50) DEFAULT NULL COMMENT '名称',
    icon VARCHAR(255) DEFAULT NULL COMMENT '图标128x128',
    url VARCHAR(255) DEFAULT NULL COMMENT '跳转URL',
    sort INT DEFAULT 0 COMMENT '排序',
    create_time DATETIME DEFAULT NULL COMMENT '添加时间',
    status INT DEFAULT 0 COMMENT '状态 0=关闭 1=启用',
    tips VARCHAR(250) DEFAULT NULL COMMENT '搜索引擎介绍',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='搜索引擎表';

CREATE INDEX search_engine_status_index ON search_engine (status);
CREATE INDEX search_engine_sort_index ON search_engine (sort);

-- 创建setting表
CREATE TABLE IF NOT EXISTS setting (
    id BIGINT NOT NULL COMMENT '设置ID-雪花算法生成',
    `keys` VARCHAR(200) NOT NULL COMMENT '配置键',
    value LONGTEXT DEFAULT NULL COMMENT '配置值',
    PRIMARY KEY (id),
    UNIQUE KEY setting_keys_unique (`keys`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='系统配置表';

-- 创建tabbar数据表
CREATE TABLE IF NOT EXISTS tabbar (
    id BIGINT NOT NULL COMMENT 'ID-雪花算法生成',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    tabs LONGTEXT DEFAULT NULL COMMENT '页脚数据JSON',
    update_time DATETIME DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户页脚信息表';

CREATE UNIQUE INDEX tabbar_user_id_unique ON tabbar (user_id);
CREATE INDEX tabbar_user_id_index ON tabbar (user_id);

-- 创建token表
CREATE TABLE IF NOT EXISTS token (
    id BIGINT NOT NULL COMMENT 'TokenID-雪花算法生成',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    token VARCHAR(64) DEFAULT NULL COMMENT '登录Token',
    create_time INT DEFAULT NULL COMMENT '创建时间戳',
    ip VARCHAR(100) DEFAULT NULL COMMENT '登录IP',
    user_agent VARCHAR(250) DEFAULT NULL COMMENT '浏览器UA',
    access_token VARCHAR(200) DEFAULT NULL COMMENT '第三方Token',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='登录令牌表';

CREATE INDEX token_user_id_index ON token (user_id);
CREATE INDEX token_token_index ON token (token);

-- 创建user表
CREATE TABLE IF NOT EXISTS user (
    id BIGINT NOT NULL COMMENT '用户ID-雪花算法生成',
    avatar VARCHAR(255) DEFAULT NULL COMMENT '头像',
    mail VARCHAR(50) DEFAULT NULL COMMENT '邮箱',
    password TEXT DEFAULT NULL COMMENT '密码MD5',
    create_time DATETIME DEFAULT NULL COMMENT '注册时间',
    login_ip VARCHAR(100) DEFAULT NULL COMMENT '登录IP',
    register_ip VARCHAR(100) DEFAULT NULL COMMENT '注册IP',
    manager INT DEFAULT 0 COMMENT '是否管理员',
    login_fail_count INT DEFAULT 0 COMMENT '登录失败次数',
    login_time DATETIME DEFAULT NULL COMMENT '最后登录时间',
    qq_open_id VARCHAR(200) DEFAULT NULL COMMENT 'QQ开放平台ID',
    nickname VARCHAR(200) DEFAULT NULL COMMENT '昵称',
    status INT DEFAULT 0 COMMENT '状态 0正常 1冻结',
    active DATE DEFAULT NULL COMMENT '今日是否活跃',
    group_id BIGINT DEFAULT 0 COMMENT '用户分组ID',
    wx_open_id VARCHAR(200) DEFAULT NULL COMMENT '微信开放平台ID',
    wx_unionid VARCHAR(200) DEFAULT NULL COMMENT '微信UnionID',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户表';

CREATE UNIQUE INDEX user_qq_open_id_unique ON user (qq_open_id);
CREATE UNIQUE INDEX user_mail_unique ON user (mail);
CREATE INDEX user_wx_open_id_index ON user (wx_open_id);
CREATE INDEX user_wx_unionid_index ON user (wx_unionid);
CREATE INDEX user_status_index ON user (status);

-- 创建user_search_engine表
CREATE TABLE IF NOT EXISTS user_search_engine (
    id BIGINT NOT NULL COMMENT 'ID-雪花算法生成',
    user_id INT NOT NULL COMMENT '用户ID',
    list LONGTEXT DEFAULT NULL COMMENT '搜索引擎列表JSON',
    PRIMARY KEY (id),
    UNIQUE KEY user_search_engine_user_id_unique (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户搜索引擎配置表';

-- 创建wallpaper表
CREATE TABLE IF NOT EXISTS wallpaper (
    id BIGINT NOT NULL COMMENT '壁纸ID-雪花算法生成',
    type INT DEFAULT NULL COMMENT '1=文件夹 0=assets',
    folder INT DEFAULT NULL COMMENT '文件夹ID',
    mime INT DEFAULT 0 COMMENT '0=images 1=video',
    url TEXT DEFAULT NULL COMMENT '图片地址',
    cover TEXT DEFAULT NULL COMMENT '封面',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    name VARCHAR(200) DEFAULT NULL COMMENT '标题',
    sort INT DEFAULT 999 COMMENT '排序',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='壁纸表';

CREATE INDEX wallpaper_type_index ON wallpaper (type);
CREATE INDEX wallpaper_sort_index ON wallpaper (sort);

-- 创建user_group用户分组表
CREATE TABLE IF NOT EXISTS user_group (
    id BIGINT NOT NULL COMMENT '分组ID-雪花算法生成',
    name VARCHAR(50) NOT NULL COMMENT '分组名称',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    sort INT DEFAULT 0 COMMENT '排序',
    default_user_group INT DEFAULT 0 COMMENT '是否默认注册分组',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户分组表';

CREATE INDEX user_group_sort_index ON user_group (sort DESC);
CREATE INDEX user_group_default_index ON user_group (default_user_group);

-- =====================================================
-- 插件相关数据表
-- =====================================================

-- 创建plugins_todo待办事项表
CREATE TABLE IF NOT EXISTS plugins_todo (
    id BIGINT NOT NULL COMMENT '待办ID-雪花算法生成',
    status INT DEFAULT 0 COMMENT '状态1=完成 0=未完成',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    expire_time DATETIME DEFAULT NULL COMMENT '到期时间',
    todo TEXT DEFAULT NULL COMMENT '待办内容',
    weight INT DEFAULT NULL COMMENT '重要程度1-6',
    folder VARCHAR(20) DEFAULT NULL COMMENT '分类 today/week/其他',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='待办事项表';

CREATE INDEX plugins_todo_user_id_index ON plugins_todo (user_id);
CREATE INDEX plugins_todo_status_index ON plugins_todo (status);
CREATE INDEX plugins_todo_folder_index ON plugins_todo (folder);

-- 创建plugins_todo_folder待办文件夹表
CREATE TABLE IF NOT EXISTS plugins_todo_folder (
    id BIGINT NOT NULL COMMENT '文件夹ID-雪花算法生成',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    name VARCHAR(30) DEFAULT NULL COMMENT '文件夹名称',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='待办文件夹表';

CREATE INDEX plugins_todo_folder_user_id_index ON plugins_todo_folder (user_id);

-- 创建ai对话消息记录表
CREATE TABLE IF NOT EXISTS ai (
    id BIGINT NOT NULL COMMENT '记录ID-雪花算法生成',
    message LONGTEXT DEFAULT NULL COMMENT '消息内容',
    role VARCHAR(100) DEFAULT NULL COMMENT '角色 user/assistant/system',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    dialogue_id BIGINT DEFAULT NULL COMMENT '对话关联ID',
    ai_id VARCHAR(255) DEFAULT NULL COMMENT 'AI对话ID',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    reasoning_content LONGTEXT DEFAULT NULL COMMENT '推理结果',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='AI对话消息记录表';

CREATE INDEX ai_dialogue_id_index ON ai (dialogue_id);
CREATE INDEX ai_user_id_index ON ai (user_id);

-- 创建dialogue对话记录表
CREATE TABLE IF NOT EXISTS dialogue (
    id BIGINT NOT NULL COMMENT '对话ID-雪花算法生成',
    title VARCHAR(255) DEFAULT NULL COMMENT '对话标题',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    mode_id INT DEFAULT NULL COMMENT '模型ID',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='对话记录表';

CREATE INDEX dialogue_user_id_index ON dialogue (user_id);

-- 创建ai_model模型配置表
CREATE TABLE IF NOT EXISTS ai_model (
    id BIGINT NOT NULL COMMENT '模型ID-雪花算法生成',
    name VARCHAR(255) DEFAULT NULL COMMENT '模型名称',
    tips VARCHAR(255) DEFAULT NULL COMMENT '模型介绍',
    api_host VARCHAR(255) DEFAULT NULL COMMENT 'API网关',
    sk VARCHAR(255) NOT NULL COMMENT 'API密钥',
    model VARCHAR(255) DEFAULT NULL COMMENT '模型类型',
    system_content TEXT DEFAULT NULL COMMENT '默认系统指令',
    create_time DATETIME DEFAULT NULL COMMENT '创建时间',
    user_id INT DEFAULT NULL COMMENT '用户ID',
    status INT DEFAULT 1 COMMENT '状态1开启0禁用',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='AI模型配置表';

CREATE INDEX ai_model_status_index ON ai_model (status);
CREATE INDEX ai_model_user_id_index ON ai_model (user_id);

-- =====================================================
-- 默认数据插入 (使用 INSERT IGNORE 避免重复插入)
-- =====================================================

-- 插入卡片数据
INSERT IGNORE INTO card (id, name, name_en, version, tips, src, url, `window`) VALUES
(1001, '今天吃什么', 'food', 3, '吃什么是个很麻烦的事情', '/plugins/food/static/ico.png', '/plugins/food/card', '/plugins/food/window'),
(1002, '天气', 'weather', 13, '获取您所在地的实时天气！', '/plugins/weather/static/ico.png', '/plugins/weather/card', '/plugins/weather/window'),
(1003, '电子木鱼', 'muyu', 5, '木鱼一敲 烦恼丢掉', '/plugins/muyu/static/ico.png', '/plugins/muyu/card', '/plugins/muyu/window'),
(1004, '热搜', 'topSearch', 15, '聚合百度，哔站，微博，知乎，头条等热搜！', '/plugins/topSearch/static/ico.png', '/plugins/topSearch/card', '/plugins/topSearch/window'),
(1005, '记事本', 'noteApp', 15, '快捷记录您的灵感', '/plugins/noteApp/static/ico.png', '/plugins/noteApp/card', '/noteApp'),
(1006, '每日诗词', 'poetry', 8, '精选每日诗词！', '/plugins/poetry/static/ico.png', '/plugins/poetry/card', '/plugins/poetry/window'),
(1007, '日历', 'calendar', 1, '日历', '/plugins/calendar/static/ico.png', '/plugins/calendar/card', '/plugins/calendar/window'),
(1008, '待办事项', 'todo', 8, '快捷添加待办事项', '/plugins/todo/static/ico.png', '/plugins/todo/card', '/plugins/todo/window'),
(1009, '倒计时', 'countdown', 8, '个性化自定义事件的倒计时组件', '/plugins/countdown/static/ico.png', '/plugins/countdown/card', '/plugins/countdown/window'),
(1010, '纪念日', 'commemorate', 8, '个性化自定义事件的纪念日组件', '/plugins/commemorate/static/ico.png', '/plugins/commemorate/card', '/plugins/commemorate/window'),
(1011, 'AI助手', 'ai', 1, '您的随身AI助手', '/plugins/ai/static/ico.png', '/plugins/ai/card', '/plugins/ai/window'),
(1012, '图片格式转换', 'imageConversion', 1, '批量将图片格式转为JPEG,PNG,WEBP等格式', '/plugins/imageConversion/static/ico.png', '/plugins/imageConversion/card', '/plugins/imageConversion/window'),
(1013, '金额换算', 'amountConversion', 1, '将金额转为大写', '/static/app/amountConversion/ico.svg', '/plugins/amountConversion/card', '/plugins/amountConversion/window');
