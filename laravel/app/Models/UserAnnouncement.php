<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnnouncement extends Model
{
    use HasFactory;

    protected $table = 'user_announcement_data';
    protected $fillable = [
        'author', 'direction_sending', 'post_code_prefix_sending', 'post_code_postfix_sending', 'city_sending',
        'direction_receiving', 'post_code_prefix_receiving', 'post_code_postfix_receiving', 'city_receiving',
        'phone_number', 'email', 'expect_sending_date', 'experience_date', 'title',
        'additional_info', 'parcels_quantity', 'humans_quantity', 'pallets_quantity', 'animals_quantity', 'others_quantity'
    ];

    public function authorUser() {
        return $this->belongsTo( User::class, 'author' );
    }

    public function parcelAnnouncement() {
        return $this->hasMany( ParcelAnnouncement::class, 'announcement_id' );
    }

    public function humanAnnouncement() {
        return $this->hasMany( HumanAnnouncement::class, 'announcement_id' );
    }

    public function palletAnnouncement() {
        return $this->hasMany( PalletAnnouncement::class, 'announcement_id' );
    }

    public function animalAnnouncement() {
        return $this->hasMany( AnimalAnnouncement::class, 'announcement_id' );
    }

    public function otherAnnouncement() {
        return $this->hasMany( OtherAnnouncement::class, 'announcement_id' );
    }

    public function delete() {
        $this->parcelAnnouncement()->delete();
        $this->humanAnnouncement()->delete();
        $this->palletAnnouncement()->delete();
        $this->animalAnnouncement()->delete();
        $this->otherAnnouncement()->delete();
        return parent::delete();
    }
}
