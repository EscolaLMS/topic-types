<?php

namespace EscolaLms\TopicTypes\Database\Factories\TopicContent;

use EscolaLms\TopicTypes\Models\TopicContent\ScormSco;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScormScoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ScormSco::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => $this->faker->uuid,
        ];
    }
}
