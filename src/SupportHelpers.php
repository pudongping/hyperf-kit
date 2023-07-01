<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-26 16:39
 */
declare(strict_types=1);

use Hyperf\Utils\Context;
use Hyperf\Utils\Str;

if (! function_exists('simple_db_debug_sql')) {
    /**
     * 极简 DB 打印 sql 语句
     *
     * @param string $sql 预处理 sql 语句
     * @param array $bindings  绑定参数
     * @param float $executeTime  程序执行时间
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function simple_db_debug_sql(string $sql, array $bindings = [], float $executeTime = 0.0): array
    {
        $executeSql = Str::replaceArray('?', $bindings, $sql);
        logger()->info(sprintf('simple db sql debug ==> time：%ss ==> %s', $executeTime, $executeSql));

        $key = config('hyperf_kit.context_key.simple_sql');

        $sqlArr = Context::get($key, []);
        array_push($sqlArr, [
            'query' => $executeSql,
            'code_execute_time' => sprintf('%ss', $executeTime),  // 代码执行时间（不是 sql 执行时间）
        ]);

        Context::set($key, $sqlArr);

        return $sqlArr;
    }
}

if (! function_exists('lock_spin')) {
    /**
     * 自旋锁
     *
     * @param callable $callBack 需要触发的回调函数
     * @param string $key 缓存 key（加锁的颗粒度）
     * @param int $counter 尝试触发多少次直至回调函数处理完成
     * @param int $expireTime 缓存时间（实际上是赌定回调函数处理多少秒内可以处理完成）
     * @param int $loopWaitTime 加锁等待时长
     * @return null
     * @throws RedisException
     */
    function lock_spin(callable $callBack, string $key, int $counter = 10, int $expireTime = 5, int $loopWaitTime = 500000)
    {
        $result = null;
        while ($counter > 0) {
            $val = microtime() . '_' . uniqid('', true);
            $noticeLog = compact('key', 'val', 'expireTime', 'loopWaitTime', 'counter');
            logger()->notice(__FUNCTION__ . ' ====> ', $noticeLog);
            if (redis()->set($key, $val, ['NX', 'EX' => $expireTime])) {
                if (redis()->get($key) === $val) {
                    try {
                        $result = $callBack();
                    } finally {
                        $delKeyLua = 'if redis.call("GET", KEYS[1]) == ARGV[1] then return redis.call("DEL", KEYS[1]) else return 0 end';
                        redis()->eval($delKeyLua, [$key, $val], 1);
                        logger()->notice(__FUNCTION__ . ' delete key ====> ', $noticeLog);
                    }
                    return $result;
                }
            }
            $counter--;
            usleep($loopWaitTime);
        }
        return $result;
    }
}

if (! function_exists('cache_remember')) {
    /**
     * 获取并缓存
     *
     * @param string $key 缓存key
     * @param int $ttl 缓存过期时间，单位：秒（s）。如果为 0 时，则表示永不过期
     * @param callable $callBack 取不到缓存数据时，获取数据的执行闭包
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function cache_remember(string $key, int $ttl, callable $callBack)
    {
        $value = cache()->get($key);
        if (! is_null($value)) {
            return $value;
        }

        cache()->set($key, $value = $callBack(), $ttl);

        return $value;
    }
}

if (! function_exists('is_env_local')) {
    /**
     * 当前环境是否为本地环境
     *
     * @return bool
     */
    function is_env_local(): bool
    {
        return config('app_env') === 'local';
    }
}

if (! function_exists('is_env_dev')) {
    /**
     * 当前环境是否为开发环境
     *
     * @return bool
     */
    function is_env_dev(): bool
    {
        return config('app_env') === 'dev';
    }
}

if (! function_exists('is_env_test')) {
    /**
     * 当前环境是否为测试环境
     *
     * @return bool
     */
    function is_env_test(): bool
    {
        return config('app_env') === 'test';
    }
}

if (! function_exists('is_env_prod')) {
    /**
     * 当前环境是否为生产环境
     *
     * @return bool
     */
    function is_env_prod(): bool
    {
        return config('app_env') === 'prod';
    }
}

if (! function_exists('set_global_init_params')) {
    /**
     * 重新设置全局初始化参数
     *
     * @param string $key
     * @param $value
     * @return mixed|null
     */
    function set_global_init_params(string $key, $value = null)
    {
        $tempValueKey = config('hyperf_kit.context_key.temp_value');
        if (! Context::has($tempValueKey)) return $value;
        $contextData = Context::get($tempValueKey, []);

        $override = array_merge($contextData, [
            $key => $value
        ]);

        return Context::set($tempValueKey, $override);
    }
}

if (! function_exists('get_global_init_params')) {
    /**
     * 获取初始化全局参数
     *
     * @param string|null $key
     * @param $default
     * @return false|mixed|null
     */
    function get_global_init_params(?string $key = '', $default = null)
    {
        $tempValueKey = config('hyperf_kit.context_key.temp_value');
        if (! Context::has($tempValueKey)) return $default;
        $contextData = Context::get($tempValueKey, []);
        if (! $key) return $contextData;
        $value = $contextData[$key] ?? $default;

        return $value;
    }
}