<?php

namespace Database\Seeders;

use App\Models\Counties;
use App\Models\Towns;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class TownsFromCsvSeeder extends Seeder
{
    public function run()
    {
        $csvFilePath = Storage::disk('local')->path('iranyitoszamok.csv');

        if (!file_exists($csvFilePath)) {
            $csvFilePath = base_path('iranyitoszamok.csv');
        }

        if (($handle = fopen($csvFilePath, 'r')) !== false) {
            // Count total rows first (for progress bar max)
            $totalRows = 0;
            while (fgetcsv($handle, 1000, ';') !== false) {
                $totalRows++;
            }
            rewind($handle);

            // Skip header
            fgetcsv($handle, 1000, ';');

            $output = new ConsoleOutput();
            $progressBar = new ProgressBar($output, $totalRows);
            $progressBar->start();

            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                $row = array_map(function ($value) {
                    return trim(preg_replace("/\r|\n/", "", $value));
                }, $row);

                if (count($row) < 3 || $this->isHeaderRow($row)) {
                    $progressBar->advance();
                    continue;
                }

                $zipCode = trim($row[0]);
                $townName = trim($row[1]);
                $countyName = trim($row[2]);

                if (empty($zipCode) || empty($townName)) {
                    $progressBar->advance();
                    continue;
                }

                $county = Counties::firstOrCreate(['name' => $countyName]);

                Towns::create([
                    'name' => $townName,
                    'zip_code' => $zipCode,
                    'county_id' => $county->id,
                ]);

                $progressBar->advance();
            }

            fclose($handle);
            $progressBar->finish();
            $output->writeln("\nSeeding completed!");
        } else {
            Log::error("Could not open the CSV file at path: {$csvFilePath}");
        }
    }

    private function isHeaderRow($row)
    {
        $headerValues = [
            'Postal Code' => 0,
            'Place Name' => 1,
            'County' => 2,
        ];

        foreach ($headerValues as $header => $index) {
            if (stripos($row[$index], $header) !== false) {
                return true;
            }
        }

        return false;
    }
}
