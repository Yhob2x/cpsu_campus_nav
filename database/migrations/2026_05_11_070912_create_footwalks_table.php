<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('footwalks')) {
            Schema::create('footwalks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('walkway');
                $table->string('color')->default('#3b82f6');
                $table->decimal('width', 5, 2)->default(2);
                $table->text('coordinates');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('footwalks');
    }
};