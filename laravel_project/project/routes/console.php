<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Süresi dolan hikayeleri her saat otomatik temizle (24 saat sonra silinir)
Schedule::command('stories:prune')->hourly();

// Süresi dolan açık artırmaları her dakika kapat + kazananı belirle
Schedule::command('auctions:close')->everyMinute();

// Kargolanmış ama onaylanmamış siparişleri süre dolunca otomatik tamamla (7 gün)
Schedule::command('orders:auto-release')->hourly();
