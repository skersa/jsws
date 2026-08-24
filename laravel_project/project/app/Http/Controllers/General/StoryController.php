<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        if (! $user->isSeller()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Yalnızca satıcılar hikaye paylaşabilir.'], 403);
            }
            return back()->with('error', 'Yalnızca satıcılar hikaye paylaşabilir.');
        }

        $request->validate([
            'media'   => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov', 'max:20480'],
            'caption' => ['nullable', 'string', 'max:150'],
        ]);

        $file = $request->file('media');
        $ext = strtolower($file->getClientOriginalExtension());
        $type = in_array($ext, ['mp4', 'webm', 'mov']) ? 'video' : 'image';

        $path = $file->store('stories', 'public');

        $story = Story::create([
            'user_id'    => $user->id,
            'media_path' => $path,
            'media_type' => $type,
            'caption'    => $request->input('caption'),
            'expires_at' => now()->addDay(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Hikayen paylaşıldı! 24 saat boyunca görünür olacak.',
                'story'   => [
                    'id'      => $story->id,
                    'type'    => $story->media_type,
                    'url'     => $story->url(),
                    'caption' => $story->caption,
                ],
                'user'    => [
                    'id'     => $user->id,
                    'name'   => 'Hikayen',
                    'avatar' => $user->profile_img,
                ],
            ]);
        }

        return back()->with('status', 'Hikayen paylaşıldı! 24 saat boyunca görünür olacak.');
    }

    public function destroy(Story $story)
    {
        abort_unless($story->user_id === auth()->id(), 403);
        $story->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => 'Hikaye silindi.']);
        }

        return back()->with('status', 'Hikaye silindi.');
    }
}
