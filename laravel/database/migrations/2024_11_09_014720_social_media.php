<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('social_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'user_id' )->constrained()->cascadeOnDelete();
            $table->string( 'google_id' );
            $table->string( 'facebook_id' );
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::drop('social_media');
    }
};
