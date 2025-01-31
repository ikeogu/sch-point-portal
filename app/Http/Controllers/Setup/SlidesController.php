<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlidesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Slide $slide)
    {

        //
        $slides = $slide->get();
        return $this->render('slides.index', compact('slides'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return $this->render('slides.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Slide $slide)
    {
        //
        $extension = $request->file('photo')->guessClientExtension();
        $folder = 'slide';
        if( $extension == 'jpeg' || $extension == 'png' || $extension == 'jpg' ) {
            $name = "event_". time() . "." . $extension;
            $file = $request->file('photo')->storeAs($folder, $name, 'public');
            $inputs['photo'] = 'storage/'.$file;
            $inputs['description'] = $request->content;
            $inputs['is_show'] = 'yes';
            $slide->create($inputs);


        }
        if($request->ajax()){

          return 'success';
        }
        return redirect()->route('slides.index');
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Side  $Slide
     * @return \Illuminate\Http\Response
     */
    public function edit(Slide $slide)
    {
        //

        return $this->render('slides.edit', compact('slide'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Side  $Slide
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Slide $slide)
    {
        //
        $input = $request->all();
        $slide->update($input);
        if($request->ajax()){

          return 'success';
        }
        return redirect()->route('slides.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Side  $Slide
     * @return \Illuminate\Http\Response
     */
    public function destroy(Slide $slide)
    {
        //
    }
}