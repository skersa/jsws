<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneStories extends Command
{
    protected $signature = 'stories:prune';

    protected $description = 'Süresi dolan (24 saati geçen) hikayeleri ve medya dosyalarını siler';

    public function handle(): int
    {
        $expired = Story::where('expires_at', '<=', now())->get();

        $count = 0;
        foreach ($expired as $story) {
            if ($story->media_path && Storage::disk('public')->exists($story->media_path)) {
                Storage::disk('public')->delete($story->media_path);
            }
            $story->delete();
            $count++;
        }

        $this->info("{$count} adet süresi dolan hikaye silindi.");

        return self::SUCCESS;
    }
}
