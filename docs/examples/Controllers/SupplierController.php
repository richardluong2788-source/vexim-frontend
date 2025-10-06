<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\Certificate;
use App\Models\ExportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Get supplier dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $data = [
            'company' => $company->load('package'),
            'stats' => [
                'total_products' => $company->products()->count(),
                'total_views' => $company->view_count,
                'pending_messages' => $company->users()->first()->receivedMessages()->where('status', 'pending')->count(),
                'rating' => $company->rating,
                'verification_status' => $company->verification_status,
            ],
            'recent_products' => $company->products()->latest()->take(5)->get(),
            'package_info' => [
                'current_package' => $company->package,
                'expires_at' => $company->package_end_date,
                'days_remaining' => $company->package_end_date ? now()->diffInDays($company->package_end_date) : 0,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Update company profile
     */
    public function updateCompany(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'website' => 'sometimes|url|max:255',
            'description' => 'sometimes|string',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($company->logo) {
                    Storage::disk('public')->delete($company->logo);
                }
                
                $logoPath = $request->file('logo')->store('logos', 'public');
                $company->logo = $logoPath;
            }

            // Update company info
            $company->update($request->except(['logo']));

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all products for supplier
     */
    public function getProducts(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $products = $company->products()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Create new product
     */
    public function createProduct(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        // Check product limit based on package
        $maxProducts = $company->package->max_products;
        $currentProducts = $company->products()->count();

        if ($maxProducts !== -1 && $currentProducts >= $maxProducts) {
            return response()->json([
                'success' => false,
                'message' => 'Product limit reached for your package'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'min_order' => 'required|integer|min:1',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = $path;
                }
            }

            $product = Product::create([
                'company_id' => $company->id,
                'product_name' => $request->product_name,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price,
                'unit' => $request->unit,
                'min_order' => $request->min_order,
                'images' => $imagePaths,
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product
     */
    public function updateProduct(Request $request, $id)
    {
        $user = $request->user();
        $company = $user->company;

        $product = Product::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:50',
            'min_order' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct($id)
    {
        $user = request()->user();
        $company = $user->company;

        $product = Product::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        try {
            // Delete product images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get certificates
     */
    public function getCertificates(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $certificates = $company->certificates()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $certificates
        ]);
    }

    /**
     * Add certificate
     */
    public function addCertificate(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $validator = Validator::make($request->all(), [
            'certificate_name' => 'required|string|max:255',
            'certificate_number' => 'required|string|max:100',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filePath = $request->file('file')->store('certificates', 'public');

            $certificate = Certificate::create([
                'company_id' => $company->id,
                'certificate_name' => $request->certificate_name,
                'certificate_number' => $request->certificate_number,
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'file_path' => $filePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Certificate added successfully',
                'data' => $certificate
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get export history
     */
    public function getExportHistory(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $exportHistory = $company->exportHistories()
            ->orderBy('year', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $exportHistory
        ]);
    }

    /**
     * Add export history
     */
    public function addExportHistory(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'export_value' => 'required|numeric|min:0',
            'destination_country' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $exportHistory = ExportHistory::create([
                'company_id' => $company->id,
                'year' => $request->year,
                'export_value' => $request->export_value,
                'destination_country' => $request->destination_country,
            ]);

            // Update company total exports
            $company->total_exports = $company->exportHistories()->sum('export_value');
            $company->save();

            return response()->json([
                'success' => true,
                'message' => 'Export history added successfully',
                'data' => $exportHistory
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add export history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
