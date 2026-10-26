<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deposit/balance and Booking Terms acceptance (greenruby-web docs/booking-terms-review.md).
 * Additive only: every new column is nullable or has a default, existing rows keep their meaning
 * (payment_plan NULL = booked before deposits existed, i.e. full payment), and the code only writes
 * these columns once they exist (App\Payments\BookingSchema).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_plan', 16)->nullable()->after('total_amount');
            $table->string('payment_status', 32)->nullable()->after('payment_plan');
            // Same storage unit as total_amount: cents for USD, whole VND.
            $table->unsignedBigInteger('amount_paid')->default(0)->after('payment_status');
            $table->date('balance_due_date')->nullable()->after('amount_paid');
            $table->string('terms_version', 32)->nullable()->after('balance_due_date');
            $table->timestamp('terms_accepted_at')->nullable()->after('terms_version');
            $table->string('terms_locale', 8)->nullable()->after('terms_accepted_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            // bookings.payment_id still points at the first payment; booking_id lets one booking have
            // several payments (deposit, then balance).
            $table->unsignedBigInteger('booking_id')->nullable()->after('id');
            $table->string('purpose', 16)->nullable()->after('gateway');
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropColumn(['booking_id', 'purpose']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_plan', 'payment_status', 'amount_paid', 'balance_due_date', 'terms_version', 'terms_accepted_at', 'terms_locale']);
        });
    }
};
