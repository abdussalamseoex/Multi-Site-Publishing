<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.import.wordpress');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml,txt',
        ]);

        $file = $request->file('xml_file');
        
        // Save the file temporarily in storage
        $filename = 'wp_import_' . time() . '.xml';
        $path = $file->storeAs('imports', $filename);

        // Analyze the file to quickly count total items using XMLReader
        $filePath = storage_path('app/' . $path);
        
        $totalItems = 0;
        $reader = new \XMLReader();
        if ($reader->open($filePath)) {
            while ($reader->read()) {
                if ($reader->nodeType == \XMLReader::ELEMENT && $reader->name === 'item') {
                    // Check if it's a post
                    $totalItems++;
                }
            }
            $reader->close();
        } else {
            return response()->json(['error' => 'Unable to read the uploaded XML file.'], 500);
        }

        return response()->json([
            'success' => true,
            'file_path' => $path,
            'total_items' => $totalItems
        ]);
    }

    public function processChunk(Request $request)
    {
        $path = $request->input('file_path');
        $offset = (int) $request->input('offset', 0);
        $chunkSize = 500;
        
        $filePath = storage_path('app/' . $path);
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $reader = new \XMLReader();
        $reader->open($filePath);
        
        $currentIndex = 0;
        $itemsProcessed = 0;
        
        $postsData = [];
        $categoriesMap = Category::pluck('id', 'name')->mapWithKeys(function ($item, $key) {
            return [strtolower($key) => $item];
        })->toArray();
        
        $userId = auth()->id();
        $now = now();

        while ($reader->read()) {
            if ($reader->nodeType == \XMLReader::ELEMENT && $reader->name === 'item') {
                if ($currentIndex >= $offset) {
                    $nodeXml = $reader->readOuterXML();
                    $xml = @simplexml_load_string($nodeXml, 'SimpleXMLElement', LIBXML_NOCDATA);
                    
                    if ($xml) {
                        $namespaces = $xml->getNamespaces(true);
                        $wp = $xml->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
                        
                        $postType = (string) $wp->post_type;
                        $status = (string) $wp->status;
                        
                        // We only want published posts (or draft), but strictly 'post' type, not page/attachment
                        if ($postType === 'post') {
                            $title = (string) $xml->title;
                            $content = (string) ($xml->children('content', true)->encoded ?? $xml->description ?? '');
                            
                            if (empty($content)) {
                                // Fallback standard content tag
                                $content = (string) $xml->content;
                            }
                            
                            $categoryName = 'Uncategorized';
                            foreach ($xml->category as $cat) {
                                $domain = (string) $cat['domain'];
                                if ($domain === 'category') {
                                    $categoryName = (string) $cat;
                                    break;
                                }
                            }
                            
                            // Get or Create Category
                            $catKey = strtolower($categoryName);
                            if (!isset($categoriesMap[$catKey])) {
                                $newCat = Category::create([
                                    'name' => $categoryName,
                                    'slug' => Str::slug($categoryName) . '-' . uniqid(),
                                    'description' => 'Imported category from WordPress.'
                                ]);
                                $categoriesMap[$catKey] = $newCat->id;
                            }
                            
                            $categoryId = $categoriesMap[$catKey];
                            
                            $postsData[] = [
                                'user_id' => $userId,
                                'category_id' => $categoryId,
                                'title' => $title ?: 'Untitled Post',
                                'slug' => Str::slug($title ?: 'untitled-post') . '-' . uniqid(),
                                'content' => $content,
                                'status' => $status === 'publish' ? 'published' : 'draft',
                                'meta_title' => Str::limit(strip_tags($title), 60),
                                'meta_description' => Str::limit(strip_tags($content), 150),
                                'created_at' => (string) $wp->post_date !== '0000-00-00 00:00:00' ? (string) $wp->post_date : $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                    
                    $itemsProcessed++;
                    
                    // Stop parsing once we hit our chunk size
                    if ($itemsProcessed >= $chunkSize) {
                        break;
                    }
                }
                
                $currentIndex++;
            }
        }
        
        $reader->close();
        
        // Batch Insert
        if (!empty($postsData)) {
            Post::insert($postsData);
        }
        
        // Determine if we are completely done
        $isFinished = ($itemsProcessed < $chunkSize);
        if ($isFinished) {
            // Cleanup file
            @unlink($filePath);
        }

        return response()->json([
            'success' => true,
            'processed' => count($postsData), // actual valid posts inserted
            'items_read' => $itemsProcessed, // raw items parsed
            'is_finished' => $isFinished,
            'next_offset' => $offset + $itemsProcessed
        ]);
    }
}
