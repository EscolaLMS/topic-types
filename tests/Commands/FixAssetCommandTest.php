<?php

namespace Tests\Commands;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Database\Factories\TopicContent\Components\H5PHelper;
use EscolaLms\TopicTypes\Database\Factories\TopicContent\Components\ScormScoHelper;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\ScormSco;
use EscolaLms\TopicTypes\Tests\TestCase;
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\PDF;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use EscolaLms\TopicTypes\Services\Contracts\TopicTypeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class FixAssetCommand extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');

        Storage::fake('test');
        Storage::put('dummy.mp4', 'Some dummy data');
        Storage::put('dummy.mp3', 'Some dummy data');
        Storage::put('dummy.jpg', 'Some dummy data');
        Storage::put('dummy.png', 'Some dummy data');
        Storage::put('dummy.pdf', 'Some dummy data');
        $this->scormSco = ScormScoHelper::getScormSco();
        $this->h5p = H5PHelper::createH5PContent();

        $course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
        ]);
        $topic_audio = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);
        $topic_image = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);
        $topic_pdf = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);
        $topic_video = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);
        $topic_richtext = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);
        $topic_oembed = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);
        $topic_scorm_sco = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);
        $topic_h5p = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $topicable_audio = Audio::factory()->create([
            'value' => 'dummy.mp3',
        ]);
        $topicable_image = Image::factory()->create([
            'value' => 'dummy.jpg',
        ]);
        $topicable_pdf = PDF::factory()->create([
            'value' => 'dummy.pdf',
        ]);
        $topicable_video = Video::factory()->create([
            'value' => 'dummy.mp4',
            'poster' => 'dummy.png',
        ]);
        $topicable_richtext = RichText::factory()->create();
        $topicable_oembed = OEmbed::factory()->create();
        $topicable_scorm_sco = ScormSco::factory()->create([
            'value' => $this->scormSco->id
        ]);
        $topicable_h5p = H5P::factory()->create([
            'value' => $this->h5p->id
        ]);

        $topic_audio->topicable()->associate($topicable_audio)->save();
        $topic_image->topicable()->associate($topicable_image)->save();
        $topic_pdf->topicable()->associate($topicable_pdf)->save();
        $topic_video->topicable()->associate($topicable_video)->save();
        $topic_richtext->topicable()->associate($topicable_richtext)->save();
        $topic_oembed->topicable()->associate($topicable_oembed)->save();
        $topic_scorm_sco->topicable()->associate($topicable_scorm_sco)->save();
        $topic_h5p->topicable()->associate($topicable_h5p)->save();

        $this->course_id = $course->id;

        $this->topic_audio_id = $topic_audio->id;
        $this->topic_image_id = $topic_image->id;
        $this->topic_pdf_id = $topic_pdf->id;
        $this->topic_video_id = $topic_video->id;
        $this->topic_scom_sco_id = $topic_scorm_sco->id;
        $this->topic_h5p_id = $topic_h5p->id;
        $this->topic_richtext_id = $topic_richtext->id;
    }

    public function testService()
    {
        Storage::assertExists(['dummy.mp3', 'dummy.mp4', 'dummy.pdf', 'dummy.jpg', 'dummy.png']);

        $service = App::make(TopicTypeServiceContract::class);
        $service->fixAssetPaths();

        $this->assertAssetPathFix();;
    }

    public function testCommand()
    {
        Storage::assertExists(['dummy.mp3', 'dummy.mp4', 'dummy.pdf', 'dummy.jpg', 'dummy.png']);
        Artisan::call('escolalms:fix-topic-types-paths');

        $this->assertAssetPathFix();;
    }

    private function assertAssetPathFix(): void
    {
        $audio_path = "courses/$this->course_id/topic/$this->topic_audio_id/dummy.mp3";
        $image_path = "courses/$this->course_id/topic/$this->topic_image_id/dummy.jpg";
        $pdf_path = "courses/$this->course_id/topic/$this->topic_pdf_id/dummy.pdf";
        $video_path = "courses/$this->course_id/topic/$this->topic_video_id/dummy.mp4";
        $video_path2 = "courses/$this->course_id/topic/$this->topic_video_id/dummy.png";
        $scorm_path = "courses/$this->course_id/topic/$this->topic_scom_sco_id/export.zip";
        $h5p_path = "courses/$this->course_id/topic/$this->topic_h5p_id/export.h5p";

        Storage::assertMissing(['dummy.mp3', 'dummy.mp4', 'dummy.pdf', 'dummy.jpg', 'dummy.png']);
        Storage::assertExists([$audio_path, $image_path, $pdf_path, $video_path, $video_path2, $scorm_path, $h5p_path]);
    }
}
