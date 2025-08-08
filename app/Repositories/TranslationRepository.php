<?php
namespace App\Repositories;

use App\Models\Translation;
use Illuminate\Support\Facades\DB;

class TranslationRepository {

    /**
     * Create a new translation record.
     *
     * @param  array  $data
     * @return Translation
     */
    public function create(array $data): Translation
    {
        $translation = new Translation();
        $translation->key = $data['key'];
        $translation->locale = $data['locale'];
        $translation->value = $data['value'];
        $translation->tag = implode(',', $data['tag'] ?? []);
        $translation->save();

        return $translation;
    
    }

    /**
     * Find a translation by ID and update it.
     */
    public function update(int $id, array $data): Translation
    {
        $translation = Translation::findOrFail($id);
        $translation->key = $data['key'] ?? $translation->key;
        $translation->locale = $data['locale'] ?? $translation->locale;
        $translation->value = $data['value'] ?? $translation->value;
        $translation->tag = isset($data['tag']) ? implode(',', $data['tag']) : $translation->tag;
        $translation->save();

        return $translation;
    }

    public function export() 
    {
            // Disable all buffering for immediate streaming
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    header('X-Accel-Buffering: no'); // Critical for Nginx
    
    // Start JSON output
    echo '[';
    
    $limit = 10000; // Total records
    $chunkSize = 2000; // Larger chunks = fewer queries
    
    // Single optimized query with cursor
    $results = DB::table('translations')
        ->select(['id', 'key', 'value', 'locale', 'created_at', 'updated_at'])
        ->orderBy('id') // MUST be indexed
        ->limit($limit)
        ->cursor(); // Uses PHP generators for memory efficiency
    
    $first = true;
    foreach ($results as $i => $record) {
        if (!$first) echo ',';
        
        echo json_encode([
            'id' => $record->id,
            'key' => $record->key,
            'value' => $record->value,
            'locale' => $record->locale,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $first = false;
        
        // Flush periodically (every 1000 records)
        if ($i % 1000 === 0) {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            flush();
        }
    }
    
    echo ']';

    }
}