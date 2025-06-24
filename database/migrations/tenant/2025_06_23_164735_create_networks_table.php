<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('networks', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('name');
            $table->string('endpoint');
            $table->string('clicks')->default('0');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('networks');
    }
};
