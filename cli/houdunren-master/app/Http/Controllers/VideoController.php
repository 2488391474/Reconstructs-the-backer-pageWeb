<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Lesson;
use App\Models\Video;
use App\Models\VideoPlayHistory;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index',  'history', 'search']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return VideoResource::collection(Video::latest('id')->with('lesson')->paginate(15));
    }

    //课程视频
    public function getLessonVideo(Lesson $lesson)
    {
        $this->authorize('getLessonVideo', Video::class);
        return VideoResource::collection($lesson->videos->makeVisible(['path']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreVideoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVideoRequest $request, Video $video)
    {
        $video->fill($request->input())->save();
        return new VideoResource($video);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function show(Video $video)
    {
        try {
            VideoPlayHistory::updateOrCreate(['user_id' => Auth::id(), 'video_id' => $video->id], ['lesson_id' => $video->lesson_id])->touch();
            //视频观看统计
            $video->view_num = VideoPlayHistory::where('video_id', $video->id)->count();
            $video->save();
            //课程观看统计
            $video->lesson->view_num = VideoPlayHistory::where('lesson_id', $video->lesson->id)->count();
            $video->lesson->save();
            //系统课程观看统计
            $system = $video->lesson->system;
            if ($system) {
                $system->view_num = VideoPlayHistory::whereIn('lesson_id', $system->lessons->pluck('id'))->count();
                $system->save();
            }
            //权限判断
            $duration = Auth()->user()->duration;
            $canPlay = $duration && (now()->diffInMinutes($duration->end_time, false) > 0);

            $video = $video->load(['lesson.videos', 'lesson.system.lessons']);
            $video->lesson->videos->each(fn ($video) => $video->makeVisible('path'));
            return new VideoResource($canPlay ? $video->makeVisible(['path']) : $video);
        } catch (AuthorizationException $e) {
            return $this->error('没有观看权限');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVideoRequest  $request
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVideoRequest $request, Video $video)
    {
        $video->fill($request->input())->save();
        return new VideoResource($video);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function destroy(Video $video)
    {
        $this->authorize('delete', $video);
        $video->delete();
        return $this->success();
    }
}