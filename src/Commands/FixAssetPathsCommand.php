<?php

namespace EscolaLms\TopicTypes\Commands;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Console\Command;

class FixAssetPathsCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'escolalms:fix-topic-types-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes all topic types paths so they are always in course/{id} folder';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $i = 0;
        // I hate imperative programming, but I'm so lazy ....
        foreach (Topic::all() as $topic) {
            $topicable = $topic->topicable;
            if (isset($topicable)) {
                foreach ($topic->topicable->fixAssetPaths() as $fix) {
                    $this->info('moving file from '.$fix[0].' to '.$fix[1]);
                    ++$i;
                }
            }
        }
        $this->info('The command was successful! Number of fixed Topics '.$i);
    }
}
