<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with(['parent'])->withCount('posts');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = $request->input('sort', 'latest');
        if ($sort == 'latest') {
            $query->latest();
        } elseif ($sort == 'oldest') {
            $query->oldest();
        } elseif ($sort == 'posts_count') {
            $query->orderBy('posts_count', 'desc');
        }

        $categories = $query->get();
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'description' => $request->description,
            'meta_title' => $request->meta_title ?? $request->name,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
        ]);

        return back()->with('status', 'Category created successfully.');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'string',
            'import_type' => 'required|string|in:category_only,category_content'
        ]);

        $count = 0;
        $postCount = 0;
        $importType = $request->input('import_type', 'category_only');

        foreach ($request->input('categories') as $catName) {
            $slug = Str::slug($catName);
            // Check if it already exists to prevent duplicate slugs
            if (!Category::where('slug', $slug)->exists()) {
                $category = Category::create([
                    'name' => $catName,
                    'slug' => $slug,
                    'description' => 'Comprehensive discussions and expert insights revolving around ' . strtolower($catName) . ' trends and best practices.',
                ]);
                $count++;

                if ($importType === 'category_content') {
                    // Generate 3 high-quality dummy posts for this new category
                    for ($i = 1; $i <= 3; $i++) {
                        $titles = [
                            "The Ultimate Guide to Understanding $catName in 2026",
                            "Why $catName is Transforming the Global Industry Landscape",
                            "Top 10 Hidden Secrets About $catName You Need to Know",
                            "A Comprehensive Analysis: The Future of $catName",
                            "Expert Insights: Navigating the Complex World of $catName"
                        ];
                        $postTitle = $titles[array_rand($titles)];
                        
                        $img1 = 'https://picsum.photos/seed/' . rand(1, 99999) . '/800/400';
                        $img2 = 'https://picsum.photos/seed/' . rand(1, 99999) . '/800/400';
                        
                        $richContent = "
                            <p class='lead' style='font-size: 1.125rem; font-weight: 500; color: #4a5568; margin-bottom: 2rem; line-height: 1.75;'>Welcome to our in-depth exploration of <strong>$catName</strong>. In an era defined by rapid technological advancement and shifting consumer expectations, understanding the nuances of this sector is more critical than ever. This comprehensive article breaks down everything you need to know to stay ahead of the curve.</p>
                            
                            <h2 style='font-size: 1.875rem; font-weight: 700; margin-top: 2.5rem; margin-bottom: 1.25rem; color: #1a202c;'>1. The Evolution of $catName</h2>
                            <p style='margin-bottom: 1.5rem; line-height: 1.75; color: #4a5568;'>The journey of $catName over the past decade has been nothing short of revolutionary. Initially starting as a niche concept, it has now permeated mainstream markets, fundamentally altering how professionals and consumers interact with modern infrastructure. According to recent industry reports, the compound annual growth rate (CAGR) for this sector is projected to hit double digits by the end of the fiscal year.</p>
                            <p style='margin-bottom: 1.5rem; line-height: 1.75; color: #4a5568;'>One of the primary drivers of this exponential growth is the democratization of data and tools. Previously restricted to enterprise-level organizations, the foundational technologies supporting $catName are now accessible to startups and individual creators. This shift has triggered a massive wave of innovation, resulting in more agile, cost-effective, and scalable solutions.</p>
                            
                            <figure style='margin: 2.5rem 0;'>
                                <img src='$img1' alt='$catName visualization' style='width: 100%; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);'>
                                <figcaption style='text-align: center; color: #718096; font-size: 0.875rem; margin-top: 0.75rem;'>Figure 1: Global adoption rates and strategic implementation of $catName.</figcaption>
                            </figure>

                            <h2 style='font-size: 1.875rem; font-weight: 700; margin-top: 2.5rem; margin-bottom: 1.25rem; color: #1a202c;'>2. Key Challenges and Opportunities</h2>
                            <p style='margin-bottom: 1.5rem; line-height: 1.75; color: #4a5568;'>Despite the overwhelmingly positive trajectory, integrating $catName into legacy systems presents several hurdles. Organizations often struggle with data silos, resistance to cultural change, and the technical debt accumulated over years of fragmented development. However, these challenges also represent massive opportunities for disruptive innovation.</p>
                            
                            <h3 style='font-size: 1.5rem; font-weight: 600; margin-top: 2rem; margin-bottom: 1rem; color: #2d3748;'>Strategic Benefits</h3>
                            <ul style='margin-bottom: 2rem; padding-left: 1.5rem; list-style-type: disc; color: #4a5568; line-height: 1.75;'>
                                <li style='margin-bottom: 0.75rem;'><strong>Enhanced Efficiency:</strong> Streamlining operational workflows and reducing redundant tasks across departments.</li>
                                <li style='margin-bottom: 0.75rem;'><strong>Scalability:</strong> The ability to seamlessly scale resources up or down based on real-time market demand.</li>
                                <li style='margin-bottom: 0.75rem;'><strong>Data-Driven Decision Making:</strong> Leveraging deep analytics and behavioral insights to inform long-term strategic initiatives.</li>
                                <li style='margin-bottom: 0.75rem;'><strong>Competitive Advantage:</strong> Early adopters of these methodologies consistently outperform their market laggards in both revenue and customer retention.</li>
                            </ul>

                            <h2 style='font-size: 1.875rem; font-weight: 700; margin-top: 2.5rem; margin-bottom: 1.25rem; color: #1a202c;'>3. Looking Towards the Future</h2>
                            <p style='margin-bottom: 1.5rem; line-height: 1.75; color: #4a5568;'>As we look towards the next five years, the intersection of $catName with artificial intelligence and machine learning will likely be the primary catalyst for the next paradigm shift. Predictive modeling and automated workflows will reduce the friction associated with manual oversight, allowing human operators to focus on high-level strategic planning rather than repetitive administrative duties.</p>
                            
                            <figure style='margin: 2.5rem 0;'>
                                <img src='$img2' alt='Future of $catName' style='width: 100%; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);'>
                            </figure>

                            <p style='margin-bottom: 1.5rem; line-height: 1.75; color: #4a5568;'>Furthermore, regulatory frameworks are beginning to adapt to these new realities. As governance models mature, we can expect greater standardization, which will ironically accelerate innovation by providing clear guardrails for developers and enterprises alike.</p>
                            
                            <h2 style='font-size: 1.875rem; font-weight: 700; margin-top: 2.5rem; margin-bottom: 1.25rem; color: #1a202c;'>Conclusion</h2>
                            <p style='margin-bottom: 1.5rem; line-height: 1.75; color: #4a5568;'>In conclusion, $catName is not just a passing trend—it is a fundamental restructuring of the industry's underlying architecture. Whether you are a seasoned professional or an enthusiastic newcomer, staying informed about these developments is essential for long-term success. By embracing agility, investing in continuous learning, and fostering a culture of innovation, organizations can fully capitalize on the incredible potential that lies ahead.</p>
                            
                            <div style='background-color: #f7fafc; border-left: 4px solid #4299e1; padding: 1.25rem; margin-top: 2rem; border-radius: 0 0.5rem 0.5rem 0;'>
                                <p style='margin: 0; color: #718096; font-size: 0.875rem; font-style: italic;'>Disclaimer: This is a comprehensive premium mock article intended for demonstration purposes. The statistics, data, and trends discussed herein are utilized for illustrative purposes to showcase high-fidelity, SEO-friendly content formatting.</p>
                            </div>
                        ";

                        \App\Models\Post::create([
                            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                            'category_id' => $category->id,
                            'title' => $postTitle,
                            'slug' => Str::slug($postTitle) . '-' . rand(10000, 99999),
                            'summary' => "A comprehensive deep-dive into $catName, covering its evolution, key challenges, strategic benefits, and future outlook in today's rapidly changing environment.",
                            'content' => $richContent,
                            'featured_image' => 'https://picsum.photos/seed/' . rand(1, 99999) . '/1200/630',
                            'status' => 'published',
                            'views' => rand(100, 5000),
                        ]);
                        $postCount++;
                    }
                }
            }
        }

        $msg = "Successfully imported $count new categories.";
        if ($importType === 'category_content') {
            $msg .= " Generated $postCount high-quality demo posts.";
        }

        return back()->with('status', $msg);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('status', 'Category deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        Category::whereIn('id', $request->categories)->delete();

        return back()->with('status', count($request->categories) . ' categories deleted successfully.');
    }
}
