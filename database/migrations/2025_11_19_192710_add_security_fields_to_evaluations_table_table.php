<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->boolean('is_online')->default(false);
            $table->integer('duration_minutes')->nullable();

            // Options de sécurité
            $table->enum('security_level', ['none', 'medium', 'strict'])->default('none');
            $table->boolean('autosave_enabled')->default(true);
            $table->boolean('disable_copy_paste')->default(false);
            $table->boolean('disable_right_click')->default(false);
            $table->boolean('disable_printscreen')->default(false);
            $table->boolean('forbid_tab_switch')->default(false);
            $table->integer('max_focus_lost')->nullable();
            $table->boolean('auto_submit_on_time_end')->default(true);
        });
    }

    public function down()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'is_online',
                'duration_minutes',
                'security_level',
                'autosave_enabled',
                'disable_copy_paste',
                'disable_right_click',
                'disable_printscreen',
                'forbid_tab_switch',
                'max_focus_lost',
                'auto_submit_on_time_end',
            ]);
        });
    }
};
