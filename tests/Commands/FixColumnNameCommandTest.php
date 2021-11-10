<?php

namespace Tests\Commands;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class FixColumnNameCommand extends TestCase
{
    use /*ApiTestTrait,*/ DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');

        Storage::fake('default');
        Storage::disk('default')->put('dummy.mp4', 'Some dummy data');

        $course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
        ]);
        $this->topic_video = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->topicable_video = Video::factory()->create([
            'value' => 'dummy.mp4',
            'poster' => 'dummy.png',
        ]);

        $this->topic_video->topicable()->associate($this->topicable_video)->save();

        $this->course_id = $course->id;
    }

    public function testService()
    {
        $this->expectException(\Exception::class);
        $this->expectException(\Error::class);

        $t = Topic::find($this->topic_video->id)
            ->update(['topicable_type' => "EscolaLms\\Courses\\Models\\TopicContent\Video"]);

        try {
            $this->topic_video->refresh();
        } finally {
            $this->assertNull($this->topic_video->topicable);
            // Artisan::call('escolalms:fix-type-column-name');
            $service = App::make(TopicTypeServiceContract::class);
            $service->fixTopicTypeColumnName();

            $this->topic_video->refresh();

            $this->assertEquals($this->topic_video->topicable->id, $this->topicable_video->id);
        }
    }

    public function testCommand()
    {
        $this->expectException(\Exception::class);
        $this->expectException(\Error::class);

        $t = Topic::find($this->topic_video->id)
            ->update(['topicable_type' => "EscolaLms\\Courses\\Models\\TopicContent\Video"]);

        try {
            $this->topic_video->refresh();
        } finally {
            $this->assertNull($this->topic_video->topicable);
            Artisan::call('escolalms:fix-type-column-name');
            /*
            $service = App::make(TopicTypeServiceContract::class);
            $service->fixTopicTypeColumnName();
            */

            $this->topic_video->refresh();

            $this->assertEquals($this->topic_video->topicable->id, $this->topicable_video->id);
        }
    }
}
