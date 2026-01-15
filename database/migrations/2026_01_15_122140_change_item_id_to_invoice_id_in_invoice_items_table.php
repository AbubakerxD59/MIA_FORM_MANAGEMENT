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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Drop the old item_id column if it exists
            if (Schema::hasColumn('invoice_items', 'item_id')) {
                $table->dropColumn('item_id');
            }
        });
        
        Schema::table('invoice_items', function (Blueprint $table) {
            // Add the new invoice_id column with foreign key
            $table->foreignId('invoice_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Drop foreign key and invoice_id column
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
        
        Schema::table('invoice_items', function (Blueprint $table) {
            // Restore the old item_id column
            $table->unsignedBigInteger('item_id')->nullable()->after('user_id');
        });
    }
};
