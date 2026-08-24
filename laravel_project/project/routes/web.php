<?php

use App\Http\Controllers\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Front\BrowseController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\General\BalanceController;
use App\Http\Controllers\General\BidController;
use App\Http\Controllers\General\ChatController;
use App\Http\Controllers\General\FollowController;
use App\Http\Controllers\General\HomeController;
use App\Http\Controllers\General\MessageController;
use App\Http\Controllers\General\ReviewController;
use App\Http\Controllers\General\StoryController;
use App\Http\Controllers\General\NotificationController;
use App\Http\Controllers\General\OrderController;
use App\Http\Controllers\General\ProfileController;
use App\Http\Controllers\General\SearchController;
use App\Http\Controllers\General\SupportController;
use App\Http\Controllers\LiveKitTokenController;
use App\Http\Controllers\Seller\AuctionController as SellerAuctionController;
use App\Http\Controllers\Seller\BroadcastController;
use App\Http\Controllers\Seller\SaleController as SellerSaleController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboard;
use App\Http\Controllers\Seller\ProfileController as SellerProfileController;
use Illuminate\Support\Facades\Route;

/*
|==========================================================================
| PUBLIC ROUTES
|==========================================================================
*/

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/live-search', [SearchController::class, 'search'])->name('live.search');

Route::controller(BrowseController::class)->prefix('browse')->name('browse.')->group(function () {
    Route::get('/auctions', 'auctions')->name('auctions');
    Route::get('/live', 'live')->name('live');
    Route::get('/explore', 'explore')->name('explore');
});

Route::get('/auctions/{auction:slug}', [BidController::class, 'show'])->name('auctions.show');
Route::get('/auctions/{auction:slug}/live-state', [BidController::class, 'liveState'])->name('auctions.live-state');
Route::get('/auctions/{auction:slug}/chat', [ChatController::class, 'poll'])->name('auctions.chat.poll');

// LiveKit (WebRTC SFU) katılım token'ı — yayıncı/izleyici. Public: izleyici misafir de olabilir.
Route::post('/livekit/token', LiveKitTokenController::class)
    ->middleware('throttle:60,1')->name('livekit.token');
Route::get('/u/{username}', [ProfileController::class, 'show'])
    ->where('username', '[a-z0-9._]+')
    ->name('profile.public');

/*
| Kurumsal Sayfalar
*/
Route::controller(PageController::class)->group(function () {
    Route::get('/corporate', 'corporate')->name('corporate');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSend')->name('contact.send');
    Route::get('/privacy-policy', 'privacy_policy')->name('privacy');
});

/*
|==========================================================================
| AUTH REQUIRED
|==========================================================================
*/

Route::middleware('auth')->group(function () {

    /*
    | E-posta Doğrulama
    */
    Route::get('/email/verify', function () {
        return auth()->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : \Inertia\Inertia::render('Auth/VerifyEmail', [
                'status' => session('status'),
                'activeAuctions' => \App\Models\Auction::where('status', 'active')->count(),
            ]);
    })->name('verification.notice');

    /*
    | Teklif
    */
    Route::post('/auctions/{auction:slug}/bid', [BidController::class, 'store'])->name('bids.store');

    /*
    | Canlı yayın sohbeti (Twitch tarzı — spam korumalı)
    */
    Route::post('/auctions/{auction:slug}/chat', [ChatController::class, 'store'])
        ->middleware('throttle:20,1')->name('auctions.chat.store');

    /*
    | Mesajlaşma (birebir sohbet)
    */
    Route::prefix('messages')->name('messages.')->controller(MessageController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/start', 'start')->middleware('throttle:30,1')->name('start');
        Route::get('/{conversation}', 'show')->name('show');
        Route::post('/{conversation}', 'store')->middleware('throttle:60,1')->name('store');
        Route::get('/{conversation}/poll', 'poll')->name('poll');
    });

    /*
    | Satıcı puanlama
    */
    Route::post('/u/{user:username}/review', [ReviewController::class, 'store'])->name('reviews.store');

    /*
    | Hikayeler (24 saatlik story)
    */
    Route::post('/stories', [StoryController::class, 'store'])->middleware('throttle:20,1')->name('stories.store');
    Route::delete('/stories/{story}', [StoryController::class, 'destroy'])->name('stories.destroy');

});

/*
|==========================================================================
| AUTH + VERIFIED ROUTES
|==========================================================================
*/

