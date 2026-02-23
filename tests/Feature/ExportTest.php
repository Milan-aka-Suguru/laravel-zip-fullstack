<?php

namespace Tests\Feature;

use App\Models\Counties;
use App\Models\Towns;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_counties_csv_requires_email()
    {
        $response = $this->postJson('/api/export/counties/csv', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['error' => ['email']]);
    }

    public function test_export_counties_csv_sends_email()
    {
        Mail::fake();

        Counties::factory()->create(['name' => 'Test County']);

        $response = $this->postJson('/api/export/counties/csv', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Export sent successfully to test@example.com']);

        Mail::assertSent(\App\Mail\ExportMail::class, function ($mail) {
            return $mail->hasTo('test@example.com') &&
                   $mail->exportType === 'counties';
        });
    }

    public function test_export_counties_pdf_sends_email()
    {
        Mail::fake();

        Counties::factory()->create(['name' => 'Test County']);

        $response = $this->postJson('/api/export/counties/pdf', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        Mail::assertSent(\App\Mail\ExportMail::class);
    }

    public function test_export_towns_csv_sends_email()
    {
        Mail::fake();

        $county = Counties::factory()->create();
        Towns::factory()->create([
            'name' => 'Test Town',
            'zip_code' => '1234',
            'county_id' => $county->id,
        ]);

        $response = $this->postJson('/api/export/towns/csv', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        Mail::assertSent(\App\Mail\ExportMail::class, function ($mail) {
            return $mail->hasTo('test@example.com') &&
                   $mail->exportType === 'towns';
        });
    }

    public function test_export_towns_csv_with_query_filter()
    {
        Mail::fake();

        $county = Counties::factory()->create(['name' => 'Test County']);
        Towns::factory()->create([
            'name' => 'Budapest',
            'zip_code' => '1000',
            'county_id' => $county->id,
        ]);
        Towns::factory()->create([
            'name' => 'Debrecen',
            'zip_code' => '4000',
            'county_id' => $county->id,
        ]);

        $response = $this->postJson('/api/export/towns/csv', [
            'email' => 'test@example.com',
            'query' => 'Budapest',
        ]);

        $response->assertStatus(200);

        Mail::assertSent(\App\Mail\ExportMail::class);
    }

    public function test_export_returns_error_with_no_data()
    {
        $response = $this->postJson('/api/export/counties/csv', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'No data to export']);
    }
}
