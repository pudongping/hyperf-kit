<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 16:05
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Exception;

use Hyperf\Server\Exception\ServerException;
use Throwable;
use Pudongping\HyperfKit\Constants\ErrorCode;

class ApiException extends ServerException
{

    public function __construct(int $code = 0, string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        }

        parent::__construct($message, $code, $previous);
    }

}