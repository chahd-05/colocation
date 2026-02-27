<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (! Schema::hasColumn('invitations', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('colocation_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('invitations', 'status')) {
                $table->string('status')->default('pendding')->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (Schema::hasColumn('invitations', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('invitations', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
