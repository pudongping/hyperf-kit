<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 17:17
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Codec\Json;

class LogInfoMiddleware implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(
        ContainerInterface $container,
        RequestInterface $request
    ) {
        $this->container = $container;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);

        if (config('hyperf_kit.log.request_only')) {
            logger()->info('Request ========> ' . $this->logFormat($this->getRequestInfo($request, $startTime)));
        }

        $response = $handler->handle($request);

        $endTime = microtime(true);
        $costTime = $endTime - $startTime;

        if (config('hyperf_kit.log.response_only')) {
            $result = $this->getResponseInfo($response);
            if (is_array($result)) {
                $result = compact('costTime', 'startTime', 'endTime', 'result');
            }
            if ($costTime > (float)config('hyperf_kit.log.code_run_cost_timeout', 5)) {
                logger()->warning('Response ========> ' . $this->logFormat($result));
            } else {
                logger()->info('Response ========> ' . $this->logFormat($result));
            }
        }

        return $response;
    }

    private function getRequestInfo(ServerRequestInterface $request, float $startTime)
    {
        $data = [
            'method' => $request->getMethod(),  // 当前请求方法 GET/POST/PUT/PATCH ……
            'current_url' => $this->request->url(),
            'full_url' => $this->request->fullUrl(),
            'origin' => $this->request->header('Origin'),  // 外部请求源链接地址
            'user_agent' => $this->request->header('user-agent'),  // 请求设备信息
            'headers' => $request->getHeaders(),
            'server_params' => $request->getServerParams(),
            'remote_addr' => $request->getServerParams()['remote_addr'] ?? '',  // 浏览当前页面的用户的 IP 地址
            'start_time' => $startTime,  // 请求开始时间
        ];

        if (false === strpos($request->getHeaderLine('Content-Type'), 'multipart/form-data')) {
            $data['original_params'] = $this->request->all(); // 客户端请求的所有原始数据
        } else {
            $data['original_params'] = 'The body contains boundary data, ignore it.';
        }

        return $data;
    }

    private function getResponseInfo(ResponseInterface $response)
    {
        // 控制器返回的数据
        $result = $response->getBody()->getContents();
        return json_decode($result, true);
    }

    private function logFormat($data)
    {
        return config('hyperf_kit.log.format_human') ? var_export($data, true) : Json::encode($data);
    }

}