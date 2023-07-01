<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-25 17:50
 */
declare(strict_types=1);

use Hyperf\Utils\ApplicationContext;
use Hyperf\Redis\RedisFactory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\DB\DB as HyperfSimpleDB;
use Hyperf\Paginator\LengthAwarePaginator;
use Psr\EventDispatcher\EventDispatcherInterface;

if (! function_exists('container')) {
    /**
     * 获取容器对象
     *
     * @param string $id
     * @return mixed|\Psr\Container\ContainerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function container(string $id = '')
    {
        $container = ApplicationContext::getContainer();

        if ($id) return $container->get($id);

        return $container;
    }
}

if (! function_exists('di')) {
    /**
     * @param string $id
     * @return mixed|\Psr\Container\ContainerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function di(string $id = '')
    {
        return container($id);
    }
}

if (! function_exists('redis')) {
    /**
     * 获取 Redis 协程客户端
     *
     * @param string $poolName 连接池名称
     * @return \Hyperf\Redis\RedisProxy
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function redis(string $poolName = 'default')
    {
        return container()->get(RedisFactory::class)->get($poolName);
    }
}

if (! function_exists('std_out_log')) {
    /**
     * 控制台日志
     *
     * @return StdoutLoggerInterface|mixed
     */
    function std_out_log()
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

if (! function_exists('logger')) {
    /**
     * 文件日志
     *
     * @param $name
     * @param $group
     * @return \Psr\Log\LoggerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function logger($name = 'hyperf', $group = 'default')
    {
        return container()->get(LoggerFactory::class)->get($name, $group);
    }
}

if (! function_exists('request')) {
    /**
     * request 实例
     *
     * @return RequestInterface|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function request()
    {
        return container()->get(RequestInterface::class);
    }
}

if (! function_exists('response')) {
    /**
     * response 实例
     *
     * @return ResponseInterface|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function response()
    {
        return container()->get(ResponseInterface::class);
    }
}

if (! function_exists('cache')) {
    /**
     * 简单的缓存实例
     *
     * @return mixed|CacheInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function cache()
    {
        return container()->get(CacheInterface::class);
    }
}

if (! function_exists('simple_db')) {
    /**
     * 极简 DB
     *
     * @return HyperfSimpleDB|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function simple_db()
    {
        return container()->get(HyperfSimpleDB::class);
    }
}

if (! function_exists('queue_push')) {
    /**
     * 将任务投递到异步队列中
     *
     * @param JobInterface $job
     * @param int $delay
     * @param string $key
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = container()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}

if (! function_exists('event_dispatch')) {
    /**
     * 事件分发
     *
     * @param object $event  事件对象
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function event_dispatch(object $event)
    {
        return container()->get(EventDispatcherInterface::class)->dispatch($event);
    }
}

if (! function_exists('format_throwable')) {
    /**
     * 将错误异常对象格式化成字符串
     *
     * @param Throwable $throwable
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function format_throwable(Throwable $throwable): string
    {
        return container()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * 获取客户端 ip
     *
     * @return mixed|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function get_client_ip()
    {
        $request = request();
        return $request->getHeaderLine('X-Forwarded-For')
            ?: $request->getHeaderLine('X-Real-IP')
            ?: ($request->getServerParams()['remote_addr'] ?? '')
            ?: '127.0.0.1';
    }
}

if (! function_exists('get_current_action')) {
    /**
     * 获取当前请求的控制器和方法
     *
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function get_current_action(): array
    {
        $obj = request()->getAttribute(Dispatched::class);

        if (property_exists($obj, 'handler')
            && isset($obj->handler)
            && property_exists($obj->handler, 'callback')
        ) {
            $action = $obj->handler->callback;
        } else {
            throw new \Exception('The route is undefined! Please check!');
        }

        $errMsg = 'The controller and method are not found! Please check!';
        if (is_array($action)) {
            list($controller, $method) = $action;
        } elseif (is_string($action)) {
            if (strstr($action, '::')) {
                list($controller, $method) = explode('::', $action);
            } elseif (strstr($action, '@')) {
                list($controller, $method) = explode('@', $action);
            } else {
                list($controller, $method) = [false, false];
                std_out_log()->error($errMsg);
            }
        } else {
            list($controller, $method) = [false, false];
            std_out_log()->error($errMsg);
        }

        return compact('controller', 'method');
    }
}

if (! function_exists('route_original')) {
    /**
     * 获取路由地址
     *
     * @param bool $withParams 是否需要携带参数
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function route_original(bool $withParams = false): string
    {
        $obj = request()->getAttribute(Dispatched::class);

        if (! property_exists($obj, 'handler')
            || ! isset($obj->handler)
            || ! property_exists($obj->handler, 'route')
        ) {
            throw new \Exception('The route is undefined! Please check!');
        }

        if ($withParams) {
            // eg: "/foo/bar/article/detail/123"
            return request()->getPathInfo();
        }

        // eg: "/foo/bar/{hello}/detail/{id:\d+}"
        return $obj->handler->route;
    }
}

if (! function_exists('prepare_for_page')) {
    /**
     * 拼接分页数据结构
     *
     * @param LengthAwarePaginator $obj  分页数据集
     * @return array
     */
    function prepare_for_page(LengthAwarePaginator $obj): array
    {
        $res = [];
        $pageArr = $obj->toArray();
        $res['total'] = $pageArr['total'];  // 数据总数
        $res['count'] = $obj->count();  // 当前页的条数
        $res['current_page'] = $pageArr['current_page'];  // 当前页数
        $res['last_page'] = $pageArr['last_page'];  // 最后页数
        $res['per_page'] = $pageArr['per_page'];  // 每页的数据条数
        $res['from'] = $pageArr['from'];  // 当前页中第一条数据的编号
        $res['to'] = $pageArr['to'];  // 当前页中最后一条数据的编号
        $res['links']['first_page_url'] = $pageArr['first_page_url'];  // 第一页的 url
        $res['links']['last_page_url'] = $pageArr['last_page_url'];  // 最后一页的 url
        $res['links']['prev_page_url'] = $pageArr['prev_page_url'];  // 上一页的 url
        $res['links']['next_page_url'] = $pageArr['next_page_url'];  // 下一页的 url
        $res['links']['path'] = $pageArr['path'];  // 所有 url 的基本路径
        return $res;
    }
}