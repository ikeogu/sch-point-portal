<?php

namespace App\Http\Controllers;

use App\Models\CurriculumLevelGroup;
use App\Models\CurriculumCategory;
use App\Models\CurriculumLevel;
use Illuminate\Http\Request;

class CurriculumCategoryController extends Controller
{

    public function findCurriculumCategory($id)
    {
        return CurriculumCategory::find($id);
    }
    public function fetchCurriculumCategory()
    {
        $curricula = CurriculumCategory::with('curriculumLevelGroups')->get();
        return $this->render(compact('curricula'));
        // return response()->json(compact('curricula'), 200);
    }

    public function allCurriculumLevelGroups()
    {
        $curriculum_level_groups = CurriculumLevelGroup::with('curriculumCategory',)->orderBy('curriculum_category_id')->get();
        return $this->render(compact('curriculum_level_groups'));
        // return response()->json(compact('curriculum_level_groups'), 200);
    }
    public function allCurriculumLevels()
    {
        $curriculum_levels = CurriculumLevel::with('curriculumCategory', 'curriculumLevelGroup')->orderBy('curriculum_level_group_id')->get();
        return $this->render(compact('curriculum_levels'));
        // return response()->json(compact('curriculum_levels'), 200);
    }
    public function storeCurriculumLevelGroup(Request $request)
    {
        $curriculum_category_id = $request->curriculum_id;
        $curriculum_category = $this->findCurriculumCategory($curriculum_category_id);
        $name = $request->name;

        $curriculum_level_group = CurriculumLevelGroup::where(['curriculum_category_id' => $curriculum_category_id, 'name' => $name])->first();

        if ($curriculum_level_group) {
            // something exist return an error
            return response()->json('exist');
        }
        $curriculum_level_group = new CurriculumLevelGroup();
        $curriculum_level_group->curriculum_category_id = $curriculum_category_id;
        $curriculum_level_group->name = $name;
        $curriculum_level_group->curriculum = strtolower($curriculum_category->name);
        $curriculum_level_group->save();

        return $this->showCurriculumLevelGroup($curriculum_level_group);
    }
    public function  storeCurriculumLevel(Request $request)
    {
        $curriculum_category_id = $request->curriculum_category_id;
        $curriculum_level_group_id = $request->curriculum_level_group_id;
        $level_name = $request->level_name;
        $abbrev = $request->abbrev;

        $curriculum_level = CurriculumLevel::where(['curriculum_category_id' => $curriculum_category_id, 'level_name' => $level_name, 'curriculum_level_group_id' => $curriculum_level_group_id])->first();

        if ($curriculum_level) {
            // something exist return an error
            return response()->json('exist');
        }
        $curriculum_level = new CurriculumLevel();
        $curriculum_level->curriculum_category_id = $curriculum_category_id;
        $curriculum_level->curriculum_level_group_id = $curriculum_level_group_id;
        $curriculum_level->level_name = $level_name;
        $curriculum_level->abbrev = $abbrev;
        $curriculum_level->save();

        return $this->showCurriculumLevel($curriculum_level);
    }
    public function  updateCurriculumLevel(Request $request, CurriculumLevel $curriculum_level)
    {
        $curriculum_category_id = $request->curriculum_category_id;
        $curriculum_level_group_id = $request->curriculum_level_group_id;
        $level_name = $request->level_name;
        $abbrev = $request->abbrev;

        $curriculum_level->curriculum_category_id = $curriculum_category_id;
        $curriculum_level->curriculum_level_group_id = $curriculum_level_group_id;
        $curriculum_level->level_name = $level_name;
        $curriculum_level->abbrev = $abbrev;
        $curriculum_level->save();

        return $this->showCurriculumLevel($curriculum_level);
    }
    public function updateCurriculumLevelGroup(Request $request, CurriculumLevelGroup $curriculum_level_group)
    {
        $curriculum_category = $this->findCurriculumCategory($request->curriculum_id);
        $curriculum_level_group->curriculum_category_id = $request->curriculum_id;
        $curriculum_level_group->name = $request->name;
        $curriculum_level_group->curriculum = strtolower($curriculum_category->name);
        $curriculum_level_group->save();

        return $this->showCurriculumLevelGroup($curriculum_level_group);
    }


    public function showCurriculumLevelGroup(CurriculumLevelGroup $curriculum_level_group)
    {
        $curriculum_level_group = $curriculum_level_group->with('curriculumCategory')->where('id', $curriculum_level_group->id)->first();
        return $curriculum_level_group;
    }
    public function showCurriculumLevel(CurriculumLevel $curriculum_level)
    {
        $curriculum_level = $curriculum_level->with('curriculumCategory', 'curriculumLevelGroup')->where('id', $curriculum_level->id)->first();
        return $curriculum_level;
    }
}
