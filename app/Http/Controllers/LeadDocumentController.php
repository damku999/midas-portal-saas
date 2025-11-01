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
     */
    public function store(Request $request, int $leadId)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|max:100',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('lead-documents/' . $leadId, 'public');
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            $document = LeadDocument::create([
                'lead_id' => $leadId,
                'document_type' => $validated['document_type'],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'uploaded_by' => Auth::id(),
            ]);

            return back()->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Download a document
     */
    public function download(int $leadId, int $documentId)
    {
        try {
            $document = LeadDocument::where('lead_id', $leadId)->findOrFail($documentId);

            if (!$document->exists()) {
                return back()->with('error', 'File not found.');
            }

            return Storage::download($document->file_path, $document->file_name);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download document: ' . $e->getMessage());
        }
    }

    /**
     * View/preview a document
     */
    public function view(int $leadId, int $documentId)
    {
        try {
            $document = LeadDocument::where('lead_id', $leadId)->findOrFail($documentId);

            if (!$document->exists()) {
                return response()->json(['error' => 'File not found.'], 404);
            }

            $file = Storage::get($document->file_path);
            return response($file, 200)
                ->header('Content-Type', $document->mime_type);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
            return back()->with('error', 'Failed to delete document: ' . $e->getMessage());
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
