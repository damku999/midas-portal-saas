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
        Schema::create('lead_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete()->comment('Lead reference');
            $table->string('document_type', 100)->comment('Document category (ID proof, address proof, etc.)');
            $table->string('file_name', 255)->comment('Original filename');
            $table->string('file_path', 500)->comment('Storage path');
            $table->integer('file_size')->comment('File size in bytes');
            $table->string('mime_type', 100)->comment('File MIME type');
            $table->foreignId('uploaded_by')->constrained('users')->comment('Uploaded by user');
            $table->timestamps();

            // Indexes for performance
            $table->index(['lead_id', 'document_type']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_documents');
    }
};
