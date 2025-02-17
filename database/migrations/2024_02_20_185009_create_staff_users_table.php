<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('staff_users', function (Blueprint $table) {
            $table->id('staff_user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password');
            $table->boolean('status')->default(true);
            $table->string('locale')->default('en');
            $table->unsignedBigInteger('timezone_id')->nullable()->constrained('timezones', 'timezone_id')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_users');
    }
};
