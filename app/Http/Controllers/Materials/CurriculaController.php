<?php

namespace App\Http\Controllers\Materials;

use App\Models\Curriculum;
use App\Models\Teacher;
use App\Models\School;
use App\Http\Controllers\Controller;
use App\Http\Requests\CurriculumRequest;
use App\Models\SubjectTeacher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash;
use Illuminate\Http\Request;

class CurriculaController extends Controller
{
    public function index(Curriculum $curriculum, Teacher $teacher)
    {
        $school = $this->getSchool();
        $teacher_id = $this->getStaff()->id;
        $curricula = Curriculum::join('subject_teachers', 'curricula.subject_teacher_id', '=', 'subject_teachers.id')
            ->where('subject_teachers.teacher_id', $teacher_id)
            ->orderBy('curricula.id', 'DESC')
            ->select('curricula.id', 'subject_teachers.id as subject_teacher_id', 'term_id', 'title', 'curriculum', 'curricula.created_at')
            ->get();

        $curricula = $curriculum->teacherCurricula($curricula); //from helpers
        $details = $teacher->teacherSubjects($teacher_id, $school->id);

        $subject_details = subjectTeacherSelectWithClassLevel($details);

        return $this->render('material::curricula.index', compact('curricula', 'subject_details'));
    }

    public function teacherCurriculum()
    {
        $school = $this->getSchool();
        $teacher_id = $this->getStaff()->id;
        $term_id = $this->getTerm()->id;
        $subject_teachers = SubjectTeacher::with(['subject', 'classTeacher.c_class', 'curriculum' => function ($q) use ($term_id) {
            $q->where('term_id', $term_id);
        }])->where('teacher_id', $teacher_id)->get();

        return response()->json(compact('subject_teachers'), 200);
    }

    public function store(Request $request)
    {
        $school = $this->getSchool();
        $teacher_id = $this->getStaff()->id;
        $term_id = $this->getTerm()->id;

        $subject_teacher_id = $request->subject_teacher_id;
        $description = $request->description;

        $curriculum = Curriculum::where(['school_id' => $school->id, 'subject_teacher_id' => $subject_teacher_id, 'term_id' => $term_id])->first();

        if (!$curriculum) {
            $curriculum = new Curriculum();
        }
        $curriculum->school_id = $school->id;
        $curriculum->teacher_id = $teacher_id;
        $curriculum->subject_teacher_id = $subject_teacher_id;
        $curriculum->term_id = $term_id;
        $curriculum->description = $description;
        $curriculum->save();
        return response()->json([], 204);
    }

    // public function store(Request $request, Curriculum $curriculum)
    // {
    //     $school = new School(); //new object of school

    //     $folder_key = $school->getFolderKey($this->getSchool()->id);
    //     $today = todayDate();
    //     $folder = "schools/" . $folder_key . '/curricula/' . $today;

    //     $subject_teacher_ids = $request->subject_teacher_id;

    //     $inputs = $request->all();

    //     $extension = $request->file('curriculum')->guessClientExtension();
    //     if ($extension == 'doc' || $extension == 'docx' || $extension == 'pdf') {
    //         $name = "curriculum_" . time() . "." . $extension;
    //         $file = $request->file('curriculum')->storeAs($folder, $name, 'public');
    //         foreach ($subject_teacher_ids as $subject_teacher_id) {

    //             $inputs['curriculum'] = $file;
    //             $inputs['teacher_id'] = $this->getStaff()->id;
    //             $inputs['school_id'] = $this->getSchool()->id;
    //             $inputs['subject_teacher_id'] = $subject_teacher_id;
    //             $curriculum->create($inputs);
    //         }
    //         return redirect()->route('curricula.index');
    //     }
    //     return redirect()->route('curricula.index');
    // }

    public function update()
    {
    }

    public function destroy($id)
    {
        try {
            $curriculum = Curriculum::findOrFail($id);
            if (Storage::disk('public')->exists($curriculum->curriculum)) {
                Storage::disk('public')->delete($curriculum->curriculum);
            }
            $curriculum->delete();
            return redirect()->back();
        } catch (ModelNotFoundException $ex) {
            return redirect()->route('curricula.index');
        }
    }

    public function curricula()
    {
        $curriculum = new Curriculum();
        $curricula = Curriculum::where('school_id', $this->getSchool()->id)
            ->orderBy('curricula.id', 'DESC')
            ->get();
        $curricula = $curriculum->teacherCurricula($curricula);
        return $this->render('material::curricula.curricula', compact('curricula'));
    }
    public function subjectCurriculum(SubjectTeacher $subject_teacher)
    {
        $school_id = $this->getSchool()->id;
        $term_id = $this->getTerm()->id;
        $curriculum = Curriculum::where(['school_id' => $school_id, 'term_id' => $term_id, 'subject_teacher_id' => $subject_teacher->id])->first();
        if (!$curriculum) {
            $curriculum = new Curriculum();
            $curriculum->description = 'No Curriculum was set for this subject. Kindly inform your subject teacher';
            $curriculum->curriculum = null;
        }
        return response()->json(compact('curriculum'), 200);
    }
}
