<?php

namespace Tests\Helpers;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Facades\Markdown;
use EscolaLms\TopicTypes\Tests\TestCase;

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
        $destinationPrefix = sprintf('courses/%d/topic/%d/', $course->id, $topic->id);

        $result = Markdown::convertImagesPathsForImageApi('lorem ipsum', $destinationPrefix);
        $this->assertArrayHasKey('value', $result);
        $this->assertTrue($result['value'] === 'lorem ipsum');
    }
}
