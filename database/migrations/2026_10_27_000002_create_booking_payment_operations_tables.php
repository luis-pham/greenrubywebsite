<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Balance collection, refunds with approval and an audit trail (TPX decisions 3-6; greenruby-web
 * docs/booking-terms-review.md). Additive only; nothing reads these tables until the switches in
 * config/booking.php are on, and the admin screens only show them once they exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('amount_refunded')->default(0)->after('amount_paid');
            $table->timestamp('payment_overdue_at')->nullable()->after('balance_due_date');
            $table->timestamp('cure_deadline_at')->nullable()->after('payment_overdue_at');
        });

        // One row per message sent about a booking. The unique key makes the daily balance job
        // idempotent: a stage (request, reminder, notice) is sent at most once.
        Schema::create('booking_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('type', 40);
            $table->string('recipient', 191)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('sent_at');
            $table->unique(['booking_id', 'type']);
        });

        // Reservations requests, Finance approves and records the refund made in Stripe, PayPal or by
        // bank transfer. Amounts in the booking's storage unit (cents for USD, whole VND).
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 10);
            $table->string('reason', 32);
            $table->text('note')->nullable();
            $table->string('status', 16)->default('requested');
            $table->unsignedInteger('requested_by');
            $table->timestamp('requested_at');
            $table->unsignedInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->unsignedInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('provider_reference', 191)->nullable();
            $table->timestamps();
        });

        Schema::create('booking_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->string('action', 48);
            $table->unsignedInteger('user_id')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_audit_log');
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('booking_notifications');
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['amount_refunded', 'payment_overdue_at', 'cure_deadline_at']);
        });
    }
};
