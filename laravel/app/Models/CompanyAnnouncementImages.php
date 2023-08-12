<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAnnouncementImages extends Model
{
    use HasFactory;
    protected $table = 'company_announcement_images';
    protected $fillable = [
        'courier_announcement_id', 'image_name', 'image_link', 'image_description'
    ];
    public function announcementId() {
        return $this->belongsTo( CourierAnnouncement::class, 'courier_announcement_id' );
    }
}
