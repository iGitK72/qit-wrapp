<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QlinkConfiguration extends Model
{
    use HasFactory;
    
    /**
     * All fields are mass assignable except the following
     *
     */
    protected $guarded = ['id', 'created_at'];

    /**
     * Get the user associated with the Queue Configuration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
