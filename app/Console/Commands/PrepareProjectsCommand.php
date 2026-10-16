<?php

namespace App\Console\Commands;

use Database\Seeders\StarterPlansSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('projects:prepare')]
#[Description('Prepare the database and starter plans without creating remote resources')]
class PrepareProjectsCommand extends Command
{
    public function handle(): int
    {
        if (! config('app.key')) {
            if (app()->isProduction()) {
                $this->components->error('Set APP_KEY in the hosting environment before preparing the application.');

                return self::FAILURE;
            }

            if ($this->call('key:generate', ['--no-interaction' => true]) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        foreach ([
            ['migrate', ['--force' => true]],
            ['db:seed', ['--force' => true]],
            ['modules:seed', []],
            ['db:seed', ['--class' => StarterPlansSeeder::class, '--force' => true]],
        ] as [$command, $arguments]) {
            if ($this->call($command, $arguments) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
