<?php

namespace EscolaLms\TopicTypes\Database\Factories\TopicContent;

use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;


class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            //'topic_id' => $this->faker->word,
            'value' => '1.mp4',
            'poster' => 'poster.jpg',
            'width' => 640,
            'height' => 480,
        ];
    }

    public function updatePath(int $videoId): VideoFactory
    {
        return $this->state(function () use ($videoId) {
            $word = $this->faker->word;
            $filename = "topic/$videoId/" . $word . '.mp4';
            $filenamePoster = "topic/$videoId/" . $word . '.jpg';
            $dest = Storage::disk('public')->path($filename);
            $destPoster = Storage::disk('public')->path($filenamePoster);
            $destDir = dirname($dest);
            if (!is_dir($destDir) || (mkdir($destDir, 0777, true) && !is_dir($destDir))) {
                throw new DirectoryNotFoundException(sprintf('Directory "%s" was not created', $destDir));
            }
            copy(realpath(__DIR__.'/../../mocks/1.mp4'), $dest);
            copy(realpath(__DIR__.'/../../mocks/poster.jpg'), $destPoster);

            return [
                'value' => $filename,
                'poster' => $filenamePoster,
            ];
        });
    }
}
