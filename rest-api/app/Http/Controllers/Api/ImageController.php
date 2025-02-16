<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ImageController extends Controller {
    // GET: List all images
    public function index(Request $request): JsonResponse {
        $perPage = $request->query('per_page', 10); // Default 10 items per page
        $images = Image::orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'message' => 'Images retrieved successfully',
            'data' => ImageResource::collection($images),
            'meta' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total()
            ]
        ], 200);
    }

    // POST: Store multiple images
    public function storeOrUpdate(Request $request): JsonResponse {
        try {
            DB::beginTransaction();
            
            // Get all image IDs from the request
            $requestImageIds = collect($request->images)->pluck('id')->filter();
            
            // Fetch existing images in a single query
            $existingImages = Image::whereIn('id', $requestImageIds)
                ->get()
                ->keyBy('id');
            
            $updateBatch = [];
            $processedImages = new Collection();
            
            foreach ($request->images as $img) {
                $imageData = [
                    'id' => $img['id'],
                    'path' => $img['path'],
                    'label' => $img['label'] ?? null,
                    'imageUrl' => $img['imageUrl']
                ];
                
                if ($existingImages->has($img['id'])) {
                    // Update existing image
                    $updateBatch[] = $imageData;
                    $processedImages->push($existingImages->get($img['id']));
                } else {
                    // Create new image using create() to trigger model events
                    $newImage = Image::create($imageData);
                    $processedImages->push($newImage);
                }
            }
            
            // Bulk update existing images
            if (!empty($updateBatch)) {
                foreach ($updateBatch as $item) {
                    Image::where('id', $item['id'])->update($item);
                }
            }
            
            DB::commit();

            return response()->json([
                'message' => 'Images processed successfully',
                'data' => ImageResource::collection($processedImages)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET: Show single image
    public function show(string $id): JsonResponse {
        $image = Image::findOrFail($id);
        return response()->json([
            'message' => 'Images retrieved successfully',
            'data' => new ImageResource($image)
        ], 200);
    }

    // DELETE: Soft delete image
    public function destroy(string $id): JsonResponse {
        try {
            DB::beginTransaction();
            
            $image = Image::findOrFail($id);
            $image->delete();
            
            DB::commit();

            return response()->json(['message' => 'Image deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
