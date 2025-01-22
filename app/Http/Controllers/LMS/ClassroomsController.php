<?php

namespace App\Http\Controllers\LMS;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\ClassroomPost;
use App\Models\ClassTeacher;
use App\Models\Routine;
use App\Models\DailyClassroom;
use App\Models\DailyClassroomAttendee;
use App\Models\DailyClassroomMaterial;
use App\Models\DailyClassroomVideo;
use App\Models\StudentsInClass;
use App\Models\SubjectTeacher;
use App\Models\User;
use App\Models\YoutubeVideo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClassroomsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $user = $this->getUser();
        if ($user->hasRole('admin') || $user->hasRole('proprietor')) {
            return $this->render('lms::classroom.admin');
        }
        if ($user->hasRole('student')) {
            return $this->render('lms::classroom.student');
        }
        if ($user->hasRole('teacher')) {
            return $this->render('lms::classroom.teacher');
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function teacherRoutine()
    {
        set_time_limit(0);
        $staff = $this->getStaff();
        $school = $this->getSchool();
        $dateS = Carbon::now()->startOfQuarter(); // ->subMonth(3); // within a term
        $dateE = Carbon::now()->endOfQuarter();
        $subject_teachers = SubjectTeacher::with(['dailyClassrooms' => function ($q) use ($dateS, $dateE) {
            $q->whereBetween('created_at', [$dateS, $dateE]);
        }, 'dailyClassrooms.materials', 'dailyClassrooms.videos.youtubeVideo', 'routines', 'classTeacher.c_class', 'subject'])->where(['school_id' => $school->id, 'teacher_id' => $staff->id])->get();

        return response()->json(compact('subject_teachers'), 200);
    }

    public function createdOnlineClassrooms(Request $request)
    {
        set_time_limit(0);
        $school_id = $this->getSchool()->id;
        $date = todayDate();
        $today = getDateFormatWords($date);
        $dateS = Carbon::now()->startOfQuarter(); // ->subMonth(3); // within a term
        $dateE = Carbon::now()->endOfQuarter();


        if (isset($request->option) && $request->option == 'yes') {
            $daily_classrooms = DailyClassroom::with(['materials', 'posts', 'subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class', 'subjectTeacher.staff.user'])->where(['school_id' => $school_id])->whereBetween('created_at', [$dateS, $dateE])->orderBy('date', 'DESC')->get();
        } else {
            $daily_classrooms = DailyClassroom::with(['materials', 'videos.youtubeVideo', 'posts', 'subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class', 'subjectTeacher.staff.user'])->where(['school_id' => $school_id, 'date' => $today])->orderBy('date', 'DESC')->get();
        }


        return response()->json(compact('daily_classrooms'), 200);
        //
    }

    public function studentRoutine(Request $request)
    {
        $stud_id = $this->getStudent()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;
        $student_in_class = StudentsInClass::where([
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'student_id' => $stud_id,
            //'term_id'=>$term_id,
        ])->first();
        $date = todayDate();
        $today = getDateFormatWords($date);
        $class_teacher_id = $student_in_class->class_teacher_id;
        $dateS = Carbon::now()->startOfQuarter(); //->subMonth(4); // within a term
        $dateE = Carbon::now()->endOfQuarter();


        if (isset($request->option) && $request->option == 'yes') {
            $daily_classrooms = DailyClassroom::with(['materials', 'videos.youtubeVideo', 'posts', 'subjectTeacher.subject', 'subjectTeacher.staff.user'])->where(['school_id' => $school_id, 'class_teacher_id' => $class_teacher_id])->orderBy('date', 'DESC')->whereBetween('created_at', [$dateS, $dateE])->get();
        } else {
            $daily_classrooms = DailyClassroom::with(['materials', 'videos.youtubeVideo', 'posts', 'subjectTeacher.subject', 'subjectTeacher.staff.user'])->where(['school_id' => $school_id, 'class_teacher_id' => $class_teacher_id, 'date' => $today])->orderBy('date', 'DESC')->get();
        }

        return response()->json(compact('daily_classrooms'), 200);
    }


    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $staff = $this->getStaff();
        $school = $this->getSchool();
        $class_teacher_id = $request->class_teacher_id;
        $subject_teacher_id = $request->subject_teacher_id;
        $subject_teacher = SubjectTeacher::with(['subject', 'classTeacher.c_class'])->find($subject_teacher_id);

        $day = $request->day;
        $routine = Routine::where(['school_id' => $school->id, /*'teacher_id' => $staff->id,*/ 'class_teacher_id' => $class_teacher_id, 'subject_teacher_id' => $subject_teacher_id, 'day' => $day])->first();
        $routine->teacher_id = $staff->id;
        $routine->save();
        $end = strtotime($routine->end);
        $start = strtotime($routine->start);
        $duration = ($end - $start) / 60; //in minutes

        $daily_classroom = new DailyClassroom();
        $daily_classroom->school_id = $school->id;
        $daily_classroom->class_teacher_id = $class_teacher_id;
        $daily_classroom->subject_teacher_id = $subject_teacher_id;
        $daily_classroom->topic = $request->topic;
        $daily_classroom->description = $request->description;
        $daily_classroom->duration = $duration;
        $daily_classroom->end = $routine->end;
        $daily_classroom->start = $routine->start;
        $daily_classroom->date = getDateFormatWords($request->date);
        if ($daily_classroom->save()) {

            $request->class_teacher_id = $class_teacher_id;
            //we dont want to record save events...intead we want submit,approve, publish, disapprove etc
            $event_action = "Scheduled an online classroom to hold on " . $daily_classroom->date . " for students offering" . $subject_teacher->subject->name . " in " . $subject_teacher->classTeacher->c_class->name;

            $this->teacherStudentEventTrail($request, $event_action, 'class');
            return response()->json(compact('daily_classroom'), 200);
        }
    }


    public function createOnlineClassVideo(Request $request)
    {
        return $this->render('lms::classroom.create_video');
    }

    public function uploadOnlineClassVideo(Request $request)
    {
        $school = $this->getSchool();
        $request->school_id = $school->id;
        // $youtube_video_obj = new YoutubeVideo();
        // $youtube = $youtube_video_obj->storeVideo($request);
        $link = $request->link;
        $link_array = explode('?v=', $link);
        if (!isset($link_array[1])) {
            $link_array = explode('.be/', $link);
        }
        // return $link_array;
        $param = $link_array[1];
        $daily_classroom_id = $request->daily_classroom_id;

        $daily_class_room_video = DailyClassroomVideo::where(['daily_classroom_id' => $daily_classroom_id, 'link' => $link])->first();
        if (!$daily_class_room_video) {
            $daily_class_room_video = new DailyClassroomVideo();
            $daily_class_room_video->daily_classroom_id = $daily_classroom_id;
            $daily_class_room_video->link = $link;
            $daily_class_room_video->param = $param;
            $daily_class_room_video->save();
        }
        // $daily_class_room_video = new DailyClassroomVideo();
        // $daily_class_room_video->daily_classroom_id = $daily_classroom_id;
        // $daily_class_room_video->link = $link;
        // $daily_class_room_video->save();

        // $daily_class_room_video->youtubeVideo = $daily_class_room_video->youtubeVideo;
        return response()->json(
            [
                'daily_class_room_video' => $daily_class_room_video,
            ],
            200
        );
    }
    /**
     * Show the specified resource.
     * @return Response
     */
    public function uploadOnlineClassMaterials(Request $request)
    {
        $media = $request->file('media');
        $daily_classroom_id = $request->daily_classroom_id;
        $classroom = DailyClassroom::find($daily_classroom_id);
        $classroom->class_note = $request->class_note;
        $classroom->save();
        $school = $this->getSchool();
        if ($media != null && $media->isValid()) {
            $file_name = time() . "." . $media->guessClientExtension();
            // $folder_key = $request->folder_key . DIRECTORY_SEPARATOR . "photo" . DIRECTORY_SEPARATOR . $role;
            $folder_key = $school->folder_key . '/' . "classroom";
            $photo =  $this->uploadFile($media, $file_name, $folder_key);

            $daily_class_room_material = new DailyClassroomMaterial();
            $daily_class_room_material->daily_classroom_id = $daily_classroom_id;
            $daily_class_room_material->file_link = $photo;
            $daily_class_room_material->file_name = $file_name;
            $daily_class_room_material->mime = $media->getMimeType();
            $daily_class_room_material->save();
            return response()->json(
                [
                    'material' => $daily_class_room_material,
                ],
                200
            );
        }
    }

    public function updateOnlineClassNote(Request $request)
    {
        $daily_classroom_id = $request->daily_classroom_id;
        $classroom = DailyClassroom::find($daily_classroom_id);
        $classroom->class_note = $request->class_note;
        $classroom->save();
    }
    public function postInOnlineClass(Request $request)
    {
        $user_id = $this->getUser()->id;
        $classroom_post = new ClassroomPost();
        $classroom_post->sender_id = $user_id;
        $classroom_post->daily_classroom_id = $request->daily_classroom_id;
        $classroom_post->post = $request->post_chat;
        $classroom_post->save();
        $classroom_post->user = $classroom_post->user;
        return response()->json(compact('classroom_post'), 200);
    }
    public function deleteClassroomPost($id)
    {
        $classroom_post = ClassroomPost::find($id);
        $classroom_post->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function onlineClassStudents($id)
    {
        $user = $this->getUser();
        $user_id = $user->id;
        $classroom = DailyClassroom::with(['attendees.user', 'posts.user', 'materials', 'videos.youtubeVideo',])->find($id);
        $attendees = $classroom->attendees;
        $online_students = $attendees;

        $can_edit = false;
        if ($classroom->subjectTeacher->staff->user_id == $user_id) {
            $can_edit = true;
        }
        // if ($attendees != null) {

        //     foreach ($attendees as $attendee) {
        //         $online_students[] = User::find($attendee->user_id);
        //     }

        //     $classroom->online_students = $online_students;
        // }
        $classroom_chats = $classroom->posts;

        return response()->json(compact('online_students', 'classroom_chats', 'can_edit', 'user_id', 'classroom'), 200);
    }

    public function comeOnline($daily_classroom_id)
    {
        $user = $this->getUser();
        $user_id = $user->id;
        $attendee = DailyClassroomAttendee::where(['user_id' => $user_id, 'daily_classroom_id' => $daily_classroom_id])->first();
        if (!$attendee) {
            $attendee = new DailyClassroomAttendee();
            $attendee->user_id = $user_id;
            $attendee->daily_classroom_id = $daily_classroom_id;
            $attendee->save();
        }
        // $classroom = DailyClassroom::with('posts')->find($daily_classroom_id);
        // $attendees = $classroom->attendees;

        // $classroom->attendees = addSingleElementToString($attendees, $user_id);
        // $classroom->save();

        return response()->json(compact('attendee', 'user'), 200);
    }
    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function deleteOnlineclass($id)
    {
        $classroom = DailyClassroom::with(['materials', 'posts', 'attendees'])->find($id);

        foreach ($classroom->materials as $material) {
            Storage::disk('public')->delete($material->file_link);
            $material->delete();
        }
        $classroom->posts()->delete();
        $classroom->attendees()->delete();
        $classroom->delete();

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function deleteOnlineclassMaterial($id)
    {
        $material = DailyClassroomMaterial::find($id);

        Storage::disk('public')->delete($material->file_link);
        $material->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function deleteOnlineclassVideo($id)
    {
        // $youtube_video_obj = new YoutubeVideo();
        $video = DailyClassroomVideo::find($id);

        // $videoId = $video->youtubeVideo->youtube_id;
        // $youtube_video_obj->deleteVideo($videoId); //delete from youtube server
        // $video->youtubeVideo->delete(); //delete from our db
        $video->delete(); //delete relating table
        return response()->json([], 204);
    }

    public function liveClass()
    {
        return $this->render('lms::classroom.live_classroom');
    }
    public function createLiveClass()
    {
        return $this->render('lms::classroom.create_live_class');
    }
}
