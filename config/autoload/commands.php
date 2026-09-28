<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */
use Hyperf\Database\Commands\Migrations\FreshCommand;
use Hyperf\Database\Commands\Migrations\InstallCommand;
use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Hyperf\Database\Commands\Migrations\RefreshCommand;
use Hyperf\Database\Commands\Migrations\ResetCommand;
use Hyperf\Database\Commands\Migrations\RollbackCommand;
use Hyperf\Database\Commands\Migrations\StatusCommand;

/*
 * Migration commands live in hyperf/database but are normally registered by
 * hyperf/devtool (require-dev). Register them here so `migrate` works in
 * production images built with `composer install --no-dev`.
 */
return [
    InstallCommand::class,
    MigrateCommand::class,
    FreshCommand::class,
    RefreshCommand::class,
    ResetCommand::class,
    RollbackCommand::class,
    StatusCommand::class,
];
