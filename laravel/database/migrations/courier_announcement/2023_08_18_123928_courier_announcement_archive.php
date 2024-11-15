<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('courier_announcement_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author'); // klucz obcy
            $table->string('description');
            $table->date('experience_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists( 'courier_announcement_archive' );
    }
};