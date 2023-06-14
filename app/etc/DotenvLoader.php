<?php

declare(strict_types=1);

use Qunity\Component\Dotenv\Dotenv;

Dotenv::create()->usePutEnv(true)->loadEnv(__DIR__ . '/../../dotenv/.env');
