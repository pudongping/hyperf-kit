<h1 align="center">hyperf-kit</h1>

<p align="center">

[![Latest Stable Version](https://poser.pugx.org/pudongping/hyperf-kit/v/stable.svg)](https://packagist.org/packages/pudongping/hyperf-kit)
[![Total Downloads](https://poser.pugx.org/pudongping/hyperf-kit/downloads.svg)](https://packagist.org/packages/pudongping/hyperf-kit)
[![Latest Unstable Version](https://poser.pugx.org/pudongping/hyperf-kit/v/unstable.svg)](https://packagist.org/packages/pudongping/hyperf-kit)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg)](https://php.net/)
[![Packagist](https://img.shields.io/packagist/v/pudongping/hyperf-kit.svg)](https://github.com/pudongping/hyperf-kit)
[![License](https://poser.pugx.org/pudongping/hyperf-kit/license)](https://packagist.org/packages/pudongping/hyperf-kit)

</p>

> 为 Hyperf 框架提供的实用工具类或者函数，让开发变得更快、更轻松。

## 运行环境

- php >= 8.0
- composer
- hyperf ~3.0.0

## 分支或者标签

### 分支

- **2.2:** For hyperf 2.2
- **3.0:** For hyperf 3.0

### 标签

- **1.0.x:** For hyperf 2.2
- **2.0.x:** For hyperf 3.0

## 安装

```shell
composer require pudongping/hyperf-kit:^2.0 -vvv
```

## 配置

### 发布配置

在你自己的项目根目录下，执行以下命令

```shell
php bin/hyperf.php vendor:publish pudongping/hyperf-kit
```

因为此包还配套使用了 `hyperf-throttle-requests` 包和 `hyperf-alarm-clock` 包，因此还需要执行以下命令发布相关配置文件。

```shell
php bin/hyperf.php vendor:publish pudongping/hyperf-throttle-requests

php bin/hyperf.php vendor:publish pudongping/hyperf-alarm-clock
```

## 使用

具体使用可以参考 [hyperf-biz-web-api](https://github.com/pudongping/hyperf-biz-web-api) 项目。

## Changelog

[CHANGELOG](./CHANGELOG.md)

## License

MIT