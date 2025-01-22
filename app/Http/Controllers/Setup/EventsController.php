<?php

namespace App\Http\Controllers\Setup;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Event;
use App\Models\News;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $school_id = $this->getSchool()->id;
        $start_of_month = date('Y-m-d', strtotime(Carbon::now()->startOfMonth()));
        $events = Event::where('school_id', $school_id)/*->where('end', '>=', $start_of_month)*/->get();
        return $this->render(compact('events'));
    }
    public function events()
    {
        return $this->render('core::events.calendar');
    }

    public function addEvent(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $event = Event::where(['school_id' => $school_id, 'title' => $request->title, 'start' => $request->start])->first();


        if (!$event) {
            $event = new Event();
        }
        $event->title = $request->title;
        $event->description = str_replace("'", "`", $request->description);
        $event->targeted_audience = implode('~', $request->targeted_audience);
        $event->start = date('Y-m-d', strtotime($request->start));
        $event->end = date('Y-m-d', strtotime($request->end));

        if ($event->start < todayDate()) {
            return response()->json([
                'message' => 'wrong_start_date',
                'url' => ''
            ]);
        }
        if ($event->start > $event->end) {
            return response()->json([
                'message' => 'wrong_end_date',
                'url' => ''
            ]);
        }

        $event->school_id = $school_id;
        $event->save();

        return $this->index();
    }
    public function upcomingEvents()
    {
        $start_of_month = date('Y-m-d', strtotime(Carbon::now()->startOfMonth()));
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $events = Event::where('school_id', $school_id)
            ->where('end', '>=', $start_of_month)
            ->where(function ($q) use ($user) {
                return $q->where('targeted_audience', 'like', $user->role)
                    ->orWhere('targeted_audience', 'like', $user->role . '~%')
                    ->orWhere('targeted_audience', 'like', '%~' . $user->role . '~%')
                    ->orWhere('targeted_audience', 'like', '%~' . $user->role);
            })
            ->orderBy('id', 'DESC')->get();
        if ($events != '[]') {
            foreach ($events as $notification) {

                $seen_by_array  = explode('~', $notification->seen_by);

                $notification->seen_by_array = $seen_by_array;
            }
        }
        return $this->render(compact('events', 'user'));
    }
    public function deleteEvent(Event $event)
    {
        $event->delete();
        return $this->index();
    }

    public function updateEvent(Event $event)
    {
        $inputs = request()->all();
        $event->update($inputs);
        return $this->index();
    }

    public function eventCalendar(Event $event_obj)
    {
        $school_id = $this->getSchool()->id;
        $events = $event_obj->fetchEvent($school_id);

        return $this->render('core::events.calendar', compact('events'));
    }

    public function show(Event $event, $id, Request $request)
    {
        //
        $reader =  $request->reader;
        $notification = $event->find($id);

        $readers = $notification->seen_by;

        if ($readers != NULL && $readers != "") {
            $uniq_readers = explode('~', $readers . '~' . $reader);
            $uniq_readers = implode('~', array_unique($uniq_readers));
            $notification->seen_by = $uniq_readers;
        } else {
            $notification->seen_by = $reader;
        }
        $notification->save();
        return $this->render('core::events.show', compact('notification'));
    }
}
