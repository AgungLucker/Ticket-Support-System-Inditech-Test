<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Low, Medium, High, Critical
            $table->string('slug')->unique();
            $table->string('color')->default('#94a3b8');
            $table->integer('level')->default(0); // For sorting purposes (e.g. Critical = 4)
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priorities');
    }
};
