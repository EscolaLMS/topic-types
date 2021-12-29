<?php

namespace EscolaLms\TopicTypes\Database\Factories\TopicContent;

use EscolaLms\TopicTypes\Database\Factories\TopicContent\Components\FileHelper;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //'topic_id' => $this->faker->word,
            'value' => '1.jpg',
            'width' => 640,
            'height' => 480,
        ];
    }

    public function updatePath(int $imageId)
    {
        return $this->state(function (array $attributes) use ($imageId) {
            return FileHelper::uploadFile($imageId, $this->faker->word);
        });
    }
}
