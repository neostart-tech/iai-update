<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('blog_comments', 'status')) {
            Schema::table('blog_comments', function (Blueprint $table) {
                $table->string('status')->default('pending'); // pending, approved, rejected
            });
        }

        if (!Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('status')->default('active'); // active, unsubscribed
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('blog_comments', 'status')) {
            Schema::table('blog_comments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
        Schema::dropIfExists('newsletter_subscribers');
    }
};
