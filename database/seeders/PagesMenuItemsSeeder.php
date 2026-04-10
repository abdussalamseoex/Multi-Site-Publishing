<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Str;

class PagesMenuItemsSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Pages
        $pages = [
            'About Us' => '<p>Welcome to our platform. We are dedicated to providing the best content and guest posting opportunities on the web. Our mission is to bridge the gap between talented authors and authoritative publications.</p><p>We started in 2026 with a vision to streamline publishing. Today, we host thousands of articles across technology, lifestyle, and business.</p>',
            'Privacy Policy' => '<p>Your privacy is important to us. This privacy policy explains what personal data we collect from you and how we use it.</p><h3>Data Collection</h3><p>We collect data to operate effectively and provide you the best experiences with our services.</p><h3>Data Use</h3><p>We use the data to provide the services we offer, which includes updating, securing, and troubleshooting, as well as providing support.</p>',
            'Terms of Service' => '<p>By accessing our website, you are agreeing to be bound by these terms of service, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws.</p><h3>Use License</h3><p>Permission is granted to temporarily download one copy of the materials on our website for personal, non-commercial transitory viewing only.</p>',
            'Contact Us' => '<p>We would love to hear from you!</p><p>Email: support@ourplatform.com</p><p>Phone: +1 (555) 123-4567</p><p>Address: 123 Tech Lane, Silicon Valley, CA</p>'
        ];

        foreach ($pages as $title => $content) {
            Page::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'content' => $content,
                    'meta_title' => $title . ' - Official Page',
                    'meta_description' => substr(strip_tags($content), 0, 150)
                ]
            );
        }

        // 2. Fetch Menus
        $header = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Header Navigation']);
        $footer = Menu::firstOrCreate(['location' => 'footer'], ['name' => 'Footer Navigation']);

        // Clear existing items just in case to prevent duplicates
        $header->items()->delete();
        $footer->items()->delete();

        // 3. Add Items to Header
        $headerItems = [
            ['title' => 'Home', 'url' => '/', 'order' => 1],
            ['title' => 'About Us', 'url' => '/p/about-us', 'order' => 2],
            ['title' => 'Contact', 'url' => '/p/contact-us', 'order' => 3],
        ];

        foreach ($headerItems as $item) {
            $header->items()->create($item);
        }

        // 4. Add Items to Footer
        $footerItems = [
            ['title' => 'Home', 'url' => '/', 'order' => 1],
            ['title' => 'About Us', 'url' => '/p/about-us', 'order' => 2],
            ['title' => 'Privacy Policy', 'url' => '/p/privacy-policy', 'order' => 3],
            ['title' => 'Terms of Service', 'url' => '/p/terms-of-service', 'order' => 4],
            ['title' => 'Contact Us', 'url' => '/p/contact-us', 'order' => 5],
        ];

        foreach ($footerItems as $item) {
            $footer->items()->create($item);
        }
    }
}
