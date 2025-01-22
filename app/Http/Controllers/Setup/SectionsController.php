<?php

namespace App\Http\Controllers\Setup;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Section;

class SectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $sections = Section::where('school_id', $this->getSchool()->id)->get();

        return $this->render(compact('sections'));
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

        $names = $request->name;

        $school_id = $this->getSchool()->id;
        foreach ($names as $name) :

            $section = new Section();
            $section->school_id = $school_id;
            $section->name = $name;
            $section->save();
        endforeach;

        return $this->index();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $section = Section::find($id);

        $section->name = $request->name;
        $section->save();


        return $this->index();
    }
}
