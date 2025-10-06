<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm các cột tracking mà không dựa vào company_id
            $table->integer('weekly_contact_count')->default(0);
            $table->timestamp('contact_count_reset_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('weekly_contact_count');
            $table->dropColumn('contact_count_reset_at');
        });
    }
};

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
