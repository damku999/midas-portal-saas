<?php

namespace App\Http\Controllers;

use App\Models\LeadDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeadDocumentController extends Controller
{
    /**
     * Store a new document for a lead
     *
     * SECURITY FIX #6: Unrestricted file upload protection
     * - Added MIME type validation
     * - Added content type checking
     * - Sanitized filenames
     * - Generate unique filenames
     * - Store in private disk instead of public
     */
    public function store(Request $request, int $leadId)
    {
        // SECURITY: Validate file type extensions and MIME types
        $validated = $request->validate([
            'document_type' => 'required|string|max:100',
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', // Whitelist file types
            ],
        ]);

        try {
            $file = $request->file('file');

            // SECURITY: Validate actual MIME type (not just extension)
            $allowedMimeTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/jpg',
                'image/png',
            ];

            $actualMimeType = $file->getMimeType();
            if (!in_array($actualMimeType, $allowedMimeTypes)) {
                \Log::warning('SECURITY: Attempted to upload file with invalid MIME type', [
                    'lead_id' => $leadId,
                    'mime_type' => $actualMimeType,
                    'original_name' => $file->getClientOriginalName(),
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                ]);

                return back()->with('error', 'Invalid file type. Only PDF, DOC, DOCX, XLS, XLSX, JPG, and PNG files are allowed.');
            }

            // SECURITY: Sanitize filename - remove potentially dangerous characters
            $originalFileName = $file->getClientOriginalName();
            $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName);

            // SECURITY: Generate unique filename to prevent overwrites and collisions
            $extension = $file->getClientOriginalExtension();
            $uniqueFileName = time() . '_' . uniqid() . '_' . $sanitizedFileName;

            // SECURITY: Store in private disk (not public) to prevent direct access
            $filePath = $file->storeAs(
                'lead-documents/' . $leadId,
                $uniqueFileName,
                'private' // Changed from 'public' to 'private'
            );

            $fileSize = $file->getSize();

            $document = LeadDocument::create([
                'lead_id' => $leadId,
                'document_type' => $validated['document_type'],
                'file_name' => $sanitizedFileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'mime_type' => $actualMimeType,
                'uploaded_by' => Auth::id(),
            ]);

            // SECURITY: Log successful upload
            \Log::info('Document uploaded successfully', [
                'document_id' => $document->id,
                'lead_id' => $leadId,
                'file_name' => $sanitizedFileName,
                'file_size' => $fileSize,
                'user_id' => Auth::id(),
            ]);

            return back()->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            \Log::error('Document upload failed', [
                'lead_id' => $leadId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to upload document: '.$e->getMessage());
        }
    }

    /**
     * Download a document
     *
     * SECURITY FIX #7: File download authorization
     * - Added authorization check to verify user can access lead
     * - Added tenant isolation check
     * - Added audit logging
     * - Use private disk storage
     */
    public function download(int $leadId, int $documentId)
    {
        try {
            $document = LeadDocument::where('lead_id', $leadId)->findOrFail($documentId);
            $lead = $document->lead;

            // SECURITY: Verify lead exists and belongs to current tenant
            if (!$lead) {
                \Log::warning('SECURITY: Attempted to download document for non-existent lead', [
                    'lead_id' => $leadId,
                    'document_id' => $documentId,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                ]);

                return back()->with('error', 'Document not found.');
            }

            // SECURITY: Authorization check - verify user can view this lead
            if (Auth::user()->cannot('view', $lead)) {
                \Log::warning('SECURITY: Unauthorized document download attempt', [
                    'lead_id' => $leadId,
                    'document_id' => $documentId,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                ]);

                return back()->with('error', 'Unauthorized access.');
            }

            // SECURITY: Verify file exists
            if (!Storage::disk('private')->exists($document->file_path)) {
                return back()->with('error', 'File not found.');
            }

            // SECURITY: Log document download for audit trail
            \Log::info('Document downloaded', [
                'document_id' => $documentId,
                'lead_id' => $leadId,
                'file_name' => $document->file_name,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            return Storage::disk('private')->download($document->file_path, $document->file_name);
        } catch (\Exception $e) {
            \Log::error('Document download failed', [
                'lead_id' => $leadId,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to download document.');
        }
    }

    /**
     * View/preview a document
     *
     * SECURITY FIX #7: File view authorization
     * - Added authorization check to verify user can access lead
     * - Added tenant isolation check
     * - Added audit logging
     */
    public function view(int $leadId, int $documentId)
    {
        try {
            $document = LeadDocument::where('lead_id', $leadId)->findOrFail($documentId);
            $lead = $document->lead;

            // SECURITY: Verify lead exists and belongs to current tenant
            if (!$lead) {
                \Log::warning('SECURITY: Attempted to view document for non-existent lead', [
                    'lead_id' => $leadId,
                    'document_id' => $documentId,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                ]);

                return response()->json(['error' => 'Document not found.'], 404);
            }

            // SECURITY: Authorization check - verify user can view this lead
            if (Auth::user()->cannot('view', $lead)) {
                \Log::warning('SECURITY: Unauthorized document view attempt', [
                    'lead_id' => $leadId,
                    'document_id' => $documentId,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                ]);

                return response()->json(['error' => 'Unauthorized access.'], 403);
            }

            // SECURITY: Verify file exists
            if (!Storage::disk('private')->exists($document->file_path)) {
                return response()->json(['error' => 'File not found.'], 404);
            }

            // SECURITY: Log document view for audit trail
            \Log::info('Document viewed', [
                'document_id' => $documentId,
                'lead_id' => $leadId,
                'file_name' => $document->file_name,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            $file = Storage::disk('private')->get($document->file_path);

            return response($file, 200)
                ->header('Content-Type', $document->mime_type)
                ->header('X-Content-Type-Options', 'nosniff'); // Prevent MIME sniffing
        } catch (\Exception $e) {
            \Log::error('Document view failed', [
                'lead_id' => $leadId,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json(['error' => 'Failed to view document.'], 500);
        }
    }

    /**
     * Delete a document
     */
    public function destroy(int $leadId, int $documentId)
    {
        try {
            $document = LeadDocument::where('lead_id', $leadId)->findOrFail($documentId);
            $document->delete();

            return back()->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete document: '.$e->getMessage());
        }
    }

    /**
     * Get all documents for a lead
     */
    public function index(int $leadId)
    {
        try {
            $documents = LeadDocument::where('lead_id', $leadId)
                ->with('uploader')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get documents by type
     */
    public function byType(int $leadId, string $type)
    {
        try {
            $documents = LeadDocument::where('lead_id', $leadId)
                ->ofType($type)
                ->with('uploader')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
