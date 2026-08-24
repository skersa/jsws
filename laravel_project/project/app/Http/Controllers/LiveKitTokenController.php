<?php

namespace App\Http\Controllers;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Models\Auction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * LiveKit (WebRTC SFU) katılım token'ı üretir.
 * - Yayıncı (broadcaster): yalnızca ilanın sahibi satıcı → canPublish
 * - İzleyici (viewer): herkes (giriş yapmamış olsa da) → canSubscribe
 * API secret ASLA istemciye gönderilmez; yalnızca kısa ömürlü JWT + server_url döner.
 */
class LiveKitTokenController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $apiKey    = config('services.livekit.api_key');
        $apiSecret = config('services.livekit.api_secret');
        $serverUrl = config('services.livekit.url');

        if (! $apiKey || ! $apiSecret || ! $serverUrl) {
            return response()->json([
                'message' => 'Canlı yayın altyapısı (LiveKit) henüz yapılandırılmadı.',
                'configured' => false,
            ], 503);
        }

        $data = $request->validate([
            'auction' => ['required', 'string'],
            'role'    => ['required', 'in:broadcaster,viewer'],
        ]);

        $auction = Auction::where('slug', $data['auction'])->orWhere('id', $data['auction'])->firstOrFail();
        $room    = 'auction-'.$auction->id;

        $user       = $request->user();
        $isPublisher = $data['role'] === 'broadcaster';

        if ($isPublisher) {
            // Yalnızca ilan sahibi satıcı yayın yapabilir
            abort_unless($user && (int) $user->id === (int) $auction->user_id, 403);
            $identity = 'user-'.$user->id;
            $name     = $user->name;
        } else {
            $identity = $user ? ('user-'.$user->id) : ('guest-'.Str::random(10));
            $name     = $user->name ?? 'İzleyici';
        }

        $options = (new AccessTokenOptions())
            ->setIdentity($identity)
            ->setName($name)
            ->setTtl(3600); // 1 saat

        $grant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($room)
            ->setCanPublish($isPublisher)
            ->setCanSubscribe(true)
            ->setCanPublishData(true);

        $token = (new AccessToken($apiKey, $apiSecret))
            ->init($options)
            ->setGrant($grant)
            ->toJwt();

        return response()->json([
            'configured'        => true,
            'server_url'        => $serverUrl,
            'participant_token' => $token,
            'room'              => $room,
            'identity'          => $identity,
        ]);
    }
}
