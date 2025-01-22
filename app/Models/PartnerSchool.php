<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerSchool extends Model
{
    //
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo(User::class);
    }

    public function addPartnerSchool($partner_id, $school_id)
    {
        $partner_school = PartnerSchool::where(['school_id' => $school_id])->first();

        if (!$partner_school) {
            $partner_school = new PartnerSchool();
            $partner_school->user_id = $partner_id;
            $partner_school->school_id = $school_id;
            $partner_school->save();
        }
    }
}
