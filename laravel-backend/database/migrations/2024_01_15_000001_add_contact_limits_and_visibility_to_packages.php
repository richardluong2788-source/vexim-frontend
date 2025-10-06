<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('contact_limit')->default(1)->after('features');
            $table->integer('visibility_level')->default(1)->after('contact_limit');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->integer('visibility_level')->default(1)->after('package_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['contact_limit', 'visibility_level']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('visibility_level');
        });
    }
};
