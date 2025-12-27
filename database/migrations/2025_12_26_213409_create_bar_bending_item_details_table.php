<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bar_bending_item_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained('bar_bending_locations')->onDelete('cascade');
            $table->string('name');
            $table->integer('number');
            $table->decimal('width', 5, 2);
            $table->decimal('height', 5, 2);
            $table->decimal('length', 5, 2);
            $table->integer('no_of_units');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bar_bending_item_details');
    }
};
