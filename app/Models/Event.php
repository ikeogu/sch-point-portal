<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'school_id',
        'title',
        'description',
        'start',
        'end'
    ];
    //
    public function fetchEvent($school_id, $id = null)
    {
        $start_of_month = date('Y-m-d', strtotime(Carbon::now()->startOfMonth()));
        $events = Event::where('school_id', $school_id)->where('end', '>=', $start_of_month)->get();
        if ($id) {
            $events = Event::where(['id' => $id, 'school_id' => $school_id])->where('end', '>=', $start_of_month)->get();
        }
        return $events;
    }
}
