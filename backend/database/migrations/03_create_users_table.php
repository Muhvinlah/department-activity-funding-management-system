<?php
// database/migrations/2024_01_01_000003_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('user_id', 10)->primary()->comment('NIM (10 digits)');
            $table->string('full_name', 100);
            $table->string('email', 100)->unique();
            $table->string('password')->comment('Hashed password');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
