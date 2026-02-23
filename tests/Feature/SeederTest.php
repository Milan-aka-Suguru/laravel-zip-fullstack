<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TownSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_seeder_creates_towns()
    {
        Storage::fake('local');
        Storage::disk('local')->put('iranyitoszamok.csv', "Postal Code;Place Name;County\n7400;Kaposvár;Somogy");

        $this->artisan('db:seed --class=TownsFromCsvSeeder')
             ->assertExitCode(0);

        $this->assertDatabaseHas('towns', ['name' => 'Kaposvár', 'zip_code' => '7400']);
        $this->assertDatabaseHas('counties', ['name' => 'Somogy']);
    }
}
