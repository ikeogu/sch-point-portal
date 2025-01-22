<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProposal extends Model
{
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner() {
        return $this->belongsTo(Partner::class);
    }
    
    
    public function addProposal($data)
    {
    	$proposal = new SchoolProposal();
    	$amount = $data->amount;
    	if($amount == ""){
    	    $amount = 500;
    	}
    	$proposal->partner_id = $data->partner_id;
    	$proposal->school_name = $data->school_name;
    	$proposal->amount = $amount;
    	$proposal->save();
    	
    }
}
