<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete the static physical robots.txt to ensure dynamic routing takes over
        if (File::exists(public_path('robots.txt'))) {
            File::delete(public_path('robots.txt'));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
