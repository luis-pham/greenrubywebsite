<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reservations desk (rsv.greenruby.cruises): stored contact-form messages, the desk's own workflow per
 * enquiry (status, owner, follow-up date, notes), login tokens and an access log. Bookings, payments and
 * refunds stay in their own tables; the desk only reads them. Additive: nothing existing is changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('email', 191)->nullable()->index();
            $table->string('phone', 50)->nullable()->index();
            $table->text('message');
            $table->string('locale', 16)->nullable();
            $table->string('source', 32)->default('contact_form');
            $table->string('page_url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('mail_status', 16)->default('pending'); // pending | sent | failed
            $table->text('mail_error')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        // Workflow of one enquiry: item_type contact (contact_messages.id) or booking (bookings.id, inquiries included).
        Schema::create('rsv_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_type', 16);
            $table->unsignedBigInteger('item_id');
            $table->string('status', 16)->default('new');
            $table->unsignedInteger('assigned_to')->nullable()->index();
            $table->date('follow_up_at')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['item_type', 'item_id']);
        });

        Schema::create('rsv_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_type', 16);
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('user_id');
            $table->text('body');
            $table->timestamp('created_at')->nullable();
            $table->index(['item_type', 'item_id']);
        });

        Schema::create('rsv_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->char('token_hash', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('created_at');
        });

        // Who looked at or changed guest data (personal data and payments).
        Schema::create('rsv_audit_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('action', 32);
            $table->string('item_type', 16)->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->json('detail')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsv_audit_log');
        Schema::dropIfExists('rsv_tokens');
        Schema::dropIfExists('rsv_notes');
        Schema::dropIfExists('rsv_states');
        Schema::dropIfExists('contact_messages');
    }
};
