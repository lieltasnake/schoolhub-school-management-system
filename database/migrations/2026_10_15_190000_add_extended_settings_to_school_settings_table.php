<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            // School Profile extras
            $table->string('website', 191)->nullable()->after('address');
            $table->string('motto', 255)->nullable()->after('website');
            $table->string('logo_path', 255)->nullable()->after('motto');
            $table->string('facebook_url', 255)->nullable()->after('logo_path');
            $table->string('twitter_url', 255)->nullable()->after('facebook_url');
            $table->string('instagram_url', 255)->nullable()->after('twitter_url');
            $table->string('youtube_url', 255)->nullable()->after('instagram_url');
            $table->string('school_type', 50)->nullable()->after('youtube_url');
            $table->year('established_year')->nullable()->after('school_type');

            // Notification settings (stored as JSON flags)
            $table->boolean('notify_new_registration')->default(true)->after('established_year');
            $table->boolean('notify_payment_received')->default(true)->after('notify_new_registration');
            $table->boolean('notify_attendance_alert')->default(false)->after('notify_payment_received');
            $table->boolean('notify_exam_results')->default(true)->after('notify_attendance_alert');
            $table->boolean('notify_fee_due')->default(true)->after('notify_exam_results');
            $table->string('notification_email', 191)->nullable()->after('notify_fee_due');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'website', 'motto', 'logo_path',
                'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url',
                'school_type', 'established_year',
                'notify_new_registration', 'notify_payment_received',
                'notify_attendance_alert', 'notify_exam_results',
                'notify_fee_due', 'notification_email',
            ]);
        });
    }
};
