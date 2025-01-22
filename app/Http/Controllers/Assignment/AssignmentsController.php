<?php

namespace App\Http\Controllers\Assignment;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentStudent;
use App\Models\AssignmentStudentMedia;
use App\Models\Student;
use App\Models\CClass;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\School;
use App\Models\User;
use App\Models\SubjectTeacher;
use App\Models\StudentsInClass;
use App\Models\ClassTeacher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash;
use App\Http\Requests\AssignmentRequest;
use Carbon\Carbon;

class AssignmentsController extends Controller
{
    public function allAssignments(Request $request)
    {
        set_time_limit(0);
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $start = $request->start_date;
        $end = $request->end_date;
        $term_id = $this->getTerm()->id;
        $class_teacher_id = $request->class_teacher_id;
        $subject_teacher_ids = SubjectTeacher::where(['school_id' => $school_id, 'class_teacher_id' => $class_teacher_id])->pluck('id');
        $assignments = Assignment::with('studentAssignments.student.user', 'subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class', 'subjectTeacher.staff.user')->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->whereIn('subject_teacher_id', $subject_teacher_ids)->where('created_at', '>=', $start)->where('created_at', '<=', $end)->orderBy('id', 'DESC')->get();
        return $this->render(compact('assignments'));
    }
    public function index(Request $request)
    {
        $assignment_object = new Assignment();

        $teacher_id = $this->getStaff()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $subject_teacher_id = $request->subject_teacher_id;
        $assignments = Assignment::with('studentAssignments.student.user', 'subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class', 'subjectTeacher.staff.user')->where(['subject_teacher_id' => $subject_teacher_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->orderBy('id', 'DESC')->get();

        // echo $subject_assignments[1]->assignment_details;exit;
        return $this->render(compact('assignments'));
    }

    public function fetchSubjects(Teacher $teacher)
    {
        $teacher_id = $this->getStaff()->id;
        $school_id = $this->getSchool()->id;
        $subject_teachers = $teacher->teacherSubjects($teacher_id, $school_id);
        // print_r( $subject_details );
        return $this->render(compact('subject_teachers'));
    }

    public function store(Request $request, Assignment $assignment)
    {
        //
        $school = $this->getSchool(); //new object of school

        // $folder_key = $school->getFolderKey($this->getSchool()->id);
        // $today = todayDate();
        //$folder = "schools/".$folder_key.'/assignments/'.$today;
        $inputs = $request->all();

        //$extension = $request->file('download_link')->guessClientExtension();
        //if( $extension == 'txt' || $extension == 'doc' || $extension == 'docx' || $extension == 'pdf') {
        //$name = "assign_".$request->subject_teacher_id.'_'. time() . "." . $extension;
        //$file = $request->file('download_link')->storeAs($folder, $name, 'public');
        //$inputs['download_link'] = $file;
        $inputs['assignment_details'] = $request->assignment_details;
        $inputs['subject_teacher_id'] = $request->subject_teacher_id;
        $inputs['school_id'] = $school->id;
        $inputs['sess_id'] = $this->getSession()->id;
        $inputs['term_id'] = $this->getTerm()->id;
        $assignment->create($inputs);
        $action = "Added new assignment";
        //log this event
        $this->teacherStudentEventTrail($request, $action, 'subject');
        $this->auditTrailEvent($request, $action);
        // return redirect()->route('assignments.index');

        //}
        //return redirect()->route('assignments.index');

    }

    public function getMark($id)
    {
        $student_assignments = AssignmentStudent::where('assignment_id', $id)
            ->orderBy('student_id')->get();
        //echo $id;exit;
        foreach ($student_assignments as $student_assignment) :

            $user_id = $student_assignment->student->user_id;

            $user = User::find($user_id);
            $student_assignment->user = $user;
        endforeach;

        return $this->render('assignment::assignments.mark', compact('student_assignments'));
    }

    public function scoreAssignment(Request $request)
    {
        $student_assignment = AssignmentStudent::find($request->id);

        $student_assignment->score = $request->score;
        $student_assignment->remark = $request->remark;
        $student_assignment->save();
    }

    public function postMark()
    {
    }

    public function destroy($id)
    {
        try {
            $assignment = Assignment::findOrFail($id);
            if (Storage::disk('public')->exists($assignment->assignment)) {
                Storage::disk('public')->delete($assignment->assignment);
            }
            $assignment->delete();
            $stud_assignments = AssignmentStudent::with('assignmentStudentMedias')->where('assignment_id', $id)->get();
            if ($stud_assignments->isNotEmpty()) {

                foreach ($stud_assignments as $stud_assignment) {
                    if ($stud_assignment->assignmentStudentMedias) {
                        foreach ($stud_assignment->assignmentStudentMedia as $media) {
                            Storage::disk('public')->delete($media->media_link);
                            $media->delete();
                        }
                    }

                    $stud_assignment->delete();
                }
            }
            // Flash::success("Assignment deleted successfully");
            // return redirect()->back();
        } catch (ModelNotFoundException $ex) {
            // Flash::error('Error: ' . $ex->getMessage());
            // return redirect()->route('assignments.index');
        }
    }

    public function assignments()
    {
        // DB::enableQueryLog();
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $assignments = Assignment::join('subjects', 'assignments.subject_id', '=', 'subjects.id')
            ->join('teachers', 'assignments.teacher_id', '=', 'teachers.id')
            ->where(['sess_id' => $sess_id, 'term_id' => $term_id])
            ->where('assignments.class_id', $this->user->student->class_id)
            ->select('assignments.deadline', 'assignments.assignment', 'subjects.name as subject_name', 'teachers.first_name', 'teachers.last_name')->get();
        // dd(DB::getQueryLog());
        return $this->render('assignment::assignments.assignments', compact('assignments'));
    }

    public function tackleAssignment(AssignmentStudent $stud_assignment_obj, School $sch, Request $request)
    {

        //return request()->all();
        $folder_key = $sch->getFolderKey($this->getSchool()->id);
        $today = todayDate();
        $folder = "schools/" . $folder_key . '/student_assignments/' . $today;

        $school = $this->getSchool();
        $student = $this->getStudent();
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $assignment_id = $request->assignment_id;
        $student_answer = $request->student_answer;

        $date = todayDateTime();

        $first_name = $this->user->first_name;
        $last_name = $this->user->last_name;

        $data = array(
            'school_id' => $school->id,
            'student_id' => $student->id,
            'assignment_id' => $assignment_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id

        );
        $stud_assignment = AssignmentStudent::with('assignmentStudentMedias')->where($data)->first();
        if ($stud_assignment) {

            // Unlink the old answer

            $data['id'] = $stud_assignment->id;
            $data['date'] = $date;
            $data['student_answer'] = $student_answer;

            foreach ($stud_assignment->assignmentStudentMedias as $media) {
                Storage::disk('public')->delete($media->media_link);
                $media->delete();
            }
            //Storage::disk('public')->delete($stud_assignment->answer_link);

            //update entry
            $assignment_student = $stud_assignment_obj->submitAssignment($data, 'update');
        } else {
            //insert new entry
            //$data['created_at'] = todayDateTime();
            //$data['updated_at'] = todayDateTime();
            $data['date'] = $date;
            $data['student_answer'] = $student_answer;
            $assignment_student = $stud_assignment_obj->submitAssignment($data);
        }


        if ($request->file('uploadedFiles') !== null) {
            $uploaded_files = $request->file('uploadedFiles');
            foreach ($uploaded_files as $uploaded_file) {
                $extension = strtolower($uploaded_file->guessClientExtension());
                if ($extension == 'jpg' || $extension == 'png' || $extension == 'jpeg') {


                    $name = "answer_" . $assignment_id . '_' . str_replace('/', '-', $student->registration_no) . "." . $extension;

                    $file = $uploaded_file->storeAs($folder, $name, 'public');


                    $data = array(
                        'assignment_student_id' => $assignment_student->id,
                        'media_link' => $file,

                    );

                    $assignment_media = new AssignmentStudentMedia();
                    $assignment_media->create($data);
                }
            }
        }

        // Flash::success('Assignment Submitted successfully');
        // return redirect()->route('student_assignments');

        /* if (isset($request->file('answer_link'))) {
            $extension = $request->file('answer_link')->guessClientExtension();
            if( $extension == 'doc' || $extension == 'docx' || $extension == 'pdf') {


                $name = "answer_".$assignment_id.'_'. str_replace('/', '-', $student->registration_no). "." . $extension;

                $file = $request->file('answer_link')->storeAs($folder, $name, 'public');

                $data['answer_link'] = $file;

                $data = array(
                            'student_id' => $student->id,
                            'assignment_id' => $assignment_id,
                            'sess_id' => $sess_id,
                            'term_id' => $term_id,
                            'date' => $date,
                            'answer_link' => $file

                        );
                Flash::success('Assignment Submitted successfully');

            }else{
                Flash::error('Invalid File Extension. Make sure your file is either a .doc, .docx or .pdf file');
            }
        }

        return redirect()->route('student_assignments');*/
    }

    public function studentAssignments($id = NULL)
    {
        set_time_limit(0);
        $today = date('Y-m-d H:i:s', strtotime('now'));
        // DB::enableQueryLog();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        if ($id != NULL) {
            $stud_id  = $id;
            $parent_view = TRUE;
        } else {
            $stud_id = $this->getStudent()->id;
            $parent_view = FALSE;
        }
        $student_in_class_obj = new StudentsInClass();
        $student = Student::find($stud_id);
        $student_in_class = StudentsInClass::where([
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'student_id' => $stud_id,
        ])->first();

        $class_teacher_id = $student_in_class->class_teacher_id;
        $subject_teacher_ids = SubjectTeacher::where('class_teacher_id', $class_teacher_id)->pluck('id');
        $assignments = Assignment::with(['subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class', 'subjectTeacher.staff.user'])->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->whereIn('subject_teacher_id', $subject_teacher_ids)->where('deadline', '>', $today)->orderBy('id', 'DESC')->get();

        // $assignments = Assignment::join('subject_teachers', 'assignments.subject_teacher_id', '=', 'subject_teachers.id')
        //     ->join('subjects', 'subject_teachers.subject_id', '=', 'subjects.id')
        //     ->join('staff', 'staff.id', '=', 'subject_teachers.teacher_id')
        //     ->join('users', 'users.id', '=', 'staff.user_id')
        //     ->where('subject_teachers.class_teacher_id', $class_teacher_id)
        //     ->where(['sess_id' => $sess_id, 'term_id' => $term_id])
        //     ->orderBy('assignments.id', 'DESC')
        //     ->select('assignments.id as assignment_id', 'assignments.assignment_details', 'assignments.deadline', 'assignments.download_link', 'subjects.name as subject_name', 'users.first_name', 'users.last_name')->get();
        // foreach ($assignments as $assignment) :
        //     $assignment->is_submitted = 0;
        //     $assignment->status = 0;
        //     $assignment->stud_assignment = '';
        //     $stud_assignment = AssignmentStudent::where(['student_id' => $stud_id, 'assignment_id' => $assignment->assignment_id])->first();
        //     if ($stud_assignment) {
        //         $assignment->stud_assignment = $stud_assignment;
        //         $assignment->is_submitted = 1;
        //         $assignment->ass_stud_id = $stud_assignment->id;
        //         $assignment->answer_link = $stud_assignment->answer_link;
        //     }

        //     if ($assignment->deadline > todayDateTime()) {

        //         $assignment->status = 1;
        //     }
        // endforeach;
        // dd(DB::getQueryLog());
        return $this->render(compact('assignments', 'student', 'parent_view'));
    }


    public function studentAnswerDetails(Request $request, $id)
    {
        $can_edit = false;
        $today = date('Y-m-d H:i:s', strtotime('now'));
        if (isset($request->student_id) && $request->student_id != "") {
            $student = Student::find($request->student_id);
        } else {
            $student = $this->getStudent();
            $can_edit = true;
        }
        $assignment = Assignment::find($id);
        if ($assignment->deadline < $today) {
            $can_edit = false;
        }
        $assignment_to_tackle = AssignmentStudent::with('assignment')->where(['student_id' => $student->id, 'assignment_id' => $id])->first();
        if ($assignment_to_tackle) {
            if ($assignment_to_tackle->score != null) {
                $can_edit = false;
            }
        }
        //return $assignment_to_tackle;
        return $this->render(compact('assignment_to_tackle', 'student', 'can_edit'));
    }

    public function tackleAssignmentForm(Request $request, $id)
    {
        $can_edit = false;
        if (isset($request->student_id) && $request->student_id != "") {
            $student = Student::find($request->student_id);
        } else {
            $student = $this->getStudent();
            $can_edit = true;
        }
        $assignment_to_tackle = Assignment::find($id);

        $assignment_to_tackle->studentAssignment = $assignment_to_tackle->studentAssignment()->where('student_id', $student->id)->first();
        if ($assignment_to_tackle->studentAssignment) {
            if ($assignment_to_tackle->studentAssignment->score != null) {
                $can_edit = false;
            }
        }
        //return $assignment_to_tackle;
        return $this->render('assignment::assignments.tackle_assignment_form', compact('assignment_to_tackle', 'student', 'can_edit'));
    }

    public function teacherClassAssignment(Assignment $assignment)
    {
        $teacher_id = $this->getStaff()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $assignments = Assignment::join('subject_teachers', 'assignments.subject_teacher_id', '=', 'subject_teachers.id')
            ->join('class_teachers', 'subject_teachers.class_teacher_id', '=', 'class_teachers.id')
            ->join('staff', 'staff.id', '=', 'subject_teachers.teacher_id')
            ->join('subjects', 'subject_teachers.subject_id', '=', 'subjects.id')
            ->join('users', 'users.id', '=', 'staff.user_id')
            ->where('class_teachers.teacher_id', $teacher_id)
            ->where(['sess_id' => $sess_id, 'term_id' => $term_id])
            ->orderBy('assignments.id', 'DESC')
            ->select('assignments.*')->get();
        $assignment_details = $assignment->teacherAssignmentsDetails($assignments);
        //return $assignment_details;
        return $this->render('assignment::assignments.teacher_class_assignment', compact('assignment_details'));
    }
}
