<?php
/**
 * @see document link  https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md#processors
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 16:05
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Kernel\Log;

use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Monolog\Processor\ProcessorInterface;

class AppendRequestIdProcessor implements ProcessorInterface
{
    public const REQUEST_ID = 'request_id';

    public function __invoke(array $record)
    {
        $record['extra']['request_id'] = Context::getOrSet(self::REQUEST_ID, uniqid());
        $record['extra']['coroutine_id'] = Coroutine::id();
        $record['extra']['parent_coroutine_id'] = Coroutine::parentId();
        return $record;
    }
}