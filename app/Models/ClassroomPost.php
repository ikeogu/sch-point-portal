<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomPost extends Model
{
    //
    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
