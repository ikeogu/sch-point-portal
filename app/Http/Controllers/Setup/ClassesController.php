<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Behavior;
use App\Models\CClass;
use App\Models\ClassTeacher;
use App\Models\Level;
use App\Models\Section;
use App\Models\Skill;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\StudentsInClass;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class ClassesController extends Controller
{

    public function necessaryParams()
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        // $classTeacher = new ClassTeacher();
        $class_teachers = ClassTeacher::with(['c_class', 'level', 'staff.user', 'firstStudentInClass'])->where('school_id', $school_id)->get();

        $sections = Section::where('school_id', $school_id)->get();
        $levels = $this->getLevels();

        //this is necessary since we woild want to assign class to PC teachers from here
        $staff = Staff::with('user')->where(['school_id' => $school_id])->get();

        return array($class_teachers, $sections, $levels, $staff);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        list($class_teachers, $sections, $levels, $staff) = $this->necessaryParams();

        return $this->render(compact('class_teachers', 'sections', 'levels', 'staff'));
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewAllClasses()
    {
        list($classes, $classes_array, $level_array, $teachers, $section_array) = $this->necessaryParams();

        return $this->render('core::classes.all_classes', compact('classes', 'classes_array', 'level_array', 'teachers', 'section_array'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {

        /*list($classes,$classes_array,$level_array,$teachers) = $this->necessaryParams();

        return $this->render('core::classes.create', compact('classes_array','level_array'));*/

        return $this->index();
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    /*public function store(Request $request)
    {
        $class_ids = $request->class_id;
        $school_id = $this->getSchool()->id;

        foreach ($class_ids as $class_id) {

            $level_id = CClass::find($class_id)->level;
            $class_teacher = ClassTeacher::where(['class_id'=>$class_id, 'school_id'=>$school_id])->first();


            if( !$class_teacher){
                $class_teacher = new ClassTeacher();
                $class_teacher->school_id = $school_id;
                $class_teacher->class_id = $class_id;
                $class_teacher->level_id = $level_id;
                $class_teacher->teacher_id = NULL;
                $class_teacher->save();
            }
        }




        if($request->ajax()){

          return 'success';
        }
        return redirect()->route('all_classes');
    }
    */


    /**
     * @param ClassRequest $request
     * @param CClass $class
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {

        $school_id = $this->getSchool()->id;
        $level_id = $request->level;

        $level = Level::find($level_id);
        $pre_level = $level->id;
        $sections = $request->sections;

        foreach ($sections as $section) :
            $class = new CClass();
            //$class->school_id = $this->getSchool()->id;

            $class->name = $level->level . ' ' . $section; //concatenate the level to the section to form the class name
            $class->section = $section;
            $class->level = $level_id;
            $class->pre_level = $pre_level;
            $class->school_id = $school_id;

            $count = $class->where(['name' => $class->name, 'school_id' => $school_id])->count();

            if ($count < 1) {
                $class->save();

                $class_teacher = ClassTeacher::where(['class_id' => $class->id, 'school_id' => $school_id])->first();

                if (!$class_teacher) {
                    $class_teacher = new ClassTeacher();
                    $class_teacher->school_id = $school_id;
                    $class_teacher->class_id = $class->id;
                    $class_teacher->level_id = $level_id;
                    $class_teacher->teacher_id = NULL;
                    $class_teacher->save();
                }
            }

        endforeach;
        return $this->index();
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        //$layout = $this->layout;

        try {
            $class = CClass::findOrFail($id);
            $class_section = $class->section;
            $class_name = str_replace(' ' . $class_section, '', $class->name);
            return $this->render('core::classes.edit', compact('class_section', 'class_name', 'class'));
        } catch (ModelNotFoundException $ex) {
            return redirect()->route('classes.index');
        }
    }

    /**
     * @param ClassRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {

        $class = CClass::findOrFail($id);
        $class->name = $request->name . ' ' . $request->section;
        $class->section = $request->section;
        $class->save();

        return $this->index();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    /*
    public function getAssignClass(CClass $class_object)
    {

        $class_teachers = ClassTeacher::where('school_id', $this->getSchool()_id)->get();

        foreach($class_teachers as $class_teacher){
            $teacher_id = $class_teacher->teacher_id;
            if($teacher_id != NULL) {
                $teacher = Staff::find($teacher_id);

                $class_teacher->teacher = $teacher->user;
            }

        }
        $curriculum_array = explode('~', $this->getSchool()->curriculum);
        $class_data = $class_object->getCurriculumClasses ($this->getSchool(), $curriculum_array);
        //echo $class_teachers[0]->teacher->first_name;exit;
        $classes = [''=>'Select Class'];

        foreach ($class_data as $class) {
            $classes[$class->id] = $class->name;
        }



        $teachers = [];
        $data = Staff::where('school_id', $this->getSchool()_id)->get();
        foreach ($data as $teacher) {
            $teachers[$teacher->id] = $teacher->user->first_name . ' ' . $teacher->user->last_name;
        }
        return $this->render('core::classes.assign', compact('classes', 'teachers', 'class_teachers'));
    }
    */


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassLevel(Request $request)
    {
        $class = new CClass();
        $class_id = $request->class_id;

        list($levels, $sections) = $class->getClassLevelSection($class_id, $this->getSchool()->id);


        $html = '<select name="level_id" class="form-control selectpicker col-lg-6 getClassStudent" data-live-search="true" id="level_id" required>
                    ';
        foreach ($levels as $level) :
            $html .= '<option value="' . $level->id . '" >' . $level->level . '</option>';
        endforeach;
        $html .= '</select><br><br>
                    <select name="section_id" class="form-control selectpicker col-lg-6 getClassStudent" data-live-search="true" id="section_id" required>
                    ';
        foreach ($sections as $section) :
            $html .= '<option value="' . $section->id . '" >' . $section->name . '</option>';
        endforeach;
        $html .= '</select>';

        return response()->json($html);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassStudents(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $class_teacher_id = $request->class_teacher_id;

        $students_in_class = StudentsInClass::with([
            'classTeacher.subjectTeachers.subject',
            'classTeacher.c_class',
            'student' => function ($query) {
                $query->ActiveAndSuspended();
            },
            'student.studentGuardian.guardian.user', 'student.user', 'classTeacher.c_class', 'student.behavior' => function ($q) use ($school_id, $sess_id, $term_id) {
                $q->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id]);
            }, 'student.skill' => function ($q) use ($school_id, $sess_id, $term_id) {
                $q->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id]);
            },
        ])->where([
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
        ])->get();

        return $this->render(compact('students_in_class'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignClassTeacher(Request $request)
    {

        $class_teacher = ClassTeacher::find($request->class_teacher_id);

        $class_teacher->teacher_id = $request->teacher_id;
        $class_teacher->save();
        $teacher = Staff::with('user')->find($class_teacher->teacher_id);
        return $teacher;
    }


    public function assignStudentToClass(Request $request)
    {
        $class_teacher_id = $request->class_teacher_id;
        $student_id = $request->student_id;

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $student_in_class_obj = new StudentsInClass();
        $student_in_class_obj->removeStudentFromClass($student_id, $sess_id, $term_id, $school_id);
        $add_student = $student_in_class_obj->addStudentToClass($student_id, $class_teacher_id, $sess_id, $term_id, $school_id);

        if ($add_student) {
            return ClassTeacher::find($class_teacher_id)->c_class->name;
        }
        return 'Not Assigned';
    }

    public function classTeacherClasses()
    {
        $teacher = new Teacher();

        $staff = $this->getStaff();

        $school_id = $this->getSchool()->id;
        $class_teachers = $teacher->teacherClasses($staff->id, $school_id);
        $teacher = $staff->user->first_name . ' ' . $staff->user->last_name;
        return $this->render(compact('class_teachers', 'teacher'));
    }

    public function recordRatings(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $behavior_obj = new Behavior();
        $skill_obj = new Skill();

        $ratings = $request->ratings;
        $value = $request->value;
        $student_id = $request->student_id;
        $field = $request->field;

        if ($ratings == 'skill') {
            $skill_obj->rateStudent($school_id, $sess_id, $term_id, $value, $student_id, $field);
        }

        if ($ratings == 'behaviour') {
            $behavior_obj->rateStudent($school_id, $sess_id, $term_id, $value, $student_id, $field);
        }
    }
    public function destroy(Request $request, ClassTeacher $class_teacher)
    {
        $class = $class_teacher->c_class;
        $class->delete();
        $class_teacher->delete();

        return response()->json([], 204);
    }
}
