<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 16:06
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\Validation\ValidationException;
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Model\ModelNotFoundException;
use Pudongping\HyperfKit\Traits\ResponseTrait;
use Pudongping\HyperfKit\Constants\ErrorCode;
use Pudongping\HyperfKit\Exception\ApiException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Exception\MethodNotAllowedHttpException;
use Pudongping\HyperfThrottleRequests\Exception\ThrottleRequestsException;
use function Hyperf\Config\config;

class ApiExceptionHandler extends ExceptionHandler
{

    use ResponseTrait;

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $code = ErrorCode::SERVER_ERROR;
        $msg = '';

        switch (true) {
            case $throwable instanceof ValidationException:
                $code = ErrorCode::ERR_HTTP_UNPROCESSABLE_ENTITY;
                $msg = $throwable->validator->errors()->first();
                break;
            case $throwable instanceof QueryException:
                $code = ErrorCode::ERR_QUERY;
                break;
            case $throwable instanceof \PDOException:
                $code = ErrorCode::ERR_DB;
                break;
            case $throwable instanceof ModelNotFoundException:
                $code = ErrorCode::ERR_MODEL;
                break;
            case $throwable instanceof NotFoundHttpException:
                $code = ErrorCode::NOT_FOUND;
                $msg = '路由未定义或不支持当前请求';
                break;
            case $throwable instanceof MethodNotAllowedHttpException:
                $code = ErrorCode::ERR_HTTP_METHOD_NOT_ALLOWED;
                $msg = $throwable->getMessage();
                break;
            case $throwable instanceof ThrottleRequestsException:
                $code = ErrorCode::REQUEST_FREQUENTLY;
                break;
            case $throwable instanceof ApiException:
                $code = $throwable->getCode() ?: ErrorCode::SERVER_ERROR;
                $msg = ErrorCode::SERVER_ERROR == $code ? ErrorCode::getMessage($code) : $throwable->getMessage();
                break;
        }

        $msg = $msg ?: ErrorCode::getMessage($code) ?: 'Whoops, No Error Data';

        // 阻止异常冒泡
        $this->stopPropagation();

        config('hyperf_kit.log.exception', true) && logger('ApiException')->error(format_throwable($throwable));  // 记录错误日志

        return $this->fail($code, [], $msg);
    }

    /**
     * 判断该异常处理器是否要对该异常进行处理
     *
     * @param Throwable $throwable 抛出的异常
     * @return bool  该异常处理器是否处理该异常
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

}