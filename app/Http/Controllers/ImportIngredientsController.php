<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ingredient;
use App\Models\Supplier;

class ImportIngredientsController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);
    
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Open the file
        $handle = fopen($path, 'r');
        
        // Variables to track import results
        $result = [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'error_details' => [] // Store specific error messages
        ];
        
        // Map header names to database field names (with alternatives)
        $headerMapping = [
            'FURNIZOR' => 'supplier_name',
            'SUPPLIER' => 'supplier_name',
            'INGREDIENTE' => 'name',
            'INGREDIENT' => 'name',
            'NAME' => 'name',
            'INCI' => 'inci',
            'MQO' => 'moq',
            'MOQ' => 'moq',
            'LEI' => 'price',
            'PRICE' => 'price',
            'FUNCTIE' => 'description',
            'FUNCTION' => 'description',
            'DESCRIPTION' => 'description',
            'MOSTRA DISPONIBILA' => 'is_sample',
            'SAMPLE AVAILABLE' => 'is_sample'
        ];
        
        // Skip the first row (header)
        $headers = fgetcsv($handle);
        
        // If no headers found, return error
        if (!$headers) {
            fclose($handle);
            return response()->json(['error' => 'Invalid CSV format or empty file'], 400);
        }
        
        $mappedHeaders = [];
        
        // Map the headers from the file to our expected fields
        foreach ($headers as $index => $header) {
            $header = trim($header);
            if (isset($headerMapping[$header])) {
                $mappedHeaders[$index] = $headerMapping[$header];
            }
        }
        
        // Check if we have at least name column mapped
        if (!in_array('name', $mappedHeaders)) {
            fclose($handle);
            return response()->json([
                'error' => 'CSV file must contain an ingredient name column (INGREDIENTE, INGREDIENT or NAME)'
            ], 400);
        }
        
        // Process the data rows
        DB::beginTransaction();
        
        // For batch processing
        $batchSize = 100;
        $ingredientsBatch = [];
        
        try {
            $rowNumber = 1; // Start after header
            $currentSupplier = null; // Variable to remember the current supplier
            
            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $result['total']++;
                
                // Skip empty rows
                if (count(array_filter($data)) === 0) {
                    continue;
                }
                
                $ingredientData = [];
                $supplierName = null;
                $rowErrors = [];
                
                // Map CSV data to ingredient fields
                foreach ($data as $index => $value) {
                    if (!isset($mappedHeaders[$index])) {
                        continue;
                    }
                    
                    $field = $mappedHeaders[$index];
                    $value = trim($value);
                    
                    if ($field === 'supplier_name') {
                        // If we have a supplier name, update current supplier
                        if (!empty($value)) {
                            $currentSupplier = $value;
                            $supplierName = $value;
                        } else {
                            // Use the previously stored supplier name
                            $supplierName = $currentSupplier;
                        }
                    } elseif ($field === 'is_sample') {
                        // Handle various possible values
                        $lowerValue = strtolower($value);
                        if (in_array($lowerValue, ['da', 'yes', '1', 'true'])) {
                            $ingredientData[$field] = true;
                        } elseif (in_array($lowerValue, ['nu', 'no', '0', 'false'])) {
                            $ingredientData[$field] = false;
                        } else {
                            $ingredientData[$field] = false;
                            if (!empty($value)) {
                                $rowErrors[] = "Row {$rowNumber}: Invalid sample value '{$value}'. Using 'false' as default.";
                            }
                        }
                    } elseif ($field === 'moq' || $field === 'price') {
                        // Convert comma to dot for decimal numbers and remove any non-numeric chars
                        $cleanValue = str_replace(',', '.', $value);
                        $cleanValue = preg_replace('/[^0-9.]/', '', $cleanValue);
                        
                        if ($cleanValue === '' || !is_numeric($cleanValue)) {
                            $ingredientData[$field] = null;
                            if (!empty($value)) {
                                $rowErrors[] = "Row {$rowNumber}: Invalid {$field} value '{$value}'. Using null as default.";
                            }
                        } else {
                            $ingredientData[$field] = (float)$cleanValue;
                        }
                    } else {
                        $ingredientData[$field] = $value ?: null;
                    }
                }
                
                // Only process rows with at least a name
                if (empty($ingredientData['name'])) {
                    $rowErrors[] = "Row {$rowNumber}: Missing ingredient name";
                    $result['errors']++;
                    if (!empty($rowErrors)) {
                        $result['error_details'][] = $rowErrors;
                    }
                    continue;
                }
                
                // Set defaults for required fields
                $ingredientData['in_stock'] = $ingredientData['is_sample'] ?? false;
                
                // Find or create supplier
                $supplier_id = null;
                if ($supplierName) {
                    try {
                        $supplier = Supplier::firstOrCreate(
                            ['name' => $supplierName],
                            ['contact_person' => '', 'phone_number' => '']
                        );
                        $supplier_id = $supplier->id;
                    } catch (\Exception $e) {
                        $rowErrors[] = "Row {$rowNumber}: Error creating supplier '{$supplierName}': " . $e->getMessage();
                    }
                }
                $ingredientData['supplier_id'] = $supplier_id;
                
                // Add to batch or create individually
                try {
                    // For smaller imports, create individually
                    if ($result['total'] <= 50) {
                        Ingredient::create($ingredientData);
                    } else {
                        // For larger imports, use batch insertion
                        $ingredientsBatch[] = $ingredientData;
                        
                        if (count($ingredientsBatch) >= $batchSize) {
                            Ingredient::insert($ingredientsBatch);
                            $ingredientsBatch = [];
                        }
                    }
                    
                    $result['success']++;
                } catch (\Exception $e) {
                    $errorMessage = "Row {$rowNumber}: Error importing ingredient: " . $e->getMessage();
                    Log::error($errorMessage);
                    $rowErrors[] = $errorMessage;
                    $result['errors']++;
                }
                
                if (!empty($rowErrors)) {
                    $result['error_details'][] = $rowErrors;
                }
            }
            
            // Insert any remaining batch items
            if (!empty($ingredientsBatch)) {
                Ingredient::insert($ingredientsBatch);
            }
            
            DB::commit();
            
            // Cap the number of detailed errors to prevent excessive response size
            if (count($result['error_details']) > 50) {
                $result['error_details'] = array_slice($result['error_details'], 0, 50);
                $result['error_details'][] = ["... and " . (count($result['error_details']) - 50) . " more errors (see logs for details)"];
            }
            
            return response()->json([
                'result' => $result,
                'message' => "Imported {$result['success']} ingredients with {$result['errors']} errors"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV import failed: ' . $e->getMessage());
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        } finally {
            fclose($handle);
        }
    }
}
