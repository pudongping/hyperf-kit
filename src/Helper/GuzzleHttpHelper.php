<?php
/**
 * English document link : https://docs.guzzlephp.org/en/stable/
 *
 * zh-CN document link : https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-29 11:18
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit\Helper;

use Hyperf\Guzzle\ClientFactory;
use function Hyperf\Config\config;

class GuzzleHttpHelper
{

    public function __construct(protected ClientFactory $clientFactory)
    {
    }

    public function get(string $url, array $params = []): mixed
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'query' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['query'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('GET', $url, array_merge($arr, $params));
    }

    public function post(string $url, array $params = []): mixed
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'form_params' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['form_params'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('POST', $url, array_merge($arr, $params));
    }

    public function upload(string $url, string $filePath): mixed
    {
        $arr = [
            'multipart' => [
                [
                    'name' => 'file_name',
                    'contents' => fopen($filePath, 'r')
                ],
            ],
        ];

        return $this->response('POST', $url, $arr);
    }

    public function put(string $url, array $params = []): mixed
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'json' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['json'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('PUT', $url, array_merge($arr, $params));
    }

    public function json(string $url, array $params = [], string $method = 'POST')
    {
        $arr = [
            'headers' => $params['headers'] ?? ['Content-Type' => 'application/json'],
            'json' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['json'] = $params['body'];
            unset($params['body']);
        }

        return $this->response($method, $url, array_merge($arr, $params));
    }

    public function delete(string $url, array $params = []): mixed
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'json' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['json'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('DELETE', $url, array_merge($arr, $params));
    }

    public function response(string $method, string $url, array $args): mixed
    {
        $enable = config('hyperf_kit.log.guzzle_enable', true);
        $enable && logger()->info(sprintf("此时为 %s 请求，请求地址为 ====> %s 参数为 ====> %s", $method, $url, var_export($args, true)));
        $client = $this->clientFactory->create();
        $response = $client->request($method, $url, $args);
        $contents = $response->getBody()->getContents();
        $enable && logger()->info(sprintf("请求返回的结果为  ====> %s ", var_export($contents, true)));
        return json_decode($contents, true) ?: [];
    }

}
