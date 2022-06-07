<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qlink extends Model
{
    use HasFactory;
    
    /**
     * All fields are mass assignable except the following
     *
     */
    protected $guarded = ['id', 'created_at'];

    /**
     * Get the user that owns the qlink.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
