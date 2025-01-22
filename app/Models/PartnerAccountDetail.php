<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAccountDetail extends Model
{
   

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner() {
        return $this->belongsTo(Partner::class);
    }

    public function addAccountDetails($data)
    {
    	$acc_details = PartnerAccountDetail::where(['partner_id'=>$data->partner_id])->first();

    	if (!$acc_details) {
    		$acc_details = new PartnerAccountDetail();
    		
    	}
    	$acc_details->partner_id = $data->partner_id;
    	$acc_details->bank = $data->bank;
    	$acc_details->account_no = $data->account_no;
    	$acc_details->account_name = $data->account_name;
    	$acc_details->account_type = $data->account_type;
    	$acc_details->save();
    	
    }
}
