<?php

namespace App\Http\Controllers;

use App\Models\S2Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class S2VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $idVideo = $request->query('id');
        $hashtag = $request->query('hashtag');
        $topic = $request->query('topic');
        $searchInput = $request->query('searchInput');
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 5);
        $offset = ($page - 1) * $limit;

        // Count query
        $countQuery = DB::table('s2_videos')->whereRaw('1 = 1');

        if ($idVideo) {
            $countQuery->where('id', $idVideo);
        }
        if ($hashtag) {
            $countQuery->where('hashtag', 'LIKE', '%' . $hashtag . '%');
        }
        if ($topic) {
            $countQuery->where('topic', 'LIKE', '%' . $topic . '%');
        }
        if ($searchInput) {
            $countQuery->where('title', 'LIKE', '%' . $searchInput . '%');
        }

        $totalVideos = $countQuery->count();

        // Main query with comments and views count
        $videos = DB::table('s2_videos as v')
            ->leftJoin('s2_comment as c', 'v.id', '=', 'c.video_id')
            ->select(
                'v.*',
                DB::raw("COUNT(CASE WHEN c.type = 'comment' THEN 1 END) AS total_comments"),
                DB::raw("COUNT(CASE WHEN c.type = 'view' THEN 1 END) AS total_views")
            )
            ->when($idVideo, fn($q) => $q->where('v.id', $idVideo))
            ->when($hashtag, fn($q) => $q->where('v.hashtag', 'LIKE', '%' . $hashtag . '%'))
            ->when($topic, fn($q) => $q->where('v.topic', 'LIKE', '%' . $topic . '%'))
            ->when($searchInput, function ($q) use ($searchInput) {
                $q->where(function ($subQ) use ($searchInput) {
                    $subQ->where('v.title', 'LIKE', '%' . $searchInput . '%')
                        ->orWhere('v.description', 'LIKE', '%' . $searchInput . '%');
                });
            })
            ->groupBy('v.id')
            ->orderByDesc('v.created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Convert topic from JSON string to array and format created_at
        $videos->transform(function ($item) {
            $item->created_at = $item->created_at ? date('Y-m-d H:i:s', strtotime($item->created_at)) : null;
            $item->topic = json_decode($item->topic, true);
            return $item;
        });

        return response()->json([
            'videos' => $videos,
            'total' => $totalVideos
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:create,edit',
            'title' => 'required|string',
            'description' => 'required|string',
            'url' => 'required|url',
            'hashtag' => 'nullable|string',
            'topics' => 'nullable|array',
            'typeVideo' => 'nullable|string',
            'linkShare' => 'nullable|string',
            'createdBy' => 'nullable|string',
            'id' => 'nullable|integer|required_if:action,edit',
        ]);

        try {
            $data = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'url' => $validated['url'],
                'hashtag' => $validated['hashtag'] ?? '',
                'typeVideo' => $validated['typeVideo'] ?? '',
                'linkShare' => $validated['linkShare'] ?? '',
                'created_by' => $validated['createdBy'] ?? '',
                'topic' => $validated['topics'] ?? [],
            ];

            if ($validated['action'] === 'create') {
                $video = S2Video::create($data);
                // dd($video);
                return response()->json(['status' => 'success', 'message' => 'Video created successfully']);
            }

            if ($validated['action'] === 'edit') {
                $video = S2Video::find($validated['id']);
                if (!$video) {
                    return response()->json(['status' => 'error', 'message' => 'Video not found'], 404);
                }

                $video->update($data);
                return response()->json(['status' => 'success', 'message' => 'Video updated successfully']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function listVideo(Request $request)
    {
        return response()->json([
            'videos' => S2Video::orderBy('created_at', 'desc')->get(),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(S2Video $s2Video)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(S2Video $s2Video)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, S2Video $s2Video)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(S2Video $s2Video)
    {
        //
    }
}
