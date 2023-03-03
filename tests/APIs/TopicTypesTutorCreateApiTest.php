<?php

namespace Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\TopicTypes\Database\Factories\TopicContent\Components\Cmi5AuHelper;
use EscolaLms\TopicTypes\Database\Factories\TopicContent\Components\H5PHelper;
use EscolaLms\TopicTypes\Database\Factories\TopicContent\Components\ScormScoHelper;
use EscolaLms\TopicTypes\Models\TopicContent\Cmi5Au;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\Project;
use EscolaLms\TopicTypes\Models\TopicContent\ScormSco;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use EscolaLms\TopicTypes\Tests\TestCase;
use EscolaLms\TopicTypes\Events\TopicTypeChanged;
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

class TopicTypesTutorCreateApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $this->lesson = Lesson::factory(['course_id' => $this->course->id])->create();
    }

    /**
     * @test
     */
    public function testCreateTopicImage()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json',])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Image::class,
                'value' => $file,
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $path = $data->topicable->value;

        Storage::disk('local')->assertExists('/' . $path);
        $this->assertDatabaseHas('topic_images', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicAudio()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json',])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Audio::class,
                'value' => $file,
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $path = $data->topicable->value;

        Storage::disk('local')->assertExists('/' . $path);
        $this->assertDatabaseHas('topic_audios', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicPdf()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\PDF',
                'value' => $file,
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $path = $data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);
        $this->assertDatabaseHas('topic_pdfs', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicVideo()
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $file = UploadedFile::fake()->image('avatar.mp4');

        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Video::class,
                'value' => $file,
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $path = $data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);
        $this->assertDatabaseHas('topic_videos', [
            'value' => $path,
        ]);

        Event::assertDispatched(TopicTypeChanged::class);
    }

    public function testCreateTopicRichtext()
    {
        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => RichText::class,
                'value' => 'lorem ipsum',
            ]);
        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $value = $data->topicable->value;

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $value,
        ]);
    }

    public function testCreateTopicH5P()
    {
        Event::fake(TopicTypeChanged::class);

        $content = H5PHelper::createH5PContent();
        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => H5P::class,
                'value' => $content->getKey(),
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $value = $data->topicable->value;

        $this->assertDatabaseHas('topic_h5ps', [
            'value' => $value,
        ]);

        Event::assertDispatched(TopicTypeChanged::class);
    }

    public function testCreateTopicScormSco()
    {
        Event::fake(TopicTypeChanged::class);
        $scormSco = ScormScoHelper::getScormSco();

        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => ScormSco::class,
                'value' => $scormSco->getKey(),
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $value = $data->topicable->value;

        $this->assertDatabaseHas('topic_scorm_scos', [
            'value' => $value,
        ]);

        Event::assertDispatched(TopicTypeChanged::class);
    }

    public function testCreateTopicCmi5Au()
    {
        if (!class_exists(\EscolaLms\Cmi5\EscolaLmsCmi5ServiceProvider::class)) {
            $this->markTestSkipped('Require cmi5 package');
        }
        Event::fake(TopicTypeChanged::class);
        $cmi5Au = Cmi5AuHelper::getCmi5Au();

        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Cmi5Au::class,
                'value' => $cmi5Au->getKey(),
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $value = $data->topicable->value;

        $this->assertDatabaseHas('topic_cmi5_aus', [
            'value' => $value,
        ]);

        Event::assertDispatched(TopicTypeChanged::class);
    }

    public function testCreateTopicOEmbed()
    {
        Event::fake(TopicTypeChanged::class);

        $this->response = $this->actingAs($this->user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => OEmbed::class,
                'value' => 'https://youtu.be/b-mGA4V2LK0',
            ]);

        $this->response->assertStatus(201);

        $data = $this->response->getData()->data;
        $value = $data->topicable->value;

        $this->assertDatabaseHas('topic_oembeds', [
            'value' => $value,
        ]);

        Event::assertDispatched(TopicTypeChanged::class);
    }

    public function testCreateTopicProject(): void
    {
        $this->response = $this->actingAs($this->user, 'api')
            ->postJson('/api/admin/topics', [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Project::class,
                'value' => 'lorem ipsum',
            ])
            ->assertCreated();

        $data = $this->response->getData()->data;
        $value = $data->topicable->value;

        $this->assertDatabaseHas('topic_projects', [
            'value' => $value,
        ]);
    }

    public function testCreateTopicNoLesson()
    {
        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'topicable_type' => RichText::class,
                'value' => 'lorem ipsum',
            ]
        );

        $this->response->assertStatus(401);
    }

    public function testCreateTopicImageNoFile()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Image::class,
                'value' => 'file',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testCreateTopicAudioNoFile()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Audio::class,
                'value' => 'file',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testCreateTopicVideoNoFile()
    {
        $course = Course::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Video::class,
                'value' => 'file',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testCreateTopicWrongClass()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\Courses\TopicTypes\TopicContent\RichTextAAAAAA',
                'value' => 'lorem ipsum',
            ],
        );

        $this->response->assertStatus(422);
    }

    public function testCreateTopicImageWithReusableFile(): void
    {
        Storage::fake('local');

        $imagePath = "course/{$this->course->getKey()}/reusable/image.jpg";
        Storage::makeDirectory("course/{$this->course->getKey()}/reusable");
        copy(__DIR__ . '/../mocks/image.jpg', Storage::path($imagePath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics',
            [
                'title' => 'Create new topic',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Image::class,
                'value' => $imagePath,
            ]
        )->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $path = $data->data->topicable->value;

        $this->assertEquals($imagePath, $path);
        Storage::assertExists($path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $imagePath,
        ]);
    }

    public function testCreateTopicAudioWithReusableFile(): void
    {
        Storage::fake('local');

        $audioPath = "course/{$this->course->getKey()}/reusable/audio.mp3";
        Storage::makeDirectory("course/{$this->course->getKey()}/reusable");
        copy(__DIR__ . '/../mocks/audio.mp3', Storage::path($audioPath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics',
            [
                'title' => 'Create new topic',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Audio::class,
                'value' => $audioPath,
            ]
        )->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $path = $data->data->topicable->value;

        $this->assertEquals($audioPath, $path);
        Storage::assertExists($path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $audioPath,
        ]);
    }

    public function testCreateTopicVideoWithReusableFile(): void
    {
        Storage::fake('local');

        $videoPath = "course/{$this->course->getKey()}/reusable/video.mp4";
        $posterPath = "course/{$this->course->getKey()}/reusable/image.jpg";
        Storage::makeDirectory("course/{$this->course->getKey()}/reusable");
        copy(__DIR__ . '/../mocks/video.mp4', Storage::path($videoPath));
        copy(__DIR__ . '/../mocks/image.jpg', Storage::path($posterPath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics',
            [
                'title' => 'Create new topic',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Video::class,
                'value' => $videoPath,
                'poster' => $posterPath,
            ]
        )->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $savedVideoPath = $data->data->topicable->value;
        $savedPosterPath = $data->data->topicable->poster;

        $this->assertEquals($videoPath, $savedVideoPath);
        $this->assertEquals($posterPath, $savedPosterPath);
        Storage::assertExists($savedVideoPath);
        Storage::assertExists($savedPosterPath);

        $this->assertDatabaseHas('topic_videos', [
            'value' => $videoPath,
            'poster' => $posterPath,
        ]);
    }

    public function testCreateTopicImageWrongReusableFilePrefixPath(): void
    {
        Storage::fake('local');

        $newCourse = Course::factory()->create();
        $imagePath = "course/{$newCourse->getKey()}/image.jpg";
        Storage::makeDirectory("course/{$newCourse->getKey()}");
        copy(__DIR__ . '/../mocks/image.jpg', Storage::path($imagePath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics',
            [
                'title' => 'Create new topic',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Image::class,
                'value' => $imagePath,
            ]
        )->assertStatus(422);
    }

    public function testCreateTopicImageWithNoExistReusableFile(): void
    {
        Storage::fake('local');

        $imagePath = "image.jpg";
        Storage::assertMissing($imagePath);

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics',
            [
                'title' => 'Create new topic',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Image::class,
                'value' => $imagePath,
            ]
        )->assertStatus(422);
    }
}
