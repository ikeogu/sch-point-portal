<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\CClass;
use App\Models\ClassTeacher;
use App\Models\StudentsInClass;
use App\Models\LocalGovernmentArea;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash;

class GuardiansController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $guardians = Guardian::with('user', 'guardianStudents')->where('school_id', $this->getSchool()->id)->get();
        return $this->render(compact('guardians'));
    }

    public function fetchGuardians()
    {
        $guardians = Guardian::with('user')->where('school_id', $this->getSchool()->id)->get();
        return $this->render(compact('guardians'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return $this->render('core::guardians.create');
    }

    /**
     * @param GuardianRequest $request
     * @param Guardian $guardian
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $school = $this->getSchool();
        $username = $this->generateUsername($school->id, 'parent');
        $request->username = $username;

        $user_obj = new User();
        list($user_id, $entry_status) = $user_obj->saveUserAsParent($request);

        if ($entry_status == 'new_entry') {
            $this->updateUniqNumDb($school->id, 'parent');
        }
        /*else {
            return response()->json(['message' => "User with email ($request->email) already exists"], 500);
        }*/

        $guardian = Guardian::where('user_id', $user_id)->first();

        if (!$guardian) {

            $guardian = new Guardian();
            $guardian->school_id = $school->id;
            $guardian->user_id = $user_id;
            $guardian->occupation = $request->occupation;
            $guardian->save();
        }

        return response()->json(compact('username'), 200);
    }

    // /**
    //  * @param $id
    //  * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
    //  */
    // public function edit($id)
    // {
    //     try {
    //         $guardian = Guardian::findOrFail($id);
    //         return $this->render('core::guardians.edit', compact('guardian'));
    //     } catch (ModelNotFoundException $ex) {
    //         Flash::error('Error: ' . $ex->getMessage());
    //         return redirect()->route('parents.index');
    //     }
    // }

    /**
     * @param GuardianRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $school = $this->getSchool();
        $user_id = $user->id;
        $user->saveUserAsParent($request, 'update');
        $guardian = Guardian::where('user_id', $user_id)->first();

        if (!$guardian) {
            $guardian = new Guardian();
        }

        $guardian->school_id = $school->id;
        $guardian->user_id = $user_id;
        $guardian->occupation = $request->occupation;
        $guardian->save();
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(Guardian $guardian)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $guardian = $guardian::with(['user.state', 'user.lga', 'guardianStudents.student.myClasses' => function ($query) use ($school_id, $sess_id) {
            $query->where(['sess_id' => $sess_id, 'students_in_classes.school_id' => $school_id])->orderBy('id', 'DESC');
        }, 'guardianStudents.student.myClasses.classTeacher.c_class', 'guardianStudents.student.user'])->find($guardian->id);
        return $this->render(compact('guardian'));
    }

    public function parentInfo($id = null)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $guardian_obj = new Guardian();
        $student_in_class_obj = new StudentsInClass();

        if ($id) {
            $view_by = 'non_parent';
            $guardian = $guardian_obj->fetchGuardianDetails($id);
        } else {
            $view_by = 'parent';
            if (!$this->getGuardian()) {
                // Flash::error('You do not have a ward in this school');
                return $this->render('errors.403');
                //return redirect()->route('dashboard');
            }
            $guardian = $guardian_obj->fetchGuardianDetails($this->getGuardian()->id);
        }
        if ($guardian === false) {
            return $this->render('errors.404');
        }
        $guardian->state = State::find($guardian->user->state_id);
        $guardian->lga = LocalGovernmentArea::find($guardian->user->lga_id);


        $wards = $guardian->wards;
        foreach ($wards as $student) {

            $student_current_class = $student_in_class_obj->fetchStudentInClass($student->id, $sess_id, $term_id, $school_id);
            if ($student_current_class) {
                if ($student_current_class->classTeacher) {
                    $class = CClass::find($student_current_class->classTeacher->class_id);
                    $student->c_class = $class;
                } else {
                    $student->c_class = '';
                }
            }
        }

        return $this->render('core::guardians.dashboard', compact('guardian', 'wards', 'view_by'));
    }

    public function guardianWards()
    {
        //$request = request()->all();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $id = $this->getGuardian()->id;
        $student_in_class_obj = new StudentsInClass();
        $guardian = Guardian::with(['guardianStudents.student.user'])->find($id);

        $wards = $guardian->guardianStudents;
        foreach ($wards as $student) {

            $student_current_class = $student_in_class_obj->fetchStudentInClass($student->id, $sess_id, $term_id, $school_id);


            $student->class = ($student_current_class) ? $student_current_class->classTeacher : null;
        }

        return $this->render(compact('guardian', 'wards'));
    }
    public function wardActivities($id)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $student_in_class_obj = new StudentsInClass();

        $student = Student::find($id);
        $student_current_class = $student_in_class_obj->fetchStudentInClass($id, $sess_id, $term_id, $school_id);
        $student->class_teacher_id = $student_current_class->class_teacher_id;
        $student->feePaymentMonitor = $student->feePaymentMonitor()->orderBy('id', 'DESC')->get();

        return $this->render('core::guardians.ward_activities', compact('student'));
    }
}
