<?php

namespace App\Http\Controllers\Setup;

use App\Gallery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GalleriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Gallery $gallery)
    {

        //
        $galleries = $gallery->get();
        return $this->render('event::galleries.index', compact('galleries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return $this->render('event::galleries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Gallery $gallery)
    {
        //
        $extension = $request->file('photo')->guessClientExtension();
        $folder = 'gallery';
        if ($extension == 'jpeg' || $extension == 'png' || $extension == 'jpg') {
            $name = "event_" . time() . "." . $extension;
            $file = $request->file('photo')->storeAs($folder, $name, 'public');
            $inputs['photo'] = 'storage/' . $file;
            $inputs['title'] = $request->title;
            $inputs['description'] = $request->content;
            $inputs['is_show'] = 'yes';
            $gallery->create($inputs);
        }
        if ($request->ajax()) {

            return 'success';
        }
        return redirect()->route('galleries.index');
    }

    /**
     * fetch the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function homePageGallery(Request $request)
    {
        //
        $galleries = Gallery::where('id', '>=', $request->id)->where('is_show', 'yes')->orderBy('id')->get();

        return (string) $this->render('event::galleries.gallery_home_ajax', compact('galleries'));
    }

    /**
     * fetch the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Gallery $id
     * @return \Illuminate\Http\Response
     */
    public function galleryDetails($id)
    {
        //
        $gallery = Gallery::find($id);

        return (string) $this->render('event::galleries.details', compact('gallery'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Gallery  $Gallery
     * @return \Illuminate\Http\Response
     */
    public function edit(Gallery $gallery)
    {
        //

        return $this->render('event::galleries.edit', compact('gallery'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Gallery  $Gallery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Gallery $gallery)
    {
        //
        $input = $request->all();
        $gallery->update($input);
        if ($request->ajax()) {

            return 'success';
        }
        return redirect()->route('galleries.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Gallery  $Gallery
     * @return \Illuminate\Http\Response
     */
    public function destroy(Gallery $gallery)
    {
        //
    }
}
