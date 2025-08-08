<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Translation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(3),
            'locale' => $this->faker->randomElement(['en', 'fr', 'de', 'es', 'jp']),
            'value' => $this->faker->sentence(),
            'tag' => $this->faker->randomElement(['mobile', 'web', 'desktop']),
        ];
    }
}
