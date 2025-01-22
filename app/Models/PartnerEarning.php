<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerEarning extends Model
{
    //
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school() {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner() {
        return $this->belongsTo(Partner::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function session() {
        return $this->belongsTo(SSession::class, 'sess_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term() {
        return $this->belongsTo(Term::class);
    }

    public function addPartnerEarning($data)
    {
    	$partner_earning = PartnerEarning::where(['partner_id'=>$data->partner_id, 'school_id'=>$data->school_id, 'sess_id'=>$data->sess_id, 'term_id'=>$data->term_id])->first();

    	if (!$partner_earning) {
    		$partner_earning = new PartnerEarning();
    		$partner_earning->partner_id = $date->partner_id;
        	$partner_earning->school_id = $data->school_id;
        	$partner_earning->sess_id = $data->sess_id;
        	$partner_earning->term_id = $data->term_id;
        	
    	}
    	$partner_earning->expected_amount = $data->expected_amount;
    	$partner_earning->amount_paid = $data->amount_paid;
    	$partner_earning->save();
    	
    }
}
