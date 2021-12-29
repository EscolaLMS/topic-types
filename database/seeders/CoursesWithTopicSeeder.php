<?php

namespace EscolaLms\TopicTypes\Database\Seeders;

use EscolaLms\Auth\Models\User;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Database\Factories\FakerMarkdownProvider\FakerProvider;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicResource;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\Tags\Models\Tag;
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\PDF;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class CoursesWithTopicSeeder extends Seeder
{
    use WithFaker;

    private function getRandomRichContent($withH5P = false)
    {
        $classes = [RichText::factory(), Audio::factory(), Video::factory(), Image::factory(), OEmbed::factory(), PDF::factory()];

        if ($withH5P) {
            $classes[] = H5P::factory();
        }

        return $classes[array_rand($classes)];
    }

    public function run()
    {
        $this->faker = $this->makeFaker();
        $this->faker->addProvider(new FakerProvider($this->faker));
        $randomTags = [$this->faker->name, $this->faker->name, $this->faker->name, $this->faker->name, $this->faker->name, $this->faker->name];
        if (class_exists(EscolaLms\HeadlessH5P\Models\H5PContent::class)) {
            $hasH5P = H5PContent::first() !== null;
        } else {
            $hasH5P = false;
        }
        $path = storage_path('app/public/tutor_avatar.jpg');
        copy(__DIR__ . '/avatar.jpg', $path);
        $tutors = User::role('tutor')->get();
        foreach ($tutors as $tutor) {
            $tutor->update([
                'path_avatar' => 'tutor_avatar.jpg',
                'bio' => $this->faker->markdown(),
            ]);
        }
        $courses = Course::factory()
            ->count(random_int(5, 10))
            ->afterCreating(function (Course $course) use ($hasH5P) {
                Lesson::factory()
                    ->count(random_int(2, 5))
                    ->afterCreating(function (Lesson $lesson) use ($hasH5P) {
                        Topic::factory()
                            ->count(random_int(4, 8))
                            ->afterCreating(function (Topic $topic) use ($hasH5P) {
                                $content = $this->getRandomRichContent($hasH5P);
                                if (method_exists($content, 'updatePath')) {
                                    $content = $content->updatePath($topic->id)->create();
                                } else {
                                    $content = $content->create();
                                }
                                $topic->topicable()->associate($content)->save();
                                TopicResource::factory()->count(random_int(1, 3))->forTopic($topic)->create();
                            })
                            ->create(['lesson_id' => $lesson->id]);
                    })
                    ->create(['course_id' => $course->id]);
            })
            ->create();
        /** @var Course $course */
        foreach ($courses as $course) {
            $this->seedTags($course, $randomTags);
            $this->seedCategories($course);
        }
    }

    private function seedTags($model, $randomTags)
    {
        for ($i = 0; $i < 3; ++$i) {
            Tag::create([
                'morphable_id' => $model->getKey(),
                'morphable_type' => get_class($model),
                'title' => $this->faker->randomElement($randomTags),
            ]);
        }
    }

    private function seedCategories(Course $course)
    {
        $categories = Category::inRandomOrder()->limit(3)->get();
        foreach ($categories as $category) {
            $course->categories()->save($category);
        }
    }
}
