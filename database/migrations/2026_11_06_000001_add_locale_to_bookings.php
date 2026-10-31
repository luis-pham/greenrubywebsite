<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n phase 2B: the language of the page the guest booked on (en, vi, ja…), for guest e-mails.
 * bookings.terms_locale is not enough: it records the language of the TERMS accepted, which is 'en' for a
 * Japanese page (owner decision D), and it is only written when terms acceptance is required.
 *
 * Additive and reversible: one nullable column, no default, no backfill, no index. NULL (every existing row and
 * any booking created by code that does not write it) means "today's e-mail" (App\Services\Mail\BookingMailLocale).
 * On MySQL 8 a nullable column at the end is an INSTANT change; set lock_wait_timeout low when running it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bookings', 'locale')) {
            return;
        }
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('locale', 8)->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('bookings', 'locale')) {
            return;
        }
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
