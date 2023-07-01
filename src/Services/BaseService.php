<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 17:36
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Services;

abstract class BaseService
{

    /**
     * 使用分页
     *
     * @param $model 模型实例
     * @param string $sortColumn 排序字段
     * @param string $sort 排序规则 desc|asc
     * @return mixed 数据集
     */
    public function usePage($model, $sortColumn = 'id', $sort = 'desc')
    {
        $defaultPerPage = config('hyperf_kit.default_per_page');
        $isShowPage = get_global_init_params('is_show_page', false);
        $orderBy = get_global_init_params('order_by', '');
        $perPage = get_global_init_params('per_page', $defaultPerPage);
        $page = get_global_init_params('page', 1);

        $number = ($perPage > 0) ? $perPage : $defaultPerPage;  // 防止 $perPage 为负数

        if (! empty($orderBy)) {
            // 支持 $tempValue['order_by'] = id,desc|name,asc
            $order = explode('|', $orderBy);
            foreach ($order as $value) {
                if (! empty($value)) {
                    [$sortColumn, $sort] = explode(',', $value);
                    $model = $model->orderBy($sortColumn, $sort);
                }
            }
        } elseif ($sortColumn && $sort) {
            if (is_array($sortColumn) && is_array($sort)) {
                // 支持 $sortColumn = ['id','name'] , $sort = ['desc','asc']
                foreach ($sortColumn as $k => $col) {
                    $rank = array_key_exists($k,$sort) ? $sort[$k] : 'desc';
                    $model = $model->orderBy($col, $rank);
                }
            } else {
                $model = $model->orderBy($sortColumn, $sort);
            }
        }

        return $isShowPage ? $model->paginate($number, ['*'], 'page', $page) : $model->get();
    }

}