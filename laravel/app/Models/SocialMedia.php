<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasFactory;

    protected $table = 'social_media';
    protected $fillable = [
        'user_id', 'google_id', 'facebook_id'
    ];

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }
}
