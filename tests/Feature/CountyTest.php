<?php

namespace Tests\Feature;

use App\Models\Counties;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CountyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_county()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/counties', [
            'name' => 'Somogy',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['county']);
    }

    public function test_county_show_by_id()
    {
        Sanctum::actingAs(User::factory()->create());
        $county = Counties::create(['name' => 'Somogy']);

        $response = $this->getJson("/api/counties/show?id={$county->id}");
        $response->assertStatus(200)
                 ->assertJson(['county' => ['name' => 'Somogy']]);
    }
}
