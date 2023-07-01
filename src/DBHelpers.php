<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-25 17:51
 */
declare(strict_types=1);

use Pudongping\SmartAssist\DBHelper;
use Hyperf\DbConnection\Db;

if (! function_exists('batch_update_case_when')) {
    /**
     * 批量更新
     *
     * @param string $tableName
     * @param array $where
     * @param array $needUpdateFields
     * @return int|null
     */
    function batch_update_case_when(string $tableName, array $where, array $needUpdateFields): ?int
    {
        $result = DBHelper::batchUpdateCaseWhen($tableName, $where, $needUpdateFields);
        if (! $result['query'] || ! $result['bindings']) {
            return null;
        }

        return Db::update($result['query'], $result['bindings']);
    }
}

if (! function_exists('upsert')) {
    /**
     * 批量插入或更新
     *
     * @param string $tableName
     * @param array $data
     * @param array $columns
     * @return bool|null
     */
    function upsert(string $tableName, array $data, array $columns): ?bool
    {
        $result = DBHelper::upsert($tableName, $data, $columns);
        if (! $result['query'] || ! $result['bindings']) {
            return null;
        }

        return Db::statement($result['query'], $result['bindings']);
    }
}