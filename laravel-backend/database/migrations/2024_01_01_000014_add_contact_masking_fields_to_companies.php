<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('website');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->boolean('show_contact_info')->default(false)->after('contact_phone');
            $table->decimal('rating', 3, 2)->default(0)->after('show_contact_info');
            $table->integer('total_reviews')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone', 'show_contact_info', 'rating', 'total_reviews']);
        });
    }
};
