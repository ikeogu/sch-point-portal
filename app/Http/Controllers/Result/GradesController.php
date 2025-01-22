<?php

namespace App\Http\Controllers\Result;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use App\Models\Grade;
use App\Models\CurriculumLevelGroup;
use Laracasts\Flash\Flash;

class GradesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $grades = $this->getGrades(); //Grade::where('school_id', $this->getSchool()->id)->get();

        return $this->render(compact('grades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $school = $this->getSchool();
        $curriculum = $school->curriculum;
        $curriculum_levels = CurriculumLevelGroup::where('curriculum', $curriculum)->get();
        return $this->render(compact('curriculum_levels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $grades = json_decode(json_encode($request->grades));
        $curriculum_level_group_id = $request->curriculum_level_group_id;
        $school_id = $this->getSchool()->id;
        foreach ($grades as $each_grade) :
            $existing_grade = Grade::where(['curriculum_level_group_id' => $curriculum_level_group_id, 'school_id' => $school_id, 'grade' => $each_grade->grade])->first();
            if (!$existing_grade) {
                $grade = new Grade();
                $grade->grade = $each_grade->grade;
                $grade->school_id = $school_id;
                $grade->interpretation = $each_grade->interpretation;
                //$grade->grade_range = $each_grade->grade_range;
                $grade->upper_limit = $each_grade->upper_limit;
                $grade->lower_limit = $each_grade->lower_limit;
                $grade->grade_point = $each_grade->grade_point;
                $grade->curriculum_level_group_id = $curriculum_level_group_id;
                $grade->color_code = randomColorCode(); //this is from helpers in form of '#FFFFFF'
                $grade->save();
            }
        endforeach;

        return response()->json([], 200);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function show(Grade $grade)
    {
        //
        $grade = $grade->with('curriculumLevelGroup')->find($grade->id);
        return response()->json(compact('grade'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $grade = Grade::find($id);
        $school = $this->getSchool();
        $curriculum = $school->curriculum;
        $curriculum_levels = CurriculumLevelGroup::where('curriculum', $curriculum)->get();
        return $this->render('result::grades.edit', compact('grade', 'curriculum_levels'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Grade $grade)
    {
        //
        // $curriculum_level_group_id = $request->curriculum_level_group_id;

        // $grade->curriculum_level_group_id = $curriculum_level_group_id;
        $grade->grade = $request->grade;
        $grade->interpretation = $request->interpretation;
        //$grade->grade_range = $request->grade_range;
        $grade->upper_limit = $request->upper_limit;
        $grade->lower_limit = $request->lower_limit;
        $grade->grade_point = $request->grade_point;
        $grade->save();


        return $this->show($grade);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grade $grade)
    {
        //
        $grade->delete();
    }
}
