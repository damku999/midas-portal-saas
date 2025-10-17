<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadCustomerDocument(UploadedFile $uploadedFile, int $customerId, string $documentType, string $customerName): string
    {
        $timestamp = time();
        $extension = $uploadedFile->getClientOriginalExtension();
        $filename = $this->sanitizeFilename($customerName).'_'.$documentType.'_'.$timestamp.'.'.$extension;

        return $uploadedFile->storeAs(
            'customers/'.$customerId.'/'.$documentType.'_path',
            $filename,
            'public'
        );
    }

    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    public function getFileUrl(string $path): string
    {
        return Storage::url($path);
    }

    private function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
    }

    public function validateFileType(UploadedFile $uploadedFile, array $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png']): bool
    {
        return in_array($uploadedFile->getMimeType(), $allowedMimes);
    }

    public function validateFileSize(UploadedFile $uploadedFile, int $maxSizeKB = 1024): bool
    {
        return $uploadedFile->getSize() <= ($maxSizeKB * 1024);
    }

    public function uploadFile(UploadedFile $uploadedFile, string $directory): array
    {
        try {
            $timestamp = time();
            $extension = $uploadedFile->getClientOriginalExtension();
            $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = $this->sanitizeFilename($originalName).'_'.$timestamp.'.'.$extension;

            $filePath = $uploadedFile->storeAs($directory, $filename, 'public');

            return [
                'status' => true,
                'file_path' => $filePath,
                'filename' => $filename,
                'message' => 'File uploaded successfully',
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'file_path' => null,
                'filename' => null,
                'message' => $exception->getMessage(),
            ];
        }
    }
}
