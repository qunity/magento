<?php

declare(strict_types=1);

use Qunity\Component\Dotenv\Dotenv;

Dotenv::create()->usePutEnv()->loadEnv(__DIR__ . '/../../dotenv/.env');
