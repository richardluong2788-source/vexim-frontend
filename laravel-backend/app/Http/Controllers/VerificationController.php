<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SupplierDocument;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationApproved;
use App\Mail\VerificationRejected;
use App\Mail\DocumentsRequested;

class VerificationController extends Controller
{
    /**
     * Upload verification document
     */
    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'document_type' => 'required|in:business_license,tax_certificate,export_license,quality_certificate,other',
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user owns this company
            $company = Company::find($request->company_id);
            if ($company->users()->where('id', auth()->id())->doesntExist()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Store file
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents/' . $company->id, $filename, 'public');

            // Create document record
            $document = SupplierDocument::create([
                'company_id' => $request->company_id,
                'document_type' => $request->document_type,
                'document_name' => $request->document_name,
                'file_path' => $path,
                'status' => 'pending',
            ]);

            // Log action
            AuditLog::log(
                'document_uploaded',
                'SupplierDocument',
                $document->id,
                null,
                $document->toArray(),
                'Supplier uploaded verification document'
            );

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company documents
     */
    public function getDocuments($companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        // Check authorization
        if (auth()->user()->role !== 'admin' && 
            $company->users()->where('id', auth()->id())->doesntExist()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $documents = $company->documents()->with('verifier')->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Admin: Review document
     */
    public function reviewDocument(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $document = SupplierDocument::with('company')->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        try {
            $oldValues = $document->toArray();

            $document->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Log action
            AuditLog::log(
                'document_reviewed',
                'SupplierDocument',
                $document->id,
                $oldValues,
                $document->fresh()->toArray(),
                "Document {$request->status} by admin"
            );

            return response()->json([
                'success' => true,
                'message' => "Document {$request->status} successfully",
                'data' => $document->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Review failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Approve company verification
     */
    public function approveCompany(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = Company::with('users')->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        try {
            $oldValues = $company->toArray();

            // Generate verification ID
            $verificationId = $company->generateVerificationId();

            $company->update([
                'verification_status' => 'verified',
                'verification_id' => $verificationId,
                'verified_at' => now(),
                'package_id' => 1, // Free package
                'package_expires_at' => now()->addYear(),
            ]);

            // Log action
            AuditLog::log(
                'company_verified',
                'Company',
                $company->id,
                $oldValues,
                $company->fresh()->toArray(),
                $request->admin_notes ?? 'Company verification approved'
            );

            // Send email notification
            $supplier = $company->users()->first();
            if ($supplier) {
                Mail::to($supplier->email)->send(new VerificationApproved($company));
            }

            return response()->json([
                'success' => true,
                'message' => 'Company verified successfully',
                'data' => $company->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Reject company verification
     */
    public function rejectCompany(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = Company::with('users')->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        try {
            $oldValues = $company->toArray();

            $company->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Log action
            AuditLog::log(
                'company_rejected',
                'Company',
                $company->id,
                $oldValues,
                $company->fresh()->toArray(),
                'Company verification rejected: ' . $request->rejection_reason
            );

            // Send email notification
            $supplier = $company->users()->first();
            if ($supplier) {
                Mail::to($supplier->email)->send(new VerificationRejected($company, $request->rejection_reason));
            }

            return response()->json([
                'success' => true,
                'message' => 'Company verification rejected',
                'data' => $company->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rejection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Request additional documents
     */
    public function requestDocuments(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'required_documents' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = Company::with('users')->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        try {
            // Log action
            AuditLog::log(
                'documents_requested',
                'Company',
                $company->id,
                null,
                [
                    'message' => $request->message,
                    'required_documents' => $request->required_documents
                ],
                'Admin requested additional documents'
            );

            // Send email notification
            $supplier = $company->users()->first();
            if ($supplier) {
                Mail::to($supplier->email)->send(
                    new DocumentsRequested($company, $request->message, $request->required_documents)
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Document request sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
