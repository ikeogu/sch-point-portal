<?php

namespace App\Http\Controllers\Result;

use App\CClass;
use App\Http\Requests\RemarkRequest;
use App\Remark;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class RemarksController extends Controller
{
    public function create()
    {
        $teacher = Auth::user()->teacher;
        $classes = [];
        $class_id = "";
        foreach ($teacher->classes as $class) {
            if ($class_id == "") {
                $class_id = $class->id;
            }
            $classes[$class->id] = $class->name . ' (' . $class->level . ')';
        }
        return $this->render('remarks.create', compact('classes'));
    }

    public function getStudents()
    {
        $class_id = request()->get('class_id');
        $student_id = request()->get('student_id');
        $class = CClass::find($class_id);
        $html = '';
        foreach ($class->students as $student) {
            $selected = ($student_id == $student->id) ? 'SELECTED' : '';
            $html .= '<option value="' . $student->id . '" ' . $selected . '>' . $student->first_name . ' ' . $student->last_name . '</option>';
        }
        return response()->json($html);
    }

    public function store(RemarkRequest $request, Remark $remark)
    {
        $inputs = $request->all();
        $inputs['school_id'] = $this->getSchool()->id;
        $inputs['teacher_id'] = $this->getStaff()->id;
        $remark->create($inputs);
        return redirect()->route('dashboard');
    }

    public function edit($id)
    {
        try {
            $remark = Remark::findOrFail($id);
            $teacher = Auth::user()->teacher;
            $classes = [];
            $class_id = "";
            foreach ($teacher->classes as $class) {
                if ($class_id == "") {
                    $class_id = $class->id;
                }
                $classes[$class->id] = $class->name . ' (' . $class->level . ')';
            }
            return $this->render('remarks.edit', compact('remark', 'classes'));
        } catch (ModelNotFoundException $ex) {
            return redirect()->route('dashboard');
        }
    }

    public function update(RemarkRequest $request, $id)
    {
        try {
            $remark = Remark::findOrFail($id);
            $remark->update($request->all());
            return redirect()->route('dashboard');
        } catch (ModelNotFoundException $ex) {
            return redirect()->route('dashboard');
        }
    }
}
