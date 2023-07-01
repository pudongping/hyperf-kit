<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-30 17:55
 */
declare(strict_types=1);

namespace Pudongping\HyperfKit;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [],
            'listeners' => [],
            'aspects' => [],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => '配置文件',
                    'source' => __DIR__ . '/../publish/hyperf_kit.php',
                    'destination' => BASE_PATH . '/config/autoload/hyperf_kit.php',
                ],
            ],
        ];
    }

}