<?php

namespace Database\Factories;
use App\Models\Counties;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Counties>
 */
class CountiesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Counties::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->state(),
        ];
    }
}
