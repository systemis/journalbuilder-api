<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('user', function (Blueprint $table) {
        $table->id();
        $table->string('email');
        $table->string('username');
        $table->longText('given_name');
        $table->longText('family_name');
        $table->longText('name');
        $table->longText('picture');
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
};
