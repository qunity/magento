<?php

declare(strict_types=1);

use Qunity\Base\Component\Dotenv;

Dotenv::create()->usePutEnv(true)->loadEnv(__DIR__ . '/../../dotenv/.env');
