<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('theme:reset-layouts')]
#[Description('Deletes all theme block and sidebar settings from the database to reset themes to their default modular layouts')]
class ResetThemeLayouts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting theme layout reset...");

        $deletedBlocks = DB::table('settings')->where('key', 'like', 'theme_blocks_%')->delete();
        $this->info("Deleted {$deletedBlocks} theme block layout settings.");

        $deletedSidebars = DB::table('settings')->where('key', 'like', 'theme_sidebar_%')->delete();
        $this->info("Deleted {$deletedSidebars} theme sidebar layout settings.");

        $this->info("All theme layouts have been reset to their defaults.");
        
        return Command::SUCCESS;
    }
}
