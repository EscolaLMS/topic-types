<?php

namespace EscolaLms\TopicTypes\Commands;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Console\Command;

class FixTopicTypeColumnName extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'escolalms:fix-type-column-name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes Topicable DBs column name from old `EscolaLms\Courses\Models\TopicContent\XXX` to `EscolaLms\TopicTypes\Models\TopicContent\XXX`';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $i = 0;

        $topics = Topic::where('topicable_type', 'like', 'EscolaLms\\\\Courses\\\\Models\\\\TopicContent%')->get();

        foreach ($topics as $topic) {
            $topic->topicable_type = str_replace('EscolaLms\Courses\Models\TopicContent', "EscolaLms\TopicTypes\Models\TopicContent", $topic->topicable_type);
            $topic->save();
            ++$i;
        }
        $this->info('The command was successful! Number of fixed Topics '.$i);
    }
}
