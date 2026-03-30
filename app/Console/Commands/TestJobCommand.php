<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\TestJob;
use Illuminate\Console\Command;

final class TestJobCommand extends Command
{
    protected $signature = 'test:job';

    protected $description = 'Command description';

    public function handle(): void
    {
        TestJob::dispatch();
    }
}
