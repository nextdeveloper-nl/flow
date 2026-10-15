<?php

namespace NextDeveloper\Flow\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\Flow\Services\PipelinesService;

/**
 * Drops the cached pipeline board snapshots (Redis) so they are rebuilt from scratch on the
 * next GET /flow/pipelines/{id}/board request. Safe to run at any time; for recovery or after
 * a deploy that changes the board payload.
 *
 *   php artisan flow:rebuild-board            (every pipeline)
 *   php artisan flow:rebuild-board {uuid}     (one pipeline)
 */
class RebuildBoardCommand extends Command
{
    protected $signature = 'flow:rebuild-board {pipeline? : UUID of a single pipeline; all pipelines when omitted}';

    protected $description = 'Drop cached Flow board snapshots so they are rebuilt from scratch on next request';

    public function handle(): int
    {
        $count = PipelinesService::invalidateAllBoards($this->argument('pipeline'));

        $this->info("Board snapshot dropped for {$count} pipeline(s); it will be rebuilt on next request.");

        return self::SUCCESS;
    }
}
