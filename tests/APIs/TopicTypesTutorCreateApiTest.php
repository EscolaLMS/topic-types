<?php

namespace Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
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

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Image::class,
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicAudio()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Audio::class,
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicPdf()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\PDF',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

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

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Video::class,
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_videos', [
            'value' => $path,
        ]);

        Event::assertDispatched(TopicTypeChanged::class);
    }

    public function testCreateTopicRichtext()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => RichText::class,
                'value' => 'lorem ipsum',
            ]
        );
        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path,
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
