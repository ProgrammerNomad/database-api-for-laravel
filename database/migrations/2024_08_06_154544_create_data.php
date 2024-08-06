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
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->json('Social');
            $table->string('CompanyName');
            $table->json('Telephones');
            $table->json('Emails');
            $table->json('Titles');
            $table->string('State');
            $table->string('Postcode');    
            $table->string('Country');
            $table->string('Vertical');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data');
    }
};
