<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consents extends Model
{
    use HasFactory;
    protected $table = 'consents';
    protected $fillable = [
        'author', 'marketing'
    ];

    public function authorUser() {
        return $this->belongsTo( User::class, 'author' );
    }

}
