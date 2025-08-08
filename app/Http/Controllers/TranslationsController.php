<?php

namespace App\Http\Controllers;

use App\Models\Translations;
use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Http\Resources\TranslationResources;
use App\Models\Translation;
use App\Services\TranslationServices;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

    /**
     * @OA\Info(
     *     title="Translation API",
     *     version="1.0.0"
     * )
     *
     * @OA\Components(
     *     @OA\Schema(
     *         schema="Translation",
     *         type="object",
     *         required={"key", "value", "locale", "group"},
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="key", type="string", example="auth.login"),
     *         @OA\Property(property="value", type="string", example="Please login"),
     *         @OA\Property(property="locale", type="string", example="en"),
     *         @OA\Property(property="created_at", type="string", format="date-time"),
     *         @OA\Property(property="updated_at", type="string", format="date-time")
     *     ),
     *     @OA\Schema(
     *         schema="TranslationResource",
     *         type="object",
     *         @OA\Property(property="data", ref="#/components/schemas/Translation")
     *     ),
     *     @OA\Schema(
     *         schema="ErrorResponse",
     *         type="object",
     *         @OA\Property(property="error", type="string", example="Error message")
     *     )
     * )
     */
    class TranslationsController extends Controller
    {
        private const CHUNK_SIZE = 1000;
        private const MAX_RECORDS = 10000;

        protected TranslationServices $service;

        public function __construct(TranslationServices $service)
        {
            $this->service = $service;
        }
            /**
         * @OA\Post(
         *     path="/api/translations",
         *     tags={"Translations"},
         *     summary="Create a new translation",
         *     description="Stores a new translation record",
         *     operationId="storeTranslation",
         *     @OA\RequestBody(
         *         required=true,
         *         description="Translation data",
         *         @OA\JsonContent(
         *             required={"key", "value", "locale"},
         *             @OA\Property(property="key", type="string", example="auth.login"),
         *             @OA\Property(property="value", type="string", example="Please login"),
         *             @OA\Property(property="locale", type="string", example="en", description="Language code"),
         *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01 12:00:00", readOnly=true),
         *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01 12:00:00", readOnly=true)
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Translation created successfully",
         *         @OA\JsonContent(ref="#/components/schemas/TranslationResource")
         *     ),
         *     @OA\Response(
         *         response=422,
         *         description="Validation error",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="The given data was invalid."),
         *             @OA\Property(
         *                 property="errors",
         *                 type="object",
         *                 @OA\Property(
         *                     property="field_name",
         *                     type="array",
         *                     @OA\Items(type="string", example="The field name is required.")
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Server error",
         *         @OA\JsonContent(
         *             @OA\Property(property="error", type="string", example="Error message")
         *         )
         *     ),
         * )
         */
        public function store(TranslationRequest $request): TranslationResources
        {
            try
            {
                $translation = $this->service->create($request->validated());
                return new TranslationResources($translation);  
            } catch(Exception $e) {
                return response()->json(['error' => $e->getMessage()]);
            }

        }

            /**
         * @OA\Put(
         *     path="/api/translations/update/{id}",
         *     tags={"Translations"},
         *     summary="Update specific translation",
         *     description="Update a single translation by ID",
         *     operationId="updateTranslationById",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         description="ID of translation to return",
         *         required=true,
         *         @OA\Schema(
         *             type="integer",
         *             format="int64"
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Successful operation",
         *         @OA\JsonContent(ref="#/components/schemas/TranslationResource")
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Translation not found",
         *         @OA\JsonContent(
         *             @OA\Property(property="error", type="string", example="Translation not found")
         *         )
         *     )
         * )
         */
        public function update(TranslationRequest $request, int $id,): TranslationResources
        {
            try
            {
                $updated = $this->service->update($id,$request->validated());
                return new TranslationResources($updated);
            } catch(QueryException $qe) {
                return response()->json(['error' => $qe->getMessage()]);
            } catch(Exception $e) {
                return response()->json(['error' => $e->getMessage()]);
            }

        }

        public function show(Translation $translation): TranslationResource
        {
            return new TranslationResources($translation);
        }

                /**
         * @OA\Get(
         *     path="/api/translations",
         *     tags={"Translations"},
         *     summary="List all translations with filtering",
         *     description="Returns a paginated list of translations with optional filtering",
         *     operationId="getTranslations",
         *     @OA\Parameter(
         *         name="key",
         *         in="query",
         *         description="Filter by translation key (partial match)",
         *         required=false,
         *         @OA\Schema(type="string")
         *     ),
         *     @OA\Parameter(
         *         name="locale",
         *         in="query",
         *         description="Filter by locale code (exact match)",
         *         required=false,
         *         @OA\Schema(type="string")
         *     ),
         *     @OA\Parameter(
         *         name="tag",
         *         in="query",
         *         description="Filter by tag (partial match)",
         *         required=false,
         *         @OA\Schema(type="string")
         *     ),
         *     @OA\Parameter(
         *         name="page",
         *         in="query",
         *         description="Page number for pagination",
         *         required=false,
         *         @OA\Schema(type="integer", default=1)
         *     ),
         *     @OA\Parameter(
         *         name="per_page",
         *         in="query",
         *         description="Number of items per page",
         *         required=false,
         *         @OA\Schema(type="integer", default=15)
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Successful operation",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(
         *                 property="data",
         *                 type="array",
         *                 @OA\Items(ref="#/components/schemas/Translation")
         *             ),
         *             @OA\Property(
         *                 property="links",
         *                 type="object",
         *                 @OA\Property(property="first", type="string"),
         *                 @OA\Property(property="last", type="string"),
         *                 @OA\Property(property="prev", type="string"),
         *                 @OA\Property(property="next", type="string")
         *             ),
         *             @OA\Property(
         *                 property="meta",
         *                 type="object",
         *                 @OA\Property(property="current_page", type="integer"),
         *                 @OA\Property(property="from", type="integer"),
         *                 @OA\Property(property="last_page", type="integer"),
         *                 @OA\Property(property="path", type="string"),
         *                 @OA\Property(property="per_page", type="integer"),
         *                 @OA\Property(property="to", type="integer"),
         *                 @OA\Property(property="total", type="integer")
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Server error",
         *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
         *     ),
         *     security={
         *         {"bearerAuth": {}}
         *     }
         * )
         */
        public function index(Request $request)
        {
            $query = Translation::query();

            if ($request->filled('key')) {
                $query->where('key', 'like', '%' . $request->input('key') . '%');
            }

            if ($request->filled('locale')) {
                $query->where('locale', $request->input('locale'));
            }

            if ($request->filled('tag')) {
                $query->where('tags', 'like', '%' . $request->input('tag') . '%');
            }

            return TranslationResources::collection($query->paginate());
        }
                /**
         * @OA\Delete(
         *     path="/api/translations/delete/{id}",
         *     tags={"Translations"},
         *     summary="Delete a translation",
         *     description="Deletes a specific translation record",
         *     operationId="deleteTranslation",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         description="ID of translation to delete",
         *         required=true,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Successful deletion",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Translation deleted successfully")
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Translation not found",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Translation not found")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Server error",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Could not delete translation")
         *         )
         *     ),
         *     security={
         *         {"bearerAuth": {}}
         *     }
         * )
         */
        public function destroy($id)
        {
            try {
                $translation = Translation::findOrFail($id);
                $translation->delete();
                
                return response()->json([
                    'message' => 'Translation deleted successfully'
                ]);
                
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'message' => 'Translation not found'
                ], 404);
                
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Could not delete translation'
                ], 500);
            }
        }

            /**
         * @OA\Get(
         *     path="/api/translations/export",
         *     summary="Export translations as JSON",
         *     description="Streams all translations in JSON format for frontend apps like Vue.js. Handles large datasets efficiently using chunked output.",
         *     tags={"Translations"},
         *     @OA\Response(
         *         response=200,
         *         description="JSON stream of translations",
         *         @OA\MediaType(
         *             mediaType="application/json",
         *         )
         *     )
         * )
         */
        public function export(): StreamedResponse
        {
            return new StreamedResponse(
                function () {
                    $this->streamJsonResponse();
                },
                200,
                [
                    'Content-Type' => 'application/json',
                    'X-Accel-Buffering' => 'no',
                ]
            );
        }

        private function streamJsonResponse(): void
        {
            $this->cleanOutputBuffers();
            echo '[';
            
            $first = true;
            $records = DB::table('translations')
                ->select(['id', 'key', 'value', 'locale', 'created_at', 'updated_at'])
                ->orderBy('id')
                ->limit(self::MAX_RECORDS)
                ->cursor();

            foreach ($records as $i => $record) {
                if (!$first) {
                    echo ',';
                }

                echo json_encode([
                    'id' => $record->id,
                    'key' => $record->key,
                    'value' => $record->value,
                    'locale' => $record->locale,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $first = false;

                if ($i % self::CHUNK_SIZE === 0) {
                    $this->flushOutput();
                }
            }

        echo ']';
        }

        private function cleanOutputBuffers(): void
        {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        private function flushOutput(): void
        {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            flush();
        }

    }