Route::middleware(['auth', 'verified.account'])->group(function () {

    Route::get('/dashboard', function () {
        $u = auth()->user();
        if ($u->isAdmin())  return redirect()->route('admin.dashboard');
        if ($u->isSeller()) return redirect()->route('seller.dashboard');

        $myBids = $u->bids()->with(['auction.cover', 'auction.category'])->latest()->take(6)->get();
        $watchItems = $u->watchlist()->with('cover')->latest()->take(4)->get();

        return \Inertia\Inertia::render('Dashboard', [
            'user' => ['name' => $u->name],
            'stats' => [
                'balance'      => (float) ($u->balance ?? 0),
                'active_bids'  => $u->bids()->whereHas('auction', fn ($q) => $q->where('status', 'active'))->distinct('auction_id')->count('auction_id'),
                'fav_count'    => $u->watchlist()->count(),
                'won_count'    => $u->purchases()->count(),
            ],
            'myBids'     => $myBids->map(fn ($b) => bid_row($b))->values(),
            'watchItems' => $watchItems->map(fn ($w) => [
                'title' => \Illuminate\Support\Str::limit($w->title, 28),
                'cover_url' => $w->coverUrl(),
                'display_price' => $w->displayPrice(),
                'show_url' => route('auctions.show', $w->slug),
            ])->values(),
        ]);
    })->name('dashboard');

    Route::get('/my-bids', function () {
        $bids = auth()->user()->bids()
            ->with(['auction.cover', 'auction.category'])
            ->latest()->paginate(20);
        return \Inertia\Inertia::render('Buyer/MyBids', [
            'bids' => [
                'data'  => collect($bids->items())->map(fn ($b) => bid_row($b))->values(),
                'links' => $bids->linkCollection()->toArray(),
                'has_pages' => $bids->hasPages(),
                'total' => $bids->total(),
            ],
        ]);
    })->name('my-bids');

    Route::get('/favorites', function () {
        $items = auth()->user()->watchlist()->with('cover')->latest()->paginate(20);
        return \Inertia\Inertia::render('Buyer/Favorites', [
            'items' => [
                'data'  => collect($items->items())->map(fn ($w) => [
                    'title' => \Illuminate\Support\Str::limit($w->title, 32),
                    'cover_url' => $w->coverUrl(),
                    'display_price' => $w->displayPrice(),
                    'show_url' => route('auctions.show', $w->slug),
                ])->values(),
                'links' => $items->linkCollection()->toArray(),
                'has_pages' => $items->hasPages(),
                'total' => $items->total(),
            ],
        ]);
    })->name('favorites');

    /*
    | Alıcı: Siparişlerim (satın alma sonrası akış)
    */
    Route::prefix('orders')->name('orders.')->controller(OrderController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{order}', 'show')->name('show');
        Route::post('/{order}/pay', 'pay')->name('pay');
        Route::post('/{order}/address', 'address')->name('address');
        Route::post('/{order}/confirm', 'confirm')->name('confirm');
        Route::post('/{order}/dispute', 'dispute')->name('dispute');
    });

    /*
    |--------------------------------------------------------------------------
    | Profil
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
        Route::put('email', 'email')->name('email');
        Route::put('password', 'password')->name('password');
        Route::put('privacy', 'privacy')->name('privacy');
        Route::put('social', 'social')->name('social');
    });

    /*
    | Takip
    */
    Route::post('/follow/{user}', [FollowController::class, 'toggle'])->name('follow.toggle');
    Route::prefix('/u/{username}')->name('profile.')->group(function () {
        Route::get('followers', [FollowController::class, 'followers'])->name('followers');
        Route::get('following', [FollowController::class, 'following'])->name('following');
    });

    /*
    |--------------------------------------------------------------------------
    | Bildirimler
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('{id}/read', 'markAsRead')->name('read');
        Route::post('read-all', 'markAllRead')->name('readAll');
    });

    /*
    |--------------------------------------------------------------------------
    | Destek
    |--------------------------------------------------------------------------
    */
    Route::prefix('support')->name('support.')->controller(SupportController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{ticket}', 'show')->name('show');
        Route::post('/{ticket}/reply', 'reply')->name('reply');
        Route::post('/{ticket}/close', 'close')->name('close');
    });

    /*
    |==========================================================================
    | SATICI PANELİ
    |==========================================================================
    */
    Route::middleware('role:seller')->prefix('seller')->name('seller.')->group(function () {

        Route::get('dashboard', [SellerDashboard::class, 'index'])->name('dashboard');

        Route::get('auctions/{auction:slug}/broadcast', [BroadcastController::class, 'show'])
            ->name('auctions.broadcast');

        Route::post('auctions/{auction:slug}/sell', [BroadcastController::class, 'sell'])
            ->name('auctions.sell');

        Route::post('auctions/{auction:slug}/end-broadcast', [BroadcastController::class, 'endBroadcast'])
            ->name('auctions.end-broadcast');

        Route::post('auctions/{auction:slug}/stream-settings', [BroadcastController::class, 'streamSettings'])
            ->name('auctions.stream-settings');

        Route::post('auctions/{auction:slug}/live-status', [BroadcastController::class, 'liveStatus'])
            ->name('auctions.live-status');

        /*
        | Satışlarım (sipariş yönetimi + kargo)
        */
        Route::prefix('sales')->name('sales.')->controller(SellerSaleController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{order}', 'show')->name('show');
            Route::post('/{order}/ship', 'ship')->name('ship');
        });

        /*
        | İlanlar
        */
        Route::prefix('auctions')->name('auctions.')->controller(SellerAuctionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{auction:slug}', 'show')->name('show');
            Route::get('{auction:slug}/edit', 'edit')->name('edit');
            Route::put('{auction:slug}', 'update')->name('update');
            Route::delete('{auction:slug}', 'destroy')->name('destroy');
        });

        /*
        | Profil
        */
        Route::prefix('profile')->name('profile.')->controller(SellerProfileController::class)->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::put('{section}', 'update')->name('update');
            Route::post('document', 'uploadDocument')->name('document.upload');
        });

    });

    /*
    |==========================================================================
    | ADMİN PANELİ
    |==========================================================================
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        /*
        | Siparişler & Anlaşmazlıklar
        */
        Route::prefix('orders')->name('orders.')->controller(AdminOrderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{order}', 'show')->name('show');
            Route::post('/{order}/resolve', 'resolve')->name('resolve');
        });

        /*
        | Kullanıcılar
        */
        Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{user}', 'show')->name('show');
            Route::get('{user}/edit', 'edit')->name('edit');
            Route::put('{user}', 'update')->name('update');
            Route::delete('{user}', 'destroy')->name('destroy');
            Route::post('{user}/verify', 'verify')->name('verify');
            Route::post('{user}/unverify', 'unverify')->name('unverify');
        });

        /*
        | Kategoriler
        */
        Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{category}', 'show')->name('show');
            Route::get('{category}/edit', 'edit')->name('edit');
            Route::put('{category}', 'update')->name('update');
            Route::delete('{category}', 'destroy')->name('destroy');
            Route::post('{category}/toggle', 'toggle')->name('toggle');
            Route::post('reorder', 'reorder')->name('reorder');
        });

        /*
        | İlanlar
        */
        Route::prefix('auctions')->name('auctions.')->controller(AdminAuctionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{auction}', 'show')->name('show');
            Route::get('{auction}/edit', 'edit')->name('edit');
            Route::put('{auction}', 'update')->name('update');
            Route::delete('{auction}', 'destroy')->name('destroy');
            Route::post('{auction}/approve', 'approve')->name('approve');
            Route::post('{auction}/reject', 'reject')->name('reject');
        });

        /*
        | Destek
        */
        Route::prefix('support')->name('support.')->controller(AdminSupportController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{ticket}', 'show')->name('show');
            Route::post('/{ticket}/reply', 'reply')->name('reply');
            Route::patch('/{ticket}/status', 'updateStatus')->name('status');
        });

        /*
        | Ayarlar
        */
        Route::prefix('settings')->name('settings.')->controller(SettingsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
            Route::post('test-mail', 'testMail')->name('test-mail');
            Route::post('storage/link', 'storageLink')->name('storage.link');
            Route::post('optimize', 'optimize')->name('optimize');

            Route::prefix('cache')->name('cache.')->group(function () {
                Route::post('clear', 'cacheClear')->name('clear');
                Route::post('config', 'cacheConfig')->name('config');
                Route::post('route', 'cacheRoute')->name('route');
                Route::post('view', 'cacheView')->name('view');
            });
        });

    });

    Route::middleware(['auth', 'role:buyer|seller'])
    ->prefix('buyer/balance')
    ->name('general.balance.')
    ->group(function () {

        Route::get('/',        [BalanceController::class, 'index'])->name('index');

        Route::get('/topup',   [BalanceController::class, 'create'])->middleware('role:buyer')->name('create');

        Route::post('/topup',  [BalanceController::class, 'store'])->middleware('role:buyer')->name('store');

        Route::get('/withdraw',  [BalanceController::class, 'withdrawCreate'])->middleware('role:seller')->name('withdraw.create');

        Route::post('/withdraw', [BalanceController::class, 'withdraw'])->middleware('role:seller')->name('withdraw');

        Route::get('/transactions/{transaction}', [BalanceController::class, 'show'])->name('show');
    });


});

/*
|==========================================================================
| AUTH ROUTES
|==========================================================================
*/

require __DIR__.'/auth.php';
