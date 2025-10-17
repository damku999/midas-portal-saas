<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureFileUploadService
{
    private readonly array $allowedMimeTypes;

    private readonly array $allowedExtensions;

    private readonly int $maxFileSize;

    private readonly string $uploadPath;

    private readonly bool $scanForMalware;

    public function __construct()
    {
        $this->allowedMimeTypes = config('security.file_uploads.allowed_mime_types', [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $this->allowedExtensions = config('security.file_uploads.allowed_extensions', [
            'pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx',
        ]);

        $this->maxFileSize = config('security.file_uploads.max_size', 10240) * 1024; // Convert KB to bytes
        $this->uploadPath = config('security.file_uploads.upload_path', 'secure-uploads');
        $this->scanForMalware = config('security.file_uploads.scan_for_malware', false);
    }

    /**
     * Upload a file with comprehensive security checks
     */
    public function upload(UploadedFile $uploadedFile, string $category = 'general', ?int $userId = null): array
    {
        try {
            // Basic validation
            $this->validateFile($uploadedFile);

            // Generate secure filename
            $filename = $this->generateSecureFilename($uploadedFile);

            // Create directory structure
            $directory = $this->createSecureDirectory($category, $userId);

            // Full path for the file
            $fullPath = $directory.'/'.$filename;

            // Additional security checks
            $this->performSecurityChecks($uploadedFile);

            // Store the file
            $storedPath = Storage::disk('local')->putFileAs($directory, $uploadedFile, $filename);

            if (! $storedPath) {
                throw new Exception('Failed to store file');
            }

            // Post-upload verification
            $this->verifyUploadedFile($storedPath);

            // Log successful upload
            $this->logFileOperation('upload_success', $filename, $userId, [
                'original_name' => $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize(),
                'mime_type' => $uploadedFile->getMimeType(),
                'category' => $category,
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $storedPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize(),
                'mime_type' => $uploadedFile->getMimeType(),
                'hash' => hash_file('sha256', Storage::disk('local')->path($storedPath)),
            ];

        } catch (Exception $exception) {
            $this->logFileOperation('upload_failure', $uploadedFile->getClientOriginalName(), $userId, [
                'error' => $exception->getMessage(),
                'category' => $category,
            ]);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Validate file against security rules
     */
    private function validateFile(UploadedFile $uploadedFile): void
    {
        // Check if file is valid
        if (! $uploadedFile->isValid()) {
            throw new Exception('Invalid file upload');
        }

        // Check file size
        if ($uploadedFile->getSize() > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum allowed size of '.($this->maxFileSize / 1024).' KB');
        }

        // Check MIME type
        $mimeType = $uploadedFile->getMimeType();
        if (! in_array($mimeType, $this->allowedMimeTypes)) {
            throw new Exception('File type not allowed: '.$mimeType);
        }

        // Check extension
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        if (! in_array($extension, $this->allowedExtensions)) {
            throw new Exception('File extension not allowed: '.$extension);
        }

        // Double-check extension matches MIME type
        if (! $this->validateMimeExtensionMatch($mimeType, $extension)) {
            throw new Exception('File extension does not match file type');
        }
    }

    /**
     * Validate that MIME type matches file extension
     */
    private function validateMimeExtensionMatch(string $mimeType, string $extension): bool
    {
        $validCombinations = [
            'application/pdf' => ['pdf'],
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        ];

        return isset($validCombinations[$mimeType]) &&
               in_array($extension, $validCombinations[$mimeType]);
    }

    /**
     * Generate a secure filename
     */
    private function generateSecureFilename(UploadedFile $uploadedFile): string
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $timestamp = now()->format('Y-m-d_H-i-s');
        $randomString = Str::random(8);

        return sprintf('secure_%s_%s.%s', $timestamp, $randomString, $extension);
    }

    /**
     * Create secure directory structure
     */
    private function createSecureDirectory(string $category, ?int $userId = null): string
    {
        $basePath = $this->uploadPath;
        $year = date('Y');
        $month = date('m');

        $directory = sprintf('%s/%s/%s/%s', $basePath, $category, $year, $month);

        if ($userId !== null && $userId !== 0) {
            $directory .= '/user_'.$userId;
        }

        // Create directory if it doesn't exist
        if (! Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);

            // Create .htaccess file to prevent direct access
            $htaccess = "deny from all\n";
            Storage::disk('local')->put($directory.'/.htaccess', $htaccess);
        }

        return $directory;
    }

    /**
     * Perform additional security checks
     */
    private function performSecurityChecks(UploadedFile $uploadedFile): void
    {
        // Check for executable code in file content
        $this->scanFileContent($uploadedFile);

        // Virus scan if enabled
        if ($this->scanForMalware) {
            $this->scanForVirus($uploadedFile);
        }

        // Check for suspicious file headers
        $this->validateFileHeaders($uploadedFile);
    }

    /**
     * Scan file content for suspicious patterns
     */
    private function scanFileContent(UploadedFile $uploadedFile): void
    {
        $content = file_get_contents($uploadedFile->getRealPath());

        // Patterns that indicate potentially malicious content
        $suspiciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/data:text\/html/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec/i',
            '/base64_decode/i',
        ];

        foreach ($suspiciousPatterns as $suspiciouPattern) {
            if (preg_match($suspiciouPattern, $content)) {
                throw new Exception('File contains potentially malicious content');
            }
        }
    }

    /**
     * Validate file headers match expected type
     */
    private function validateFileHeaders(UploadedFile $uploadedFile): void
    {
        $filePath = $uploadedFile->getRealPath();
        $fileHandle = fopen($filePath, 'rb');

        if (! $fileHandle) {
            throw new Exception('Cannot read file for header validation');
        }

        $header = fread($fileHandle, 16);
        fclose($fileHandle);

        $mimeType = $uploadedFile->getMimeType();

        // Check file signatures
        $validHeaders = [
            'application/pdf' => ['\x25\x50\x44\x46'], // %PDF
            'image/jpeg' => ['\xFF\xD8\xFF'], // JPEG
            'image/png' => ['\x89\x50\x4E\x47\x0D\x0A\x1A\x0A'], // PNG
            'image/gif' => ['\x47\x49\x46\x38'], // GIF
        ];

        if (isset($validHeaders[$mimeType])) {
            $headerFound = false;
            foreach ($validHeaders[$mimeType] as $validHeader) {
                if (str_starts_with($header, $validHeader)) {
                    $headerFound = true;
                    break;
                }
            }

            if (! $headerFound) {
                throw new Exception('File header does not match declared file type');
            }
        }
    }

    /**
     * Scan for viruses (requires ClamAV or similar)
     */
    private function scanForVirus(UploadedFile $uploadedFile): void
    {
        // This would integrate with ClamAV or similar antivirus
        // For now, we'll just log that scanning was requested
        Log::info('Virus scan requested for file: '.$uploadedFile->getClientOriginalName());

        // Example integration:
        // $result = shell_exec("clamscan " . escapeshellarg($file->getRealPath()));
        // if (strpos($result, 'FOUND') !== false) {
        //     throw new Exception('Virus detected in uploaded file');
        // }
    }

    /**
     * Verify uploaded file integrity
     */
    private function verifyUploadedFile(string $storedPath): void
    {
        $fullPath = Storage::disk('local')->path($storedPath);

        if (! file_exists($fullPath)) {
            throw new Exception('File was not properly stored');
        }

        if (! is_readable($fullPath)) {
            throw new Exception('Stored file is not readable');
        }
    }

    /**
     * Retrieve a file securely
     */
    public function getFile(string $filename, ?int $userId = null): array
    {
        try {
            // Validate filename format
            if (! $this->isValidSecureFilename($filename)) {
                throw new Exception('Invalid filename format');
            }

            // Find the file
            $filePath = $this->findFile($filename);

            if (in_array($filePath, [null, '', '0'], true)) {
                throw new Exception('File not found');
            }

            // Verify file integrity
            $this->verifyUploadedFile($filePath);

            $fullPath = Storage::disk('local')->path($filePath);

            return [
                'success' => true,
                'path' => $filePath,
                'size' => filesize($fullPath),
                'mime_type' => mime_content_type($fullPath),
                'hash' => hash_file('sha256', $fullPath),
            ];

        } catch (Exception $exception) {
            $this->logFileOperation('retrieval_failure', $filename, $userId, [
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Check if filename follows secure naming pattern
     */
    private function isValidSecureFilename(string $filename): bool
    {
        return preg_match('/^secure_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_[a-zA-Z0-9]{8}\.[a-z]+$/', $filename);
    }

    /**
     * Find file in directory structure
     */
    private function findFile(string $filename): ?string
    {
        $searchPaths = [
            $this->uploadPath.'/**/'.$filename,
        ];

        foreach ($searchPaths as $searchPath) {
            $files = Storage::disk('local')->glob($searchPath);
            if (! empty($files)) {
                return $files[0];
            }
        }

        return null;
    }

    /**
     * Delete a file securely
     */
    public function deleteFile(string $filename, ?int $userId = null): array
    {
        try {
            $filePath = $this->findFile($filename);

            if (in_array($filePath, [null, '', '0'], true)) {
                throw new Exception('File not found');
            }

            // Secure deletion
            if (Storage::disk('local')->delete($filePath)) {
                $this->logFileOperation('deletion_success', $filename, $userId);

                return [
                    'success' => true,
                    'message' => 'File deleted successfully',
                ];
            }
            throw new Exception('Failed to delete file');
        } catch (Exception $exception) {
            $this->logFileOperation('deletion_failure', $filename, $userId, [
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Log file operations
     */
    private function logFileOperation(string $operation, string $filename, ?int $userId = null, array $context = []): void
    {
        Log::channel('security')->info('File Operation: '.$operation, [
            'operation' => $operation,
            'filename' => $filename,
            'user_id' => $userId,
            'ip_address' => request()?->ip(),
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
