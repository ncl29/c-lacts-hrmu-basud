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
        Schema::create('leave_attachments', function (Blueprint $table) {
            $table->id('attachment_id');
            $table->unsignedBigInteger('application_id');
            $table->string('document_type', 60)->default('supporting_document'); // e.g. medical_certificate, solo_parent_id, birth_certificate
            $table->string('document_label', 150)->nullable(); // e.g. "Medical Certificate", "Solo Parent ID"
            $table->string('original_filename', 255);
            $table->string('file_path', 500); // Relative path with forward slashes e.g. documents/2026/11/uuid.pdf
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64);
            $table->string('verification_status', 30)->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->text('verification_remarks')->nullable();
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('application_id')
                ->references('application_id')
                ->on('leave_applications')
                ->onDelete('cascade');

            $table->foreign('verified_by_user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['application_id', 'verification_status']);
            $table->index('sha256');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_attachments');
    }
};

