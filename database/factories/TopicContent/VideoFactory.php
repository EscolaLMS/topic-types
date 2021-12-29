<?php

namespace EscolaLms\TopicTypes\Database\Factories\TopicContent;

use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    public function definition()
    {
        return [
            //'topic_id' => $this->faker->word,
            'value' => '1.mp4',
            'poster' => 'poster.jpg',
            'width' => 640,
            'height' => 480,
        ];
    }

    public function updatePath(int $videoId)
    {
        return $this->state(function (array $attributes) use ($videoId) {
            $word = $this->faker->word;
            $filename = "topic/$videoId/".$word.'.mp4';
            $filename_poster = "topic/$videoId/".$word.'.jpg';
            $dest = storage_path("app/public/$filename");
            $dest_poster = storage_path("app/public/$filename_poster");
            $destDir = dirname($dest);
            if (!is_dir($destDir) ||
                (mkdir($destDir, 0777, true) && !is_dir($destDir))
            ) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $destDir));
            }
            copy(realpath(__DIR__.'/../../mocks/1.mp4'), $dest);
            copy(realpath(__DIR__.'/../../mocks/poster.jpg'), $dest_poster);

            return [
                'value' => $filename,
                'poster' => $filename_poster,
            ];
        });
    }
}
