<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 16:05
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Kernel\Context;

use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Pudongping\HyperfKit\Kernel\Log\AppendRequestIdProcessor;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;

class Coroutine
{
    protected LoggerInterface $logger;

    protected LoggerFactory $loggerFactory;

    protected ?FormatterInterface $formatter;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->loggerFactory = $container->get(LoggerFactory::class)->get('coroutine');
        if ($container->has(FormatterInterface::class)) {
            $this->formatter = $container->get(FormatterInterface::class);
        }
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public function create(callable $callable): int
    {
        $id = Utils\Coroutine::id();
        $coroutine = Co::create(function () use ($callable, $id) {
            try {
                // 按需复制，禁止复制 Socket，不然会导致 Socket 跨协程调用从而报错
                Context::copy($id, [
                    AppendRequestIdProcessor::REQUEST_ID,
                    ServerRequestInterface::class,
                ]);
                $callable();
            } catch (Throwable $throwable) {
                if ($this->formatter) {
                    $this->logger->warning($this->formatter->format($throwable));
                    $this->loggerFactory->error($this->formatter->format($throwable));
                } else {
                    $this->logger->warning((string) $throwable);
                }
            }
        });

        try {
            return $coroutine->getId();
        } catch (Throwable $throwable) {
            $this->logger->warning((string)$throwable);
            $this->loggerFactory->warning((string)$throwable);
            return -1;
        }
    }
}
