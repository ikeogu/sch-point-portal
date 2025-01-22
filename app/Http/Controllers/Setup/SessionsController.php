<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\SSession;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SessionsController extends Controller
{



    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $sessions = SSession::orderBy('id', 'DESC')->get();
        return $this->render(compact('sessions'));
    }

    /**
     * Renders the activation screen
     *
     * @return \Illuminate\Http\Response
     */
    public function activateScreen()
    {
        //
        $sessions = SSession::where('is_active', '1')->orderBy('id', 'DESC')->get();
        $active_session = $this->getSession()->name;
        $active_session_id = $this->getSession()->id;
        $sessionList = ['' => 'Select New Session'];
        if (isset($sessions) && !empty($sessions)) {
            foreach ($sessions as $session) :
                $sessionList[$session->id] = $session->name;
            endforeach;
        }
        return $this->render('core::sessions.activate', compact('sessionList', 'active_session', 'active_session_id'));
    }
    /**
     * Method to activate session
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activate(Request $request)
    {

        $school = $this->getSchool();

        $school->current_session = $request->current_session;

        $school->save();
        return $this->render([]);
    }

    /**
     * @param SessionRequest $request
     * @param SSession $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, SSession $session)
    {
        $inputs = request()->all();
        $exist = SSession::where('name', $request->name)->first();
        if (!$exist) {

            $session = $session->create($inputs);
        }
        return $this->index();
    }
    public function toggleSessionActivation(Request $request, $id)
    {
        $session = SSession::findOrFail($id);
        $session->is_active = $request->status;
        $session->save();
        return $this->index();
    }
    // public function deactivateSession($id)
    // {
    //     $session = SSession::findOrFail($id);
    //     //$session->delete();
    //     $session->is_active = '0';
    //     $session->save();
    //     return $this->index();
    // }



    /**
     * @param SessionRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $session = SSession::findOrFail($id);
            $session->update($request->all());
            return redirect()->route('sessions.index');
        } catch (ModelNotFoundException $ex) {
            return redirect()->route('sessions.index');
        }
    }
}
