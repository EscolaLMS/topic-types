<?php

namespace Tests\Helpers;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Facades\Markdown;
use EscolaLms\TopicTypes\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class HelpersMethodTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->course = Course::factory()->create();
        $this->lesson = Lesson::factory([
            'course_id' => $this->course->getKey()
        ])->create();
        $this->topic = Topic::factory([
            'lesson_id' => $this->lesson->getKey()
        ])->create();
    }

    public function testConvertImagesApi()
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $file = 'test.jpg';
        $destinationPrefix = sprintf('course/%d/topic/%d/', $course->id, $topic->id);
        Storage::disk('public')->makeDirectory($destinationPrefix);
        copy(__DIR__ . '/test.jpg', Storage::disk('public')->path($destinationPrefix . $file));
        $result = Markdown::convertImagesPathsForImageApi("![Image] (api/images/img?path={$file})", $destinationPrefix);
        $this->assertArrayHasKey('results', $result);
        $this->assertTrue(is_array($result['results']));
        $this->assertTrue(isset($result['results'][0]) && is_array($result['results'][0]));
    }
}
