<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run()
    {
        $admin = User::first();
        if (!$admin) {
            echo "No admin user found.\n";
            return;
        }

        $allCategories = Category::all();

        // Only create default categories if the database is completely empty
        if ($allCategories->isEmpty()) {
            $categories = [
                'Technology' => 'Latest in tech, gadgets and software.',
                'Business' => 'Finance, markets and corporate news.',
                'Lifestyle' => 'Health, fitness and daily life.',
                'Entertainment' => 'Movies, music and pop culture.',
                'World News' => 'Global events and politics.',
                'Sports' => 'Football, cricket and more.',
            ];

            foreach ($categories as $catName => $desc) {
                Category::create([
                    'name' => $catName,
                    'slug' => Str::slug($catName),
                    'description' => $desc,
                ]);
            }
            
            $allCategories = Category::all();
        }

        $titles = [
            "The Future of AI: How Machine Learning is Changing Everything",
            "Top 10 Gadgets You Need to Try This Year",
            "Market Hits Record Highs Amid Tech Boom",
            "How to Build a Sustainable Startup from Scratch",
            "Healthy Habits for Busy Professionals",
            "The Ultimate Guide to Mindfulness Meditation",
            "Reviewing the Biggest Blockbusters of the Summer",
            "Exclusive Interview with the Rising Star of Indie Music",
            "Global Summit Concludes with Historic Climate Agreement",
            "Elections Approaching: What You Need to Know",
            "Championship Finals: A Historic Victory",
            "The Rise of Esports and Competitive Gaming",
            "Exploring the Hidden Gems of Southeast Asia",
            "Top 5 Travel Destinations for the Upcoming Holidays",
            "The Evolution of Smart Home Technology",
            "Cryptocurrency Trends: What to Expect Next",
            "Nutrition Myths Busted: Eat Better, Live Better",
            "The Art of Minimalist Living",
            "Behind the Scenes of the Award-Winning Drama",
            "Local Policies Impacting Small Businesses",
            "Breakthrough in Renewable Energy Technology",
            "The New Era of Remote Work",
            "Fitness Trends That Are Actually Worth Your Time",
            "A Guide to the Best Coffee Shops in Town",
            "International Trade Agreements Reshaping Markets",
            "The Impact of Social Media on Mental Health",
            "Next-Gen Consoles: Are They Worth the Upgrade?",
            "Investing in Real Estate in a Shifting Market",
            "DIY Home Improvement Projects for Beginners",
            "The Psychology of Productivity",
        ];

        foreach ($titles as $index => $title) {
            $cat = $allCategories->random();
            $randomStr = Str::random(5);
            
            // Random image from picsum, using SEED so the image never changes on reload
            $imagePath = "https://picsum.photos/seed/demo-content-" . ($index + 100) . "/1200/800";

            $content = "<p>This is a randomly generated post intended to showcase the advanced layout of the News theme. It demonstrates how multiple paragraphs of text are rendered properly with the selected typography.</p><p>Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero.</p><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu.</p>";

            Post::create([
                'title' => $title,
                'slug' => Str::slug($title) . '-' . $randomStr,
                'content' => $content,
                'summary' => 'This is a summary for the post about ' . $title,
                'category_id' => $cat->id,
                'user_id' => $admin->id,
                'featured_image' => $imagePath,
                'status' => 'published',
                'is_featured' => ($index < 5) ? true : false,
                'views' => rand(100, 5000),
                'created_at' => now()->subDays(rand(1, 40)),
            ]);
        }

        echo "Created 30 posts successfully.\n";
    }
}
