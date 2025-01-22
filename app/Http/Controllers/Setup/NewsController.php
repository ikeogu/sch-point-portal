<?php

namespace Modules\Core\Http\Controllers;

use App\Models\News;
use App\Models\Event;
use App\Models\AuditTrail;
use App\Models\StudentsInClass;
use App\Models\ClassActivity;
use App\Models\ClassTeacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(News $news, Event $event)
    {
        /*$school_id = $this->getSchool()->id;
        $events = $event->fetchEvent($school_id);
        $school_id = $this->getSchool()->id;
        $news = $news->where('school_id', $school_id)->orderBy('id','DESC')->get();
        //$events = $events->where('school_id', $school_id)->orderBy('id','DESC')->get();
        return $this->render('core::news.index', compact('news', 'events'));*/
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return $this->render('core::news.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, News $news)
    {
        //
        $school_id = $this->getSchool()->id;
        $news->school_id = $school_id;
        $news->title = $request->title;
        $news->targeted_audience = implode('~', $request->targeted_audience); //"staff~student~parent";
        $news->description = $request->content; //the input tag name is content
        $news->save();


        if ($request->ajax()) {

            return 'success';
        }
        return redirect()->route('news.index');
    }

    public function recentActivities()
    {
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        //we wanna fetch the activity logs
        $today = Carbon::now();

        $user_activities = $audit_trails = $class_activities = [];

        $user_activities = $user->activityLog()->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->orderBy('id', 'DESC')->get();

        $activities = collect($user_activities);
        if ($user->hasRole('admin')) {
            $audit_trails = AuditTrail::where('school_id', $school_id)->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();
        }
        if ($user->hasRole('teacher')) {
            $staff = $this->getStaff();

            $class_activities = [];
            $class_teachers =  ClassTeacher::where(['school_id' => $school_id, 'teacher_id' => $staff->id])->get();
            foreach ($class_teachers as $class_teacher) {
                $class_activities = $class_teacher->classActivity()->orderBy('id', "DESC")->get();
                $activities = $activities->merge($class_activities);
            }
        }
        if ($user->hasRole('parent')) {
            # code...
            $guardian  = $this->getGuardian();
            $ward_ids = $guardian->ward_ids;

            $ward_id_array = explode('~', substr($ward_ids, 1));

            $class_teacher_id_array = [];


            foreach ($ward_id_array as $key => $student_id) :
                //make sure the student_id does not have an empty value
                if ($student_id != "") {

                    $student_in_class_obj = new StudentsInClass();
                    $student_class = $student_in_class_obj->fetchStudentInClass($student_id, $sess_id, $term_id, $school_id);

                    $class_teacher_id = $student_class->class_teacher_id;

                    //make class_teacher_id_array unique to avoid duplicate activity display from siblings of a parent in the same class
                    if (!in_array($class_teacher_id, $class_teacher_id_array)) {
                        //get the recent class activity for the week
                        $class_activities = ClassActivity::where(['class_teacher_id' => $class_teacher_id, 'school_id' => $school_id])->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

                        $activities = $activities->merge($class_activities);
                    }
                    $class_teacher_id_array[] = $class_teacher_id;
                }

            endforeach;
            $activities = $activities->sortByDesc('created_at');
        }
        return $this->render('core::news.recent_activities', compact('activities', 'user'));
    }

    public function dashboardNotification(News $news)
    {
        //
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $notifications = News::where('school_id', $school_id)
            ->where('targeted_audience', 'like', $user->role)
            ->orWhere('targeted_audience', 'like', $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role)
            ->orderBy('id', 'DESC')->get();
        if ($notifications != '[]') {
            foreach ($notifications as $notification) {

                $seen_by_array  = explode('~', $notification->seen_by);

                $notification->seen_by_array = $seen_by_array;
            }
        }
        return $this->render('core::news.dashboard_notification', compact('notifications', 'user'));
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Parent  $parent
     * @return \Illuminate\Http\Response
     */
    public function show(News $news, $id, Request $request)
    {
        //
        $reader =  $request->reader;
        $notification = $news->find($id);

        $readers = $notification->seen_by;

        if ($readers != NULL && $readers != "") {
            $uniq_readers = explode('~', $readers . '~' . $reader);
            $uniq_readers = implode('~', array_unique($uniq_readers));
            $notification->seen_by = $uniq_readers;
        } else {
            $notification->seen_by = $reader;
        }
        $notification->save();
        return $this->render('core::news.show', compact('notification'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\News  $news
     * @return \Illuminate\Http\Response
     */
    public function edit(News $news)
    {
        //
        $each_news = $news;
        return $this->render('core::news.edit', compact('each_news'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\News  $news
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, News $news)
    {
        //

        $news->title = $request->title;
        $news->description = $request->content;
        $news->targeted_audience = implode('~', $request->targeted_audience);
        $news->save();
        if ($request->ajax()) {

            return 'success';
        }
        return redirect()->route('news.index');
    }


    public function navbarNotificationClicked()
    {
        session(['navbar_clicked' => 'true']);
    }

    public function navbarNotification()
    {
        $user = $this->getUser();
        $today = Carbon::now();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $user_activities = $user->activityLog()->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

        $activities = collect($user_activities);
        if ($user->hasRole('teacher')) {
            $staff = $this->getStaff();

            $class_activities = [];
            $class_teachers =  ClassTeacher::where(['school_id' => $school_id, 'teacher_id' => $staff->id])->get();
            foreach ($class_teachers as $class_teacher) {
                $class_activities = $class_teacher->classActivity()->orderBy('id', "DESC")->get();
                $activities = $activities->merge($class_activities);
            }
        }
        if ($user->hasRole('parent')) {
            # code...
            $guardian  = $this->getGuardian();
            $ward_ids = $guardian->ward_ids;

            $ward_id_array = explode('~', substr($ward_ids, 1));

            $class_teacher_id_array = [];


            foreach ($ward_id_array as $key => $student_id) :
                //make sure the student_id does not have an empty value
                if ($student_id != "") {

                    $student_in_class_obj = new StudentsInClass();
                    $student_class = $student_in_class_obj->fetchStudentInClass($student_id, $sess_id, $term_id, $school_id);

                    $class_teacher_id = $student_class->class_teacher_id;

                    //make class_teacher_id_array unique to avoid duplicate activity display from siblings of a parent in the same class
                    if (!in_array($class_teacher_id, $class_teacher_id_array)) {
                        //get the recent class activity for the week
                        $class_activities = ClassActivity::where(['class_teacher_id' => $class_teacher_id, 'school_id' => $school_id])->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

                        $activities = $activities->merge($class_activities);
                    }
                    $class_teacher_id_array[] = $class_teacher_id;
                }

            endforeach;
        }
        $activities = $activities->sortByDesc('created_at');

        $notifications = News::where('school_id', $school_id)
            ->where('targeted_audience', 'like', $user->role)
            ->orWhere('targeted_audience', 'like', $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role)
            ->orderBy('id', 'DESC')->get();

        if (!$notifications->isEmpty()) {
            foreach ($notifications as $notification) {

                $seen_by_array  = explode('~', $notification->seen_by);

                $notification->seen_by_array = $seen_by_array;
            }
        }
        $count_value = 0;
        if (session()->exists('count_notification')) {
            $count_value = session('count_notification');
        }
        $count = 0;

        foreach ($activities as $activity) {
            if ($activity->actor_id != $user->id) {
                $count++;
            }
        }
        if ($count > $count_value) {
            session(['count_notification' => ($count - $count_value)]);
            session()->forget(['navbar_clicked']);
            $count_value = session('count_notification');
        }

        if (session()->exists('navbar_clicked')) {
            $count_value = "";
        }

        return view('core::news.navbar_notification', compact('activities', 'user', 'notifications', 'count_value'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\News  $News
     * @return \Illuminate\Http\Response
     */
    public function destroy(News $news)
    {
        //
    }
}
