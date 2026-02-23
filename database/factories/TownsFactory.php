<?php

namespace Database\Factories;
use App\Models\Towns;
use App\Models\Counties;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Towns>
 */
class TownsFactory extends Factory
{
    protected $model = Towns::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'zip_code' => $this->faker->postcode,
            'county_id' => Counties::factory(), // creates a county if not passed
        ];
    }
}
