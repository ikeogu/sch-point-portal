<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\CurriculumCategory;
use App\Models\CurriculumLevel;
use App\Models\CurriculumLevelGroup;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $levels = $this->getLevels();
        return $this->render(compact('levels'));
    }

    public function fetchCurriculumCategories()
    {
        $curriculum_categories = CurriculumCategory::with('curriculumLevelGroups.curriculumLevels')->get();
        return response()->json(compact('curriculum_categories'), 200);
    }
    public function fetchSpecificCurriculumLevels()
    {
        $curriculum_level_groups = [];
        $school = $this->getSchool();
        if ($school) {

            $curriculum_category_id = CurriculumCategory::where('name', ucwords($school->curriculum))->first()->id;
            $curriculum_level_groups = CurriculumLevelGroup::with('curriculumLevels')->where('curriculum_category_id', $curriculum_category_id)->get();
        }

        // return $this->render(compact('curriculum_level_groups'));
        return response()->json(compact('curriculum_level_groups'), 200);
    }


    public function fetchLevelAndClass()
    {
        $levels = Level::with('classTeachers.c_class')->where('school_id', $this->getSchool()->id)->get();

        return $this->render(compact('levels'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubjectAndClass(Request $request, Level $level)
    {
        //
        $school_id = $this->getSchool()->id;
        $level_id = $request->level_id;

        if (isset($request->action) && $request->action == "fetch_student") {
            //this request wants us to fetch students for this level
            return $level->getLevelStudent($level_id, $school_id);
        }
        return $level->getLevelClasses($level_id, $school_id);
        /*$subjects = Subject::where('level_id', $request->level_id)->get();

        $classes = CClass::join('class_teachers', 'class_teachers.class_id', '=', 'classes.id')
                            ->where('classes.level', $request->level_id)
                            ->where('class_teachers.school_id', $school_id)
                            ->select('class_teachers.id as id', 'classes.name as name', 'classes.id as class_id')->get();


        $selected_subjects = [];
        foreach ($subjects as $subject ) :
            $selected_subjects[] = array('id'=>$subject->id, 'name'=>$subject->name.' ('.$subject->code.')');
        endforeach;

        $selected_classes = [];
        foreach ($classes as $class ) :
            $selected_classes[] = array('id'=>$class->id, 'name'=>$class->name);
        endforeach;

        //$selected_subjects =  json_encode($selected_subjects);
        //$selected_classes =  json_encode($selected_classes);

        $fetched_data = array('subject'=>$selected_subjects, 'class'=>$selected_classes);

        return json_encode($fetched_data);*/
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
        $school = $this->getSchool();
        $school_id = $school->id;
        $curriculum_level_group_id = $request->curriculum_level_group_id;
        $levels = $request->levels;

        foreach ($levels as $level) :

            //we want to get the level group and description for this level
            $new_level = Level::where(['school_id' => $school_id, 'level' => $level])->first();
            if (!$new_level) {
                $new_level = new Level();
            }
            $new_level->level = $level;

            $new_level->curriculum_level_group_id = $curriculum_level_group_id;

            $new_level->school_id = $school_id;
            // $new_level->level_group = $level_group;
            $new_level->description = $level;
            $new_level->save();

        endforeach;
        // Flash::success("Level Added successfully");
        return $this->index();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function show(Level $level)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function edit(Level $level)
    {
        //

        return $this->render(compact('level'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Level $level)
    {
        //
        //return $request->level;

        $level->level = $request->level;
        // $level->level_group = $request->level_group;
        $level->description = $request->description;
        $level->save();

        // Flash::success("Level updated successfully");
        return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy(Level $level)
    {
        //
        $level->delete();
        // Flash::success("Level deleted successfully");
        return $this->index();
    }
}
