<?php

namespace App\Http\Controllers;

use App\Models\S2Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\S2Video;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;



class S2CommentController extends Controller
{
    public function get_by_video(Request $request)
    {
        $videoId = (int) $request->query('id');
        if (!$videoId) {
            return response()->json(['error' => 'Missing video ID'], 400);
        }

        try {
            $comments = DB::table('s2_comment')
                ->where('video_id', $videoId)
                ->where('type', 'comment')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($row) {
                    $row->created_at = $row->created_at ? date('Y-m-d H:i:s', strtotime($row->created_at)) : null;
                    return $row;
                });

            return response()->json($comments);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Query failed: ' . $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'message' => 'nullable|string',
            'user_id' => 'required|integer',
            'video_id' => 'required|integer',
            'type' => 'required|string|in:comment,view',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Missing or invalid POST parameters.'], 400);
        }

        $username = $request->input('username');
        $message = $request->input('message', '');
        $userId = $request->input('user_id');
        $videoId = $request->input('video_id');
        $type = $request->input('type');

        try {
            if ($type === 'comment') {
                // Always insert comment
                $insert = S2Comment::create([
                    'username' => $username,
                    'message' => $message,
                    'user_id' => $userId,
                    'video_id' => $videoId,
                    'type' => 'comment',
                    'created_at' => now(),
                ]);
                return response()->json(['message' => 'New comment posted successfully']);
            }

            // type === 'view' → Check if already viewed
            $existing = S2Comment::where('user_id', $userId)
                ->where('video_id', $videoId)
                ->where('type', 'view')
                ->exists();

            if ($existing) {
                return response()->json(['message' => "Record with type 'view' already exists."]);
            }

            if ($userId) {
                $insert = S2Comment::create([
                    'username' => $username,
                    'message' => '',
                    'user_id' => $userId,
                    'video_id' => $videoId,
                    'type' => 'view',
                    'created_at' => now(),
                ]);
            }

            return response()->json(['message' => 'New view recorded successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    public function getComments(Request $request)
    {
        $video_id = $request->input('id');
        $user_id = $request->input('user_id');  
        $user = User::where('bitrix_user_id', $user_id)->first();
        $userToken = $user->bitrix_access_token;

        $video = S2Video::with('comments')->findOrFail($video_id);
        $comments = $video->comments;

        $bitrixUsers = $this->getAllBitrixUsers($userToken);
        $commentsWithUserInfo = $comments->map(function ($comment) use ($bitrixUsers) {
            $user = $bitrixUsers->get($comment->user_id);

            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'username' => $comment->username,
                'message' => $comment->message,
                'type' => $comment->type,
                'created_at' => $comment->created_at,
                'xml_id' => $user['XML_ID'] ?? null,
                'departments' => $user['UF_DEPARTMENT'] ?? [],
            ];
        });

        return response()->json($commentsWithUserInfo);
    }
    public function getAllBitrixUsers(string $accessToken)
    {
        $allUsers = collect();
        $start = 0;

        do {
            $response = Http::get("https://bitrixdev.esuhai.org/rest/user.get.json", [
                'access_token' => $accessToken,
                'start' => $start,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Gọi API Bitrix thất bại: ' . $response->body());
            }

            $data = $response->json();
            $users = collect($data['result'] ?? []);
            $allUsers = $allUsers->merge($users);

            $start = $data['next'] ?? null;
        } while ($start !== null);

        return $allUsers->keyBy('ID');
    }
}
