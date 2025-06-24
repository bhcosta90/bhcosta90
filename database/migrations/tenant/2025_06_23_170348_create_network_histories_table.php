<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('network_histories', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('network_id')->constrained('networks');
            $table->ipAddress();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_histories');
    }
};
