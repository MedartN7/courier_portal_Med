<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCodeUkArchive extends Model
{
    use HasFactory;
    protected $table = 'post_codes_uk_archive';

    public function __construct(array $attributes = []) {
        $this->addColumnsFromJson();
        parent::__construct($attributes);

    }

    public function addColumnsFromJson() {
        $this->fillable = [
            'courier_announcement_id'
        ];
        $json = app(\App\Http\Controllers\JsonParserController::class)->ukPostCodeAction();
        // $all_postcodes = json_decode($json, true);
        $this->fillable = array_merge($this->fillable, array_keys( $json ) );
    }

    public function announcementId() {
        return $this->belongsTo( CourierAnnouncementArchive::class, 'courier_announcement_id' );
    }
}