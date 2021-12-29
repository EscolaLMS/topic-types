<?php

namespace Tests\Helpers;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Facades\Markdown;
use EscolaLms\TopicTypes\Tests\TestCase;
use Illuminate\Http\UploadedFile;

class HelpersMethodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Course::factory()->create();
        Lesson::factory()->create();
        $this->topic = Topic::factory()->create();
    }

    public function testConvertImagesMethos()
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $markdown = new MarkdownTest();
        $parseUrl = $markdown->verifyParseUrl([
            "path" => "api/images/img",
            "query" => "path=courses%2F{$course->id}%2Ftopic%2F{$topic->id}%2Ftest"
        ]);
        $this->assertTrue($parseUrl === "api/images/img?path=courses/{$course->id}/topic/{$topic->id}/test");
    }
}
