<?php

namespace App\Http\Controllers;

use App\Http\Resources\SongsResource;
use App\Models\SongPopular;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class SongPopularController extends Controller
{
    public function songsLiked(Request $request): JsonResponse
    {
        $songsLiked = SongPopular::where('user_id', $request->user_id)->get();

        return response()->json(['data' => $songsLiked, 'status' => 200]);
    }

    public function addSongLike(Request $request, SongPopular $model): JsonResponse
    {
        $validator = Validator::make($request->post(), [
            'song_id' => 'required'
        ]);
        if ($validator->fails())
            return response()->json(['message' => $validator->errors(), 'status' => 417], 417);

        $userId = $request->user_id;
        $songId = $validator->validated()['song_id'];
        $data = ['song_id' => $songId, 'user_id' => $userId];

        $addSongLike = $model->create($data);

        return ($addSongLike) ?
            response()->json(['message' => 'song added successfully', 'status' => 200]) :
            response()->json(['message' => 'an error has occurred', 'status' => 500]);
    }

    public function removeSongLike(Request $request): JsonResponse
    {
        $validator = Validator::make($request->post(), [
            'song_id' => 'required'
        ]);
        if ($validator->fails())
            return response()->json(['message' => $validator->errors(), 'status' => 417], 417);

        $songId = $validator->validated()['song_id'];

        $getLike = SongPopular::where('song_id', $songId)->where('user_id', $request->user_id);

        if (!$getLike->exists())
            return response()->json(['message' => 'an error has occurred', 'status' => 500]);

        $getLike->delete();

        return response()->json(['message' => 'song removed successfully', 'status' => 200]);
    }

    public function songsPopular(): JsonResponse
    {
        $songs = [];
        $songsPopular = SongPopular::inRandomOrder()->get();

        foreach ($songsPopular as $songPopular) {
            $getSong = $songPopular->song()->first();
            Arr::set($songs, $getSong->id, $getSong);
        }
        $songs = SongsResource::collection($songs);

        return response()->json(['data' => $songs, 'status' => 200]);
    }
}
