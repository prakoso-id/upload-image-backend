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
    public function index(): JsonResponse {
        return response()->json(ImageResource::collection(Image::all()));
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
            $createBatch = [];
            $processedImages = new Collection();
            
            foreach ($request->images as $img) {
                $imageData = [
                    'id' => $img['id'],
                    'path' => $img['path'],
                    'label' => $img['label'] ?? null,
                    'imageUrl' => $img['imageUrl'],
                    'updated_at' => now(),
                ];
                
                if ($existingImages->has($img['id'])) {
                    // Update existing image
                    $updateBatch[] = $imageData;
                    $processedImages->push($existingImages->get($img['id']));
                } else {
                    $createBatch[] = $imageData;
                    $processedImages->push(new Image($imageData));
                }
            }
            
            // Bulk update existing images
            if (!empty($updateBatch)) {
                foreach ($updateBatch as $item) {
                    Image::where('id', $item['id'])->update($item);
                }
            }
            
            // Bulk create new images
            if (!empty($createBatch)) {
                Image::insert($createBatch);
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
        return response()->json(new ImageResource($image));
    }

    // DELETE: Soft delete image
    public function destroy(string $id): JsonResponse {
        try {
            DB::beginTransaction();
            
            $image = Image::findOrFail($id);
            Storage::delete($image->path);
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
