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
            'upload_file' => 'required|file',
        ]);

        $file = $request->file('upload_file');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, ['xml', 'json', 'csv', 'txt'])) {
            return response()->json(['error' => 'Invalid file format. Only XML, JSON, or CSV are allowed.'], 422);
        }

        // Save the file temporarily in storage
        $filename = 'wp_import_' . time() . '.' . $extension;
        $path = $file->storeAs('imports', $filename);
        $filePath = storage_path('app/' . $path);
        
        $totalItems = 0;
        
        // Analyze the file to quickly count total items
        if ($extension === 'xml' || $extension === 'txt') {
            $reader = new \XMLReader();
            if ($reader->open($filePath)) {
                while ($reader->read()) {
                    if ($reader->nodeType == \XMLReader::ELEMENT && $reader->name === 'item') {
                        $totalItems++;
                    }
                }
                $reader->close();
            }
        } elseif ($extension === 'json') {
            $data = json_decode(file_get_contents($filePath), true);
            if (is_array($data)) {
                $totalItems = count($data);
            }
        } elseif ($extension === 'csv') {
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                while (fgetcsv($handle) !== FALSE) {
                    $totalItems++;
                }
                fclose($handle);
                // remove header row from count
                if ($totalItems > 0) $totalItems--;
            }
        }

        return response()->json([
            'success' => true,
            'file_path' => $path,
            'file_type' => $extension === 'txt' ? 'xml' : $extension,
            'total_items' => $totalItems
        ]);
    }

    public function processChunk(Request $request)
    {
        $path = $request->input('file_path');
        $fileType = $request->input('file_type', 'xml');
        $offset = (int) $request->input('offset', 0);
        $chunkSize = 500;
        
        $filePath = storage_path('app/' . $path);
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found on server.'], 404);
        }

        $postsData = [];
        $categoriesMap = Category::pluck('id', 'name')->mapWithKeys(function ($item, $key) {
            return [strtolower($key) => $item];
        })->toArray();
        
        $userId = auth()->id();
        $now = now();
        $itemsProcessed = 0;

        if ($fileType === 'xml') {
            $reader = new \XMLReader();
            $reader->open($filePath);
            $currentIndex = 0;
            
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
                            
                            if ($postType === 'post') {
                                $title = (string) $xml->title;
                                $content = (string) ($xml->children('content', true)->encoded ?? $xml->description ?? $xml->content ?? '');
                                
                                $categoryName = 'Uncategorized';
                                foreach ($xml->category as $cat) {
                                    $domain = (string) $cat['domain'];
                                    if ($domain === 'category') {
                                        $categoryName = (string) $cat;
                                        break;
                                    }
                                }
                                
                                $catKey = strtolower($categoryName);
                                if (!isset($categoriesMap[$catKey])) {
                                    $newCat = Category::create(['name' => $categoryName, 'slug' => Str::slug($categoryName) . '-' . uniqid()]);
                                    $categoriesMap[$catKey] = $newCat->id;
                                }
                                
                                $postsData[] = [
                                    'user_id' => $userId,
                                    'category_id' => $categoriesMap[$catKey],
                                    'title' => $title ?: 'Untitled',
                                    'slug' => Str::slug($title ?: 'untitled') . '-' . uniqid(),
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
                        if ($itemsProcessed >= $chunkSize) break;
                    }
                    $currentIndex++;
                }
            }
            $reader->close();
            
        } elseif ($fileType === 'json') {
            $data = json_decode(file_get_contents($filePath), true);
            if (is_array($data)) {
                $chunk = array_slice($data, $offset, $chunkSize);
                foreach ($chunk as $item) {
                    // Handle generic WP JSON formats
                    $title = $item['title'] ?? $item['post_title'] ?? 'Untitled';
                    $content = $item['body'] ?? $item['post_content'] ?? $item['content'] ?? '';
                    $categoryName = $item['category'] ?? $item['post_category'] ?? 'Uncategorized';
                    
                    if (is_array($categoryName)) {
                        $categoryName = $categoryName[0] ?? 'Uncategorized'; // take first
                    }

                    $catKey = strtolower($categoryName);
                    if (!isset($categoriesMap[$catKey])) {
                        $newCat = Category::create(['name' => $categoryName, 'slug' => Str::slug($categoryName) . '-' . uniqid()]);
                        $categoriesMap[$catKey] = $newCat->id;
                    }

                    $postsData[] = [
                        'user_id' => $userId,
                        'category_id' => $categoriesMap[$catKey],
                        'title' => $title,
                        'slug' => Str::slug($title) . '-' . uniqid(),
                        'content' => $content,
                        'status' => 'published',
                        'meta_title' => Str::limit(strip_tags($title), 60),
                        'meta_description' => Str::limit(strip_tags($content), 150),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $itemsProcessed++;
                }
            }
        } elseif ($fileType === 'csv') {
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                $headers = fgetcsv($handle);
                $currentIndex = 0;
                
                while (($row = fgetcsv($handle)) !== FALSE) {
                    if ($currentIndex >= $offset) {
                        $item = array_combine($headers, array_pad($row, count($headers), ''));
                        
                        $title = $item['title'] ?? $item['post_title'] ?? $item['Title'] ?? 'Untitled';
                        $content = $item['body'] ?? $item['post_content'] ?? $item['content'] ?? $item['Body'] ?? '';
                        $categoryName = $item['category'] ?? $item['post_category'] ?? $item['Category'] ?? 'Uncategorized';

                        $catKey = strtolower($categoryName);
                        if (!isset($categoriesMap[$catKey])) {
                            $newCat = Category::create(['name' => $categoryName, 'slug' => Str::slug($categoryName) . '-' . uniqid()]);
                            $categoriesMap[$catKey] = $newCat->id;
                        }

                        $postsData[] = [
                            'user_id' => $userId,
                            'category_id' => $categoriesMap[$catKey],
                            'title' => $title,
                            'slug' => Str::slug($title) . '-' . uniqid(),
                            'content' => $content,
                            'status' => 'published',
                            'meta_title' => Str::limit(strip_tags($title), 60),
                            'meta_description' => Str::limit(strip_tags($content), 150),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $itemsProcessed++;
                        if ($itemsProcessed >= $chunkSize) break;
                    }
                    $currentIndex++;
                }
                fclose($handle);
            }
        }
        
        // Batch Insert
        if (!empty($postsData)) {
            Post::insert($postsData);
        }
        
        $isFinished = ($itemsProcessed < $chunkSize);
        if ($isFinished) {
            @unlink($filePath);
        }

        return response()->json([
            'success' => true,
            'processed' => count($postsData),
            'items_read' => $itemsProcessed,
            'is_finished' => $isFinished,
            'next_offset' => $offset + $itemsProcessed
        ]);
    }
}
