<?php

namespace Tests\Feature;

use App\Models\Towns;
use App\Models\Counties;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TownTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_town()
    {
        Sanctum::actingAs(User::factory()->create());
        $county = Counties::create(['name' => 'TestCounty']);

        $response = $this->postJson('/api/towns', [
            'name' => 'TestTown',
            'zip_code' => '1234',
            'county_id' => $county->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['town']);
    }

    public function test_town_show_by_name()
    {
        Sanctum::actingAs(User::factory()->create());
        $county = Counties::create(['name' => 'TestCounty']);
        Towns::create([
            'name' => 'Kaposvár',
            'zip_code' => '7400',
            'county_id' => $county->id,
        ]);

        $response = $this->getJson('/api/towns/show?name=Kaposvár');
        $response->assertStatus(200)
                 ->assertJsonStructure(['towns']);
    }
}
