<?php

namespace Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use EscolaLms\TopicTypes\Tests\TestCase;
use EscolaLms\TopicTypes\Events\TopicTypeChanged;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class TopicTypesTutorUpdateApiTest extends TestCase
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
        $this->topic = Topic::factory()->create([
            'lesson_id' => $this->lesson->id,
            'json' => ['foo' => 'bar', 'bar' => 'foo'],
        ]);
    }

    public function testUpdateTopicImage(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->response = $this->withHeaders([
            'Content' => 'multipart/form-data',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => Image::class,
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $path,
        ]);
        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicAudio(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);
        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $path,
        ]);
        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicAudioWithNewFile(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);
        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path,
        ]);

        // ***
        // Update sending another file as value
        // ***

        $file2 = UploadedFile::fake()->create('another.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $file2,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path,
        ]);

        // ***
        // Update sending current file path as value
        // ***

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $path,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path,
        ]);
        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicVideo(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $file = UploadedFile::fake()->create('avatar.mp4');

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Video',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_videos', [
            'value' => $path,
        ]);

        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicRichtext(): void
    {
        Event::fake(TopicTypeChanged::class);
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\RichText',
                'value' => 'lorem ipsum',
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path,
        ]);
        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicPdf(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);
        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\PDF',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_pdfs', [
            'value' => $path,
        ]);
        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicWrongClass(): void
    {
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\RichTextAAAAAA',
                'value' => 'lorem ipsum',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testUpdateTopicWithJson(): void
    {
        Event::fake(TopicTypeChanged::class);
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\RichText',
                'value' => 'lorem ipsum',
                'introduction' => 'asdf1',
                'summary' => 'asdf2',
                'description' => 'asdf3',
                'json' => json_encode(['foo' => 'foobar']),
            ]
        );

        $this->response->assertStatus(200);

        $data = $this->response->json();

        $this->topicId = $data['data']['id'];
        $path = $data['data']['topicable']['value'];

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path,
        ]);
        $this->assertEquals(['foo' => 'foobar'], $data['data']['json']);
        $this->assertEquals('foobar', $data['data']['json']['foo']);
        $this->assertEquals('asdf1', $data['data']['introduction']);
        $this->assertEquals('asdf2', $data['data']['summary']);
        $this->assertEquals('asdf3', $data['data']['description']);

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->get(
            '/api/courses/'.$this->topic->lesson->course_id.'/program'
        );

        $this->response->assertOk();
        $data = $this->response->json();

        $this->assertEquals(['foo' => 'foobar'], $data['data']['lessons'][0]['topics'][0]['json']);
        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicImageWithReusableFile(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $imagePath = "course/{$this->course->getKey()}/reusable/image.jpg";
        Storage::makeDirectory("course/{$this->course->getKey()}/reusable");
        copy(__DIR__ . '/../mocks/image.jpg', Storage::path($imagePath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => Image::class,
                'value' => $imagePath,
            ]
        )->assertStatus(200);

        $data = json_decode($this->response->getContent());
        $path = $data->data->topicable->value;

        $this->assertEquals($imagePath, $path);
        Storage::assertExists($path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $imagePath,
        ]);

        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicAudioWithReusableFile(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $audioPath = "course/{$this->course->getKey()}/reusable/audio.mp3";
        Storage::makeDirectory("course/{$this->course->getKey()}/reusable");
        copy(__DIR__ . '/../mocks/audio.mp3', Storage::path($audioPath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => Audio::class,
                'value' => $audioPath,
            ]
        )->assertStatus(200);

        $data = json_decode($this->response->getContent());
        $path = $data->data->topicable->value;

        $this->assertEquals($audioPath, $path);
        Storage::assertExists($path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $path,
        ]);

        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicVideoWithReusableFile(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $videoPath = "course/{$this->course->getKey()}/reusable/video.mp4";
        $posterPath = "course/{$this->course->getKey()}/reusable/image.jpg";
        Storage::makeDirectory("course/{$this->course->getKey()}/reusable");
        copy(__DIR__ . '/../mocks/video.mp4', Storage::path($videoPath));
        copy(__DIR__ . '/../mocks/image.jpg', Storage::path($posterPath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => Video::class,
                'value' => $videoPath,
                'poster' => $posterPath,
            ]
        )->assertStatus(200);

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

        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicH5PWithReusableFile(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $library = H5PLibrary::factory()
            ->create(['runnable' => 1]);
        $contentH5P = H5PContent::factory()
            ->create([
                'library_id' => $library->getKey()
            ]);

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => H5P::class,
                'value' => $contentH5P->getKey(),
            ]
        );

        $data = json_decode($this->response->getContent());
        $contentH5PId = $data->data->topicable->value;

        $this->assertEquals($contentH5P->getKey(), $contentH5PId);

        $this->assertDatabaseHas('topic_h5ps', [
            'value' => $contentH5PId,
        ]);

        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicOEmbed(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => OEmbed::class,
                'value' => 'abc',
            ]
        );

        $data = json_decode($this->response->getContent());
        $oEmbedResponseValue = $data->data->topicable->value;

        $this->assertEquals('abc', $oEmbedResponseValue);

        $this->assertDatabaseHas('topic_oembeds', [
            'value' => 'abc',
        ]);

        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }

    public function testUpdateTopicRichTextNew(): void
    {
        Storage::fake('local');
        Event::fake(TopicTypeChanged::class);

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => RichText::class,
                'value' => 'abc',
            ]
        );

        $data = json_decode($this->response->getContent());
        $richTextResponseValue = $data->data->topicable->value;

        $this->assertEquals('abc', $richTextResponseValue);

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => 'abc',
        ]);

        Event::assertDispatched(TopicTypeChanged::class, function ($event) {
            return $event->getUser() === $this->user && $event->getTopicContent();
        });
    }
}
