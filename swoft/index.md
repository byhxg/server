##一、安装

1、通过 Composer 安装
```
composer create-project swoft/swoft swoft
```

2、手动安装
```
git clone https://github.com/swoft-cloud/swoft
cd swoft
composer install
cp .env.example .env
```

##配置 
1、编辑 .env 文件，根据需要调整相关环境配置


## Start

- Http Server

```bash
[root@swoft swoft]# php bin/swoft http:start
```

- WebSocket Server

```bash
[root@swoft swoft]# php bin/swoft ws:start
```

- RPC Server

```bash
[root@swoft swoft]# php bin/swoft rpc:start
```

- TCP Server

```bash
[root@swoft swoft]# php bin/swoft tcp:start
```

- Process Pool

```bash
[root@swoft swoft]# php bin/swoft process:start
```
##目录结构

```angular2

├── app/    ----- 应用代码目录
│   ├── Annotation/        ----- 定义注解相关
│   ├── Aspect/            ----- AOP 切面
│   ├── Common/            ----- 一些具有独立功能的 class bean
│   ├── Console/           ----- 命令行代码目录
│   ├── Exception/         ----- 定义异常类目录
│   │   └── Handler/           ----- 定义异常处理类目录
│   ├── Http/              ----- HTTP 服务代码目录
│   │   ├── Controller/
│   │   └── Middleware/
│   ├── Helper/            ----- 助手函数
│   ├── Listener/          ----- 事件监听器目录
│   ├── Model/             ----- 模型、逻辑等代码目录(这些层并不限定，根据需要使用)
│   │   ├── Dao/
│   │   ├── Data/
│   │   ├── Logic/
│   │   └── Entity/
│   ├── Rpc/               ----- RPC 服务代码目录
│   │   └── Service/
│   │   └── Middleware/
│   ├── WebSocket/         ----- WebSocket 服务代码目录
│   │   ├── Chat/
│   │   ├── Middleware/
│   │   └── ChatModule.php
│   ├── Tcp/               ----- TCP 服务代码目录
│   │   └── Controller/        ----- TCP 服务处理控制器目录
│   ├── Application.php    ----- 应用类文件继承自swoft核心
│   ├── AutoLoader.php     ----- 项目扫描等信息(应用本身也算是一个组件)
│   └── bean.php
├── bin/
│   ├── bootstrap.php
│   └── swoft              ----- Swoft 入口文件
├── config/                ----- 应用配置目录
│   ├── base.php               ----- 基础配置
│   └── db.php                 ----- 数据库配置
├── public/                ----- 公共目录
├── resource/              ----- 应用资源目录
│   ├── language/              ----- 语言资源目录  
│   └── view/                  ----- 视图资源目录  
├── runtime/               ----- 临时文件目录（日志、上传文件、文件缓存等）
├── test/                  ----- 单元测试目录
│   └── bootstrap.php
├── composer.json
├── phar.build.inc
└── phpunit.xml.dist
```

