<?php

namespace App\Http\Controllers\Messages;

use App\Http\Controllers\Controller;
use App\Models\ClassTeacher;
use App\Models\Message;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\StudentsInClass;
use App\Models\SubjectTeacher;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class MessagesController extends Controller
{

    public function index()
    {
        return $this->inbox();
    }

    public function navbarNotification()
    {
        $user_id = $this->getUser()->id;
        $messages = Message::where(function ($query) use ($user_id) {
            return $query->where('recipient', $user_id)
                ->orWhere('copied_to', 'LIKE', '%~' . $user_id . '~%');
        })->orderBy('created_at', 'DESC')->get();



        $count = $messages->count();
        if (!$messages->isEmpty()) {
            foreach ($messages as $message) {
                if ($message->original_message_id != NULL) {
                    $message->original_message = Message::find($message->original_message_id);
                }
                $read_by_array = explode('~', $message->read_by);
                $message->read = 'not_read';
                if (in_array($user_id, $read_by_array)) {
                    $message->read = 'read';
                    $count--;
                }
            }
        }

        return view('messages.navbar_notification', compact('messages', 'user_id', 'count'));
    }

    public function extraOptions()
    {
        $user = $this->getUser();
        $user_id = $user->id;
        $school_id = $this->getSchool()->id;

        if ($user->hasRole('admin')) {
            // admin
            $options = ['student', 'parent', 'staff'];
            $students = Student::with('user')->where('school_id', $school_id)
                ->where('user_id', '!=', $user_id)->get();

            $guardian = Guardian::with('user')->where('school_id', $school_id)
                ->where('user_id', '!=', $user_id)->get();
            $staff = Staff::with('user')->where('school_id', $school_id)
                ->where('user_id', '!=', $user_id)->get();

            $recipients['student'] = $students;
            $recipients['parent'] = $guardian;
            $recipients['staff'] = $staff;
        } else if ($user->role === 'staff') {

            $options = ['student', 'parent', 'staff'];
            $students = $this->getTeacherStudents();

            $guardian = [];
            $guardian_id_array = [];
            foreach ($students as $student) {
                $gurdian_student = GuardianStudent::with('guardian.user')->where('student_id', $student->id)->first();
                if (!in_array($gurdian_student->guardian->id, $guardian_id_array)) {

                    $guardian[] = $gurdian_student->guardian;

                    $guardian_id_array[] = $gurdian_student->guardian->id;
                }
            }
            $staff = Staff::with('user')->where('school_id', $school_id)
                ->where('user_id', '!=', $user_id)->get();

            $recipients['student'] = $students;
            $recipients['parent'] = $guardian;
            $recipients['staff'] = $staff;
        } else if ($user->role === 'parent') {

            $options = ['staff'];
            $staff = $this->getWardTeachers();
            $recipients['staff'] = $staff;
        } else {
            // admin
            $options = ['staff'];
            $student_id = $this->getStudent()->id;
            $staff = [];
            $staff_id_array = [];

            $student_in_class_obj = new StudentsInClass();

            $student_in_class = $student_in_class_obj->fetchStudentInClass($student_id, $this->getSession()->id, $this->getTerm()->id, $school_id);

            if ($student_in_class->classTeacher) {
                if ($student_in_class->classTeacher->teacher_id !== null) {
                    if (!in_array($student_in_class->classTeacher->teacher_id, $staff_id_array)) {

                        $staff[] = Staff::with('user')->find($student_in_class->classTeacher->teacher_id);
                        $staff_id_array[] = $student_in_class->classTeacher->teacher_id;
                    }
                }

                $stubject_teachers = SubjectTeacher::where('class_teacher_id', $student_in_class->class_teacher_id)->where('teacher_id', '!=', null)->where('teacher_id', '!=', $student_in_class->classTeacher->teacher_id)->get();
                foreach ($stubject_teachers as $stubject_teacher) {

                    if (!in_array($stubject_teacher->teacher_id, $staff_id_array)) {

                        $staff[] = Staff::with('user')->find($stubject_teacher->teacher_id);
                    }
                }
            }
            $staff[] = Staff::with('user')->where('school_id', $school_id)->first();

            $recipients['staff'] = $staff;
        }

        return array($options, $recipients);
    }
    private function getTeacherStudents()
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $teacher = $this->getStaff();
        $teacher_obj = new Teacher();

        $my_students = [];
        $my_students_id_array = [];
        $subject_teachers = SubjectTeacher::where(['teacher_id' => $teacher->id, 'school_id' => $school_id])->get();
        if ($subject_teachers->isNotEmpty()) {
            foreach ($subject_teachers as $subject_teacher) {
                $subject_students =  $teacher_obj->teacherSubjectStudents($subject_teacher, $sess_id, $term_id, $school_id);

                foreach ($subject_students as $subject_student) {
                    if (!in_array($subject_student->id, $my_students_id_array)) {

                        $my_students[] = $subject_student;
                        $my_students_id_array[] = $subject_student->id;
                    }
                }
            }
        }


        $class_teachers = ClassTeacher::where(['teacher_id' => $teacher->id, 'school_id' => $school_id])->get();
        if ($class_teachers->isNotEmpty()) {
            foreach ($class_teachers as $class_teacher) {
                $class_students =  $teacher_obj->teacherClassStudents($class_teacher->id, $sess_id, $term_id, $school_id);
                foreach ($class_students as $class_student) {
                    if (!in_array($class_student->id, $my_students_id_array)) {

                        $my_students[] = $class_student;
                        $my_students_id_array[] = $class_student->id;
                    }
                }
            }
        }

        return $my_students;
    }

    private function getWardTeachers()
    {
        $user = $this->getUser();
        $user_id = $user->id;
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $recipients = [];
        $guardian = Guardian::where(['school_id' => $school_id, 'user_id' => $user_id])->first();
        $guardian_wards = $guardian->guardianStudents;
        $teacher_id_array = [];
        foreach ($guardian_wards as $guardian_ward) {
            $student_in_class_obj = new StudentsInClass();
            $student_id = $guardian_ward->student_id;

            $student_in_class = $student_in_class_obj->fetchStudentInClass($student_id, $sess_id, $term_id, $school_id);
            if ($student_in_class) {
                # code...

                if ($student_in_class->classTeacher) {
                    if (!in_array($student_in_class->classTeacher->teacher_id, $teacher_id_array)) {

                        $staff = Staff::with('user')->find($student_in_class->classTeacher->teacher_id);
                        if ($staff) {

                            $recipients[] = Staff::with('user')->find($student_in_class->classTeacher->teacher_id); // school admin
                        }

                        $teacher_id_array[] = $student_in_class->classTeacher->teacher_id;
                    }
                }
            }
        }
        $staff = Staff::with('user')->where('school_id',  $school_id)->first();
        $recipients[] = Staff::with('user')->where('school_id',  $school_id)->first(); // school admin

        return $recipients;
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function inbox()
    {
        $user_id = $this->getUser()->id;
        $messages = Message::with('from', 'to')->where(function ($query) use ($user_id) {
            return $query->where('recipient', '=', $user_id)
                ->orWhere('copied_to', 'LIKE', '%~' . $user_id . '~%');
        })->where(function ($query) use ($user_id) {
            return $query->where('recipient_delete', 'NOT LIKE', '%~' . $user_id . '~%')
                ->orWhere('recipient_delete', '=', NULL);
        })->orderBy('created_at', 'DESC')->get();
        foreach ($messages as $message) {
            if ($message->original_message_id != NULL) {
                $message->original_message = Message::find($message->original_message_id);
            }
        }

        //echo $messages;exit;
        list($options, $recipients) = $this->extraOptions();

        $type = 'inbox';
        return response()->json(compact('messages', 'options', 'recipients', 'type'), 200);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sent()
    {
        $messages = Message::with('from', 'to')->where('sender', $this->getUser()->id)
            ->where('sender_delete', '=', NULL)
            ->orderBy('created_at', 'DESC')->get();


        list($options, $recipients) = $this->extraOptions();
        $type = 'sent';
        return $this->render(compact('messages', 'options', 'recipients', 'type'));
    }

    /**
     * @param Message $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $message = new Message();
        $recipients = $request->new_recipients;
        $count = count($recipients);
        $recipient = $recipients[0];
        if ($count > 1) {
            //removing the first element since this is the main recipient
            unset($recipients[0]);

            //convert remaining recipients to string
            $copied_to = implode('~', $recipients);

            //tie it to the $message instances
            $message->copied_to = '~' . $copied_to . '~';
        }
        $message->sender = $this->getUser()->id;
        $message->recipient = $recipient;
        $message->subject = $request->subject;
        $message->message = $request->message;

        if ($message->save()) {
            return (string) 'success';
        }
        return (string) 'failed';
    }
    private function processRepliedMessages($request, $message)
    {
        $user_id = $this->getUser()->id;
        if ($message->replies == NULL || $message->replies == '') {
            $old_replies = '';
        } else {
            $old_replies = '~' . $message->replies;
        }
        $replier = $user_id;
        $reply_message = $request->message;

        $reply_details = json_encode(
            array(
                'replier' => $replier,
                'message' => $reply_message
            )
        ) . $old_replies;

        $message->replies = $reply_details;
        $message->save();
    }
    public function update(Request $request, Message $message)
    {


        $school_id = $this->getSchool()->id;
        $user_id = $this->getUser()->id;

        //reply action
        if ($request->action == 'reply') {

            $this->processRepliedMessages($request, $message);
            $original_message_id = $message->id;
            if ($message->original_message_id != NULL) {
                $original_message = Message::find($message->original_message_id);
                $this->processRepliedMessages($request, $original_message);

                /*$original_message_id = $original_message->id;
                $original_message->copied_to;
                $new_reply_message->recipient = $message->sender;*/
            }
        }

        if ($request->action == 'forward') {
            if ($message->copied_to == NULL || $message->copied_to == '') {
                $old_copied = '';
            } else {
                $old_copied = $message->copied_to;
            }
            $recipients = $request->recipients;
            $forwarded_to = '';
            foreach ($recipients as $recipient) :
                $forwarded_to .= '~' . $recipient;
            endforeach;
            $uniq_recipients = array_unique(explode('~', $old_copied . $forwarded_to));
            $uniq_recipients_str = implode('~', $uniq_recipients);
            $message->copied_to = $uniq_recipients_str . '~';

            $message->save();
        }

        if ($request->action == 'delete_inbox') {


            if ($message->recipient_delete == NULL || $message->recipient_delete == '') {
                $deleted_recipients = '';
            } else {
                $deleted_recipients = $message->recipient_delete;
            }
            $this_recipient = '~' . $user_id;

            $uniq_recipients = array_unique(explode('~', $deleted_recipients . $this_recipient));
            $uniq_recipients_str = implode('~', $uniq_recipients);
            $message->recipient_delete = $uniq_recipients_str . '~';

            $message->save();
        }
        if ($request->action == 'delete_sent') {


            $this_recipient = $user_id;

            $message->sender_delete = $this_recipient;

            $message->save();
        }
        return $this->messageDetails($request, $message);
    }
    /**
     * @param Message $message, $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function messageDetails(Request $request, Message $message)
    {
        $user_id = $this->getUser()->id;
        $message_details = $message->with('from', 'to')->find($message->id);
        //record that this user has read the message;
        $read_by = $message_details->read_by;
        $read_by = addSingleElementToString($read_by, $user_id);
        $message_details->read_by = $read_by;
        $message_details->save();

        $copied_recipients = array();
        if ($message_details->copied_to != NULL) {
            $copied_to = substr(
                $message_details->copied_to,
                1,
                (strlen($message_details->copied_to) - 2)
            );
            $copied_to_array = explode('~', $copied_to);


            foreach ($copied_to_array as $user_id) :
                $recipient = User::find($user_id);
                if ($recipient) {

                    $copied_recipients[] = $recipient;
                }

            endforeach;
        }
        list($options, $recipients) = $this->extraOptions();

        $message_details->copied_recipients = $copied_recipients;

        $replies = $message_details->replies;
        $message_details->replies = [];
        if ($replies != NULL) {
            $message_details->replies = $this->formatMessageReplies($replies);
        }
        return $this->render(compact('message_details', 'options', 'recipients'));
    }
    public function formatMessageReplies($replies)
    {
        $replies_array = explode('~', $replies);

        foreach ($replies_array as $reply) :
            $decode_reply = json_decode($reply);

            $replier_detail = User::find($decode_reply->replier);

            $decode_reply->first_name = $replier_detail->first_name;
            $decode_reply->last_name = $replier_detail->last_name;
            $decode_reply->email = $replier_detail->email;
            $decode_reply->username = $replier_detail->username;

            $json_repy[] = $decode_reply;
        endforeach;


        return $json_repy;
    }

    // public function formatMessageReplies($replies)
    // {
    //     $replies_array = explode('~', $replies);
    //     $json_repy = [];
    //     foreach ($replies_array as $reply) :
    //         $decode_reply = json_decode($reply);

    //         $replier_detail = User::find($decode_reply->replier);
    //         if ($replier_detail) {


    //             $decode_reply->from = $replier_detail;


    //             $json_repy[] = $decode_reply;
    //         }
    //     endforeach;


    //     return $json_repy;
    // }
    /*public function forwardForm($id)
    {
        list($options, $recipients) = $this->extraOptions();

        return $this->render('messages.forward_message_form', compact('id','options','recipients'));
    }*/
}
