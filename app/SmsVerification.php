<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsVerification extends Model
{
    //
    protected $fillable = ['user_id','code','status'];

    /**
     * Store SMS Verification code
     */
    public function store($request)
    {
        $this->fill($request->all());
        $sms = $this->save();
        return response()->json($sms, 200);
    }

    /**
     * 
     * Update sms verification 
     */
    public function updateModel($request)
    {
        $this->update($request->all());
        return $this;
    }
}
