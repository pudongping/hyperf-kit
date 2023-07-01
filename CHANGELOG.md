# Release Notes for 2.x

## 2.0.0 - 2023-07-01

### Fixed

### Changed

- php >= 8.0
- hyperf ~3.0.0

---

## 1.0.0 - 2023-07-01

### Fixed

### Changed

- 初始化提交。
- php >= 7.2
- hyperf ~2.2.0
- 增加通用性错误码常量
- 增加异常处理类
- 增加文件系统处理助手类，提供各种云存储
- 增加 Guzzle Http 助手类
- 自动复制 `Psr\Http\Message\ServerRequestInterface::class` 协程上下文
- 日志自动添加 request_id（在一次 http 请求生命周期中，记录的日志含有同一个 request_id 方便链路追踪）
- 增加跨域中间件
- 增加初始化全局变量中间件
- 增加请求日志中间件
- 增加验证器父类
- 增加服务层父类
- 增加统一接口返回 Trait
- 增加验证器 Trait （特殊情况下无法使用依赖注入 Request 可能会用到）
- 增加数据库相关助手函数 DBHelpers.php
- 增加和 hyperf 框架有关的助手函数 FrameworkHelpers.php
- 增加基于 hyperf 框架开发的相关助手函数 SupportHelpers.php