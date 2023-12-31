<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 11:16
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Traits;

use Pudongping\HyperfKit\Constants\ErrorCode;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Collection\Collection as BaseCollection;
use Hyperf\Context\Context;
use function Hyperf\Config\config;

trait ResponseTrait
{

    /**
     * 正确时返回
     *
     * @param mixed $data 返回数据
     * @return PsrResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    final public function send(mixed $data): PsrResponseInterface
    {
        $ret['code'] = ErrorCode::SUCCESS;
        $ret['msg'] = ErrorCode::getMessage(ErrorCode::SUCCESS);
        $ret['data'] = $this->parseData($data);

        if ($sql = $this->showSql()) {
            $ret['query'] = $sql;
        }

        return response()->json($ret);
    }

    /**
     * 失败时返回
     *
     * @param int $code 错误码
     * @param array $data 错误返回数据
     * @param string $message 错误信息
     * @return PsrResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    final public function fail(int $code = ErrorCode::ERROR, array $data = [], string $message = ''): PsrResponseInterface
    {
        $ret['code'] = $code;
        $ret['msg'] = $message ?: ErrorCode::getMessage($code) ?: '错误信息未定义';
        // $ret['data'] = $this->parseData($data);

        if ($sql = $this->showSql()) {
            $ret['query'] = $sql;
        }

        return response()->json($ret);
    }

    /**
     * 打印 sql 日志
     *
     * @return array
     */
    final public function showSql(): array
    {
        $sql = [];
        if (config('hyperf_kit.debug') && get_global_init_params('debug')) {
            // sql 日志
            $connections = array_keys(config('databases', []));
            foreach ($connections as $connection) {
                $sqlLog = Db::connection($connection)->getQueryLog();
                Db::connection($connection)->flushQueryLog();  // 防止一个接口多次调用时 sql 打印重复
                if (! empty($sqlLog)) {
                    $sql[$connection] = $sqlLog;
                }
            }

            // 极简 DB sql 日志
            $simpleSqlKey = config('hyperf_kit.context_key.simple_sql');
            if (Context::has($simpleSqlKey)) {
                $sql['simple_db_sql'] = Context::get($simpleSqlKey);
            }

        }

        return $sql;
    }

    /**
     * @param mixed $originalData
     * @return mixed
     */
    final public function parseData(mixed $originalData): mixed
    {
        if (is_null($originalData) || (is_object($originalData) && empty((array)$originalData))) {
            // 如果返回值直接为 null 或者为空对象时
            return (new \stdClass());
        }
        if (! is_object($originalData)) return $originalData;

        $data = [];
        $resultField = 'result';
        switch (true) {
            case $originalData instanceof LengthAwarePaginator:  // 分页数据时
                {
                    // Test::query()->paginate();  ==> Hyperf\Paginator\LengthAwarePaginator
                    $data[$resultField] = $originalData->toArray()['data'];  // 分页后的单页数据
                    $data['pagination'] = prepare_for_page($originalData);  // 拼接分页数据结构
                }
                break;
            case $originalData instanceof Collection:  // 模型取多条数据时
                // Test::query()->get();  ==> Hyperf\Database\Model\Collection
                $data[$resultField] = $originalData->toArray();
                break;
            case $originalData instanceof Model:
                // Test::query()->first()  ==> Hyperf\Database\Model\Model
                $data = $originalData->toArray();
                break;
            case $originalData instanceof BaseCollection:
                // Db::table('table_name')->get();  ==> Hyperf\Utils\Collection
                $data[$resultField] = $originalData->toArray();
                break;
            case $originalData instanceof \stdClass:
                // Db::table('table_name')->first();  ==> \stdClass
                $data = (array)$originalData;
                break;
        }

        // 极简 DB （Hyperf\DB\DB） query 查询返回的数据始终为数组（不管是取多条还是取一条），因此不用管

        return $data;
    }

}
