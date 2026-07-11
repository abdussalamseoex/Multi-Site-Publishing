<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->index();
            }
        });

        // Backfill usernames for existing users
        if (Schema::hasColumn('users', 'username')) {
            $users = \App\Models\User::all();
            foreach ($users as $user) {
                if (empty($user->username)) {
                    $slug = Str::slug($user->name ?: 'user-' . $user->id);
                    $original = $slug;
                    $counter = 1;
                    while (\App\Models\User::where('username', $slug)->where('id', '!=', $user->id)->exists()) {
                        $slug = $original . '-' . $counter++;
                    }
                    $user->username = $slug;
                    $user->saveQuietly();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
