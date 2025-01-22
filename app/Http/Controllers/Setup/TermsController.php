<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class TermsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $terms = Term::get();
        return $this->render(compact('terms'));
    }

    /**
     * Renders the activation screen
     *
     * @return \Illuminate\Http\Response
     */
    public function activateScreen()
    {
        //
        $terms = Term::orderBy('id')->get();
        $active_term = Term::find($this->getTerm()->id)->name;

        $termList = ['' => 'Select New Term'];
        if (isset($terms) && !empty($terms)) {
            foreach ($terms as $term) :
                $termList[$term->id] = $term->name;
            endforeach;
        }
        return $this->render('core::terms.activate', compact('termList', 'active_term'));
    }

    public function toggleTermActivation(Request $request, $id)
    {
        $other_terms = Term::get();
        foreach ($other_terms as $other_term) {
            $other_term->is_active = '0';
            $other_term->save();
        }
        $term = Term::find($id);

        $term->is_active = '1';

        $term->save();
        return $this->index();
    }
    /**
     * Method to activate term
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activate(Request $request)
    {


        /*$other_terms= Term::where('is_active', '1')->get();
        foreach ($other_terms as $other_term) {
            $other_term->is_active = '0';
            $other_term->save();
        }
        //activate this session
        $current_term = $request->current_term;
        $term = Term::find($current_term);

        $term->is_active = '1';

        $term->save();*/
        //$user = new User();
        $school = $this->getSchool();

        $school->current_term = $request->current_term;

        $school->save();

        return $this->render([]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function show(Term $term)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function edit(Term $term)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Term $term)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function destroy(Term $term)
    {
        //
    }
}
