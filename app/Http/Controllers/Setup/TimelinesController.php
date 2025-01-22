<?php

namespace App\Http\Controllers\Setup;

use App\Models\Timeline;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClassTeacher;
use App\Models\StudentsInClass;
use App\Models\TimelineComment;

class TimelinesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user = $this->getUser();
        $school = $this->getSchool();

        $user_id = $user->id;
        //Personal time line
        $personal_timelines = Timeline::with('user', 'comments.user')->where(['school_id' => $school->id])->where('user_id', $user_id)->orWhere(function ($query) use ($user_id) {

            return
                $query->where('visible_to', 'like', $user_id)
                ->orWhere('visible_to', 'like', $user_id . '~%')
                ->orWhere('visible_to', 'like', '%~' . $user_id . '~%')
                ->orWhere('visible_to', 'like', '%~' . $user_id);
        })->orderBy('id', 'DESC')->paginate(10);

        //posts and time line that is permitted to be viewed

        // $permitted_timelines = Timeline::with('user', 'comments.user')->where(['school_id' => $school->id])->where(function ($query) use ($user_id) {
        //         return
        //             $query->where('user_id', $user_id)
        //             ->where('visible_to', 'like', $user_id)
        //             ->orWhere('visible_to', 'like', $user_id . '~%')
        //             ->orWhere('visible_to', 'like', '%~' . $user_id . '~%')
        //             ->orWhere('visible_to', 'like', '%~' . $user_id);
        //     })->orderBy('id', 'DESC')->paginate(10);

        $timelines = $personal_timelines; // collect($personal_timelines)->merge($permitted_timelines);
        return $this->render(compact('timelines'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->content == "")
            return "false";
        $visible_to = '';
        $user = $this->getUser();
        $school = $this->getSchool();
        $sess_id = $this->getSession()->id;

        if ($user->role === 'student') {
            $student = $this->getStudent();
            $student_in_class = StudentsInClass::where(['student_id' => $student->id, 'sess_id' => $sess_id, 'school_id' => $school->id])->first();
            if ($student_in_class) {

                $class_students = StudentsInClass::with('student')->where(['class_teacher_id' => $student_in_class->class_teacher_id, 'sess_id' => $sess_id, 'school_id' => $school->id])->get();

                foreach ($class_students as $class_student) {
                    $visible_to .= addSingleElementToString($visible_to, $class_student->student->user_id);
                }
            }
        } else {
            $staff = $this->getStaff();
            $class_teaher = ClassTeacher::where(['teacher_id' => $staff->id, 'school_id' => $school->id])->first();
            if ($class_teaher) {
                $class_students = StudentsInClass::with('student')->where(['class_teacher_id' => $class_teaher->id, 'sess_id' => $sess_id, 'school_id' => $school->id])->get();

                foreach ($class_students as $class_student) {
                    $visible_to .= addSingleElementToString($visible_to, $class_student->student->user_id);
                }
            }
        }
        $timeline = Timeline::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'content' => $request->content,
            'visible_to' => $visible_to,
            'status' => 1
        ]);
        return $this->show($timeline);
        //        $user_id = $this->getStudent()->user_id;
        //        $medias = TempFile::where( 'user_id', '=', $user_id )->pluck('file_link');
        // $medias = session()->remove('medias');
        // //        dd( $medias );
        // if ($medias != null) {
        //     # code...
        //     foreach ($medias as $media) {
        //         $timeline->addTimelineMedia($media);
        //     }
        // }
    }

    public function show(Timeline $timeline)
    {
        $timeline = $timeline->with('user', 'comments.user')->find($timeline->id);
        return $this->render(compact('timeline'));
    }
    public function postComment(Request $request)
    {
        $timeline_id = $request->timeline_id;
        $comment = $request->comment;
        $user = $this->getUser();

        $timeline_comment = new TimelineComment();
        $timeline_comment->timeline_id = $timeline_id;
        $timeline_comment->comment = $comment;
        $timeline_comment->user_id = $user->id;
        $timeline_comment->save();
        $timeline_comment = $timeline_comment->with('user')->find($timeline_comment->id);
        return $this->render(compact('timeline_comment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Timeline  $timeline
     * @return \Illuminate\Http\Response
     */
    public function edit(Timeline $timeline)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Timeline  $timeline
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Timeline $timeline)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Timeline  $timeline
     * @return \Illuminate\Http\Response
     */
    public function destroy(Timeline $timeline)
    {
        //
    }
}
