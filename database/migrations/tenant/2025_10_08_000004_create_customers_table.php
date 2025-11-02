<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 125);
            $table->string('email', 125)->nullable();
            $table->string('mobile_number', 125)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('wedding_anniversary_date')->nullable();
            $table->date('engagement_anniversary_date')->nullable();
            $table->enum('type', ['Corporate', 'Retail'])->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('pan_card_number', 50)->nullable();
            $table->string('aadhar_card_number', 50)->nullable();
            $table->string('gst_number', 50)->nullable();
            $table->string('pan_card_path', 150)->nullable();
            $table->string('aadhar_card_path', 150)->nullable();
            $table->string('gst_path', 150)->nullable();
            $table->unsignedBigInteger('family_group_id')->nullable()->comment('Family group this customer belongs to');
            $table->string('password', 255)->nullable()->comment('Password for customer login');
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('must_change_password')->default(0);
            $table->timestamp('email_verified_at')->nullable()->comment('Email verification timestamp');
            $table->string('email_verification_token', 255)->nullable();
            $table->timestamp('password_reset_sent_at')->nullable();
            $table->string('password_reset_token', 255)->nullable();
            $table->timestamp('password_reset_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
