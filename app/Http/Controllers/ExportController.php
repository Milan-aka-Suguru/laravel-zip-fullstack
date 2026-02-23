<?php

namespace App\Http\Controllers;

use App\Mail\ExportMail;
use App\Models\Counties;
use App\Models\Towns;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export counties to CSV and send via email
     */
    public function exportCountiesCsv(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'query' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $query = Counties::query();
        
        if ($request->filled('query')) {
            $searchQuery = $request->input('query');
            $query->where('name', 'like', "%{$searchQuery}%");
        }

        $counties = $query->get();

        if ($counties->isEmpty()) {
            return response()->json(['error' => 'No data to export'], 400);
        }

        $filename = 'counties_' . now()->format('Y-m-d_His') . '.csv';
        $filePath = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Generate CSV
        $file = fopen($filePath, 'w');
        
        // Add BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header
        fputcsv($file, ['ID', 'Név'], ';');
        
        // Add data
        foreach ($counties as $county) {
            fputcsv($file, [
                $county->id,
                $county->name,
            ], ';');
        }
        
        fclose($file);

        // Send email
        try {
            Mail::to($request->input('email'))->send(
                new ExportMail('counties', $filePath, $filename)
            );

            // Clean up file after sending
            unlink($filePath);

            return response()->json(['message' => 'Export sent successfully to ' . $request->input('email')]);
        } catch (\Exception $e) {
            // Clean up file on error
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export counties to PDF and send via email
     */
    public function exportCountiesPdf(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'query' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $query = Counties::query();
        
        if ($request->filled('query')) {
            $searchQuery = $request->input('query');
            $query->where('name', 'like', "%{$searchQuery}%");
        }

        $counties = $query->get();

        if ($counties->isEmpty()) {
            return response()->json(['error' => 'No data to export'], 400);
        }

        $filename = 'counties_' . now()->format('Y-m-d_His') . '.pdf';
        $filePath = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Generate PDF using TCPDF or similar
        // For this implementation, we'll use a simple HTML to PDF approach
        // You may want to install a package like barryvdh/laravel-dompdf
        
        $pdf = Pdf::loadView('exports.counties-pdf', ['counties' => $counties]);
        $pdf->save($filePath);

        // Send email
        try {
            Mail::to($request->input('email'))->send(
                new ExportMail('counties', $filePath, $filename)
            );

            // Clean up file after sending
            unlink($filePath);

            return response()->json(['message' => 'Export sent successfully to ' . $request->input('email')]);
        } catch (\Exception $e) {
            // Clean up file on error
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export towns to CSV and send via email
     */
    public function exportTownsCsv(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'query' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $query = Towns::with('county');
        
        if ($request->filled('query')) {
            $searchQuery = $request->input('query');
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('zip_code', 'like', "%{$searchQuery}%")
                  ->orWhereHas('county', function($subQ) use ($searchQuery) {
                      $subQ->where('name', 'like', "%{$searchQuery}%");
                  });
            });
        }

        $towns = $query->get();

        if ($towns->isEmpty()) {
            return response()->json(['error' => 'No data to export'], 400);
        }

        $filename = 'towns_' . now()->format('Y-m-d_His') . '.csv';
        $filePath = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Generate CSV
        $file = fopen($filePath, 'w');
        
        // Add BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header
        fputcsv($file, ['ID', 'Irányítószám', 'Megye', 'Név'], ';');
        
        // Add data
        foreach ($towns as $town) {
            fputcsv($file, [
                $town->id,
                $town->zip_code,
                $town->county?->name ?? '',
                $town->name,
            ], ';');
        }
        
        fclose($file);

        // Send email
        try {
            Mail::to($request->input('email'))->send(
                new ExportMail('towns', $filePath, $filename)
            );

            // Clean up file after sending
            unlink($filePath);

            return response()->json(['message' => 'Export sent successfully to ' . $request->input('email')]);
        } catch (\Exception $e) {
            // Clean up file on error
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export towns to PDF and send via email
     */
    public function exportTownsPdf(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'query' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $query = Towns::with('county');
        
        if ($request->filled('query')) {
            $searchQuery = $request->input('query');
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('zip_code', 'like', "%{$searchQuery}%")
                  ->orWhereHas('county', function($subQ) use ($searchQuery) {
                      $subQ->where('name', 'like', "%{$searchQuery}%");
                  });
            });
        }

        // Limit to 1000 records for PDF to avoid memory issues
        $towns = $query->limit(1000)->get();

        if ($towns->isEmpty()) {
            return response()->json(['error' => 'No data to export'], 400);
        }

        $filename = 'towns_' . now()->format('Y-m-d_His') . '.pdf';
        $filePath = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Generate PDF
        $pdf = Pdf::loadView('exports.towns-pdf', ['towns' => $towns]);
        $pdf->save($filePath);

        // Send email
        try {
            Mail::to($request->input('email'))->send(
                new ExportMail('towns', $filePath, $filename)
            );

            // Clean up file after sending
            unlink($filePath);

            return response()->json(['message' => 'Export sent successfully to ' . $request->input('email')]);
        } catch (\Exception $e) {
            // Clean up file on error
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }
}
