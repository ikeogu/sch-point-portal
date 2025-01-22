<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function partnerEarnings()
    {
        return $this->hasMany(PartnerEarning::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function schoolProposals()
    {
        return $this->hasMany(SchoolProposal::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function subPartner()
    {
        return $this->hasMany(SubPartner::class);
    }

    public function referrer()
    {
        return $this->belongsTo(SubPartner::class, 'child_partner_id', 'id');
    }
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    // public function sendPasswordResetNotification($token)
    // {
    //     $this->notify(new PartnerResetPasswordNotification($token));
    // }


}
