<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Commands extends BaseConfig
{
    public array $commands = [
        'users:deactivate-inactive' => \App\Commands\DeactivateInactiveUsers::class,
    ];
}
