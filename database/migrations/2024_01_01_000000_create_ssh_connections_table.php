<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ssh_connections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->string('username');
            $table->integer('port')->default(22);
            $table->string('private_key')->nullable();
            $table->string('password')->nullable();
            $table->boolean('locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ssh_connections');
    }
};