<?php

declare(strict_types=1);

use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants as ConfigConstants;
use Magento\Framework\Math\Random;
use Magento\Framework\Setup\BackendFrontnameGenerator;
use Magento\Setup\Model\CryptKeyGenerator;
use Qunity\Component\Dotenv\Dotenv;

return (static function (array $config): array {
    // @phpcs:disable Magento2.Functions, Magento2.Security

    $files = glob(__DIR__ . '/env.d/*.env.php');
    $config = array_reduce($files, static function (array $config, string $file): array {
        return array_replace_recursive($config, require_once $file);
    }, $config);

    register_shutdown_function(static function (array $config): void {
        ObjectManager::getInstance()->get(CacheManager::class)
            ->setEnabled(array_keys($config['cache_types'] ?? [], false), false);
    }, $config);

    file_put_contents(__FILE__, sprintf('<?php return %s;', var_export($config, true)));

    // @phpcs:enable: Magento2.Functions, Magento2.Security
    return $config;
})([
    'backend' => [
        'frontName' => Dotenv::value(
            'BACKEND__FRONTNAME',
            fn (): string => (new BackendFrontnameGenerator())->generate(),
            [
                Dotenv::VARIABLE_VALUE => [
                    fn (mixed $value): string => is_callable($value) ? $value() : (string) $value,
                ],
            ],
        ),
    ],
    'db' => [
        'connection' => [
            'default' => [
                'host' => Dotenv::value('DB__CONNECTION__DEFAULT__HOST'),
                'dbname' => Dotenv::value('DB__CONNECTION__DEFAULT__DBNAME'),
                'username' => Dotenv::value('DB__CONNECTION__DEFAULT__USERNAME'),
                'password' => Dotenv::value('DB__CONNECTION__DEFAULT__PASSWORD'),
            ],
        ],
    ],
    'crypt' => [
        'key' => Dotenv::value(
            'CRYPT__KEY',
            fn (): string => (new CryptKeyGenerator(new Random()))->generate(),
            [
                Dotenv::VARIABLE_VALUE => [
                    fn (mixed $value): string => is_callable($value) ? $value() : (string) $value,
                ],
            ],
        ),
    ],
    'MAGE_MODE' => Dotenv::value('MAGE_MODE', 'default'),
    'modules' => Dotenv::values([], [
        Dotenv::VARIABLE_NAME => [
            fn (string $name): string => 'MODULES_' . preg_replace('/[A-Z]/', '_$0', $name),
        ],
        Dotenv::VARIABLE_VALUE => [
            fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN),
        ],
    ]),
    'cache_types' => Dotenv::values([
        'block_html' => true,
        'full_page' => true,
    ], [
        Dotenv::VARIABLE_NAME => [
            fn (string $name): string => 'CACHE_TYPES_' . preg_replace('/^./', '_$0', $name),
        ],
        Dotenv::VARIABLE_VALUE => [
            fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN),
        ],
    ]),
    'cache' => [
        'graphql' => [
            'id_salt' => Dotenv::value(
                'CACHE__GRAPHQL__ID_SALT',
                fn (): string => (new Random())->getRandomString(ConfigConstants::STORE_KEY_RANDOM_STRING_SIZE),
                [
                    Dotenv::VARIABLE_VALUE => [
                        fn (mixed $value): string => is_callable($value) ? $value() : (string) $value,
                    ],
                ],
            ),
        ],
        'frontend' => [],
    ],
    'downloadable_domains' => Dotenv::value('DOWNLOADABLE_DOMAINS', 'localhost', [
        Dotenv::VARIABLE_VALUE => [
            fn (mixed $value): array => array_map(
                'trim',
                array_values(array_diff(explode(';', $value), [''])),
            ),
        ],
    ]),
]);
