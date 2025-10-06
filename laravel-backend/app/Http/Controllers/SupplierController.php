<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Certificate;
use App\Models\ExportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

        $stats = [
            'total_products' => $company->products()->count(),
            'active_products' => $company->products()->where('status', 'active')->count(),
            'total_reviews' => $company->reviews()->count(),
            'average_rating' => $company->rating,
            'verification_status' => $company->verification_status,
            'package_name' => $company->package ? $company->package->name : 'Free',
            'package_expires_at' => $company->package_expires_at,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company,
                'stats' => $stats
            ]
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
            'description' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'address' => 'sometimes|string',
            'website' => 'sometimes|url',
            'main_products' => 'sometimes|string',
            'year_established' => 'sometimes|integer|min:1800|max:' . date('Y'),
            'employee_count' => 'sometimes|string',
            'annual_revenue' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company->update($request->all());

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
     * Upload company logo
     */
    public function uploadLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $company = $user->company;

            // Delete old logo if exists
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            $company->update(['logo' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Logo uploaded successfully',
                'data' => [
                    'logo_url' => Storage::url($path)
                ]
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
     * Get all products for supplier
     */
    public function getProducts(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        $products = $company->products()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'min_order' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $company = $user->company;

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
                'name' => $request->name,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price,
                'min_order' => $request->min_order,
                'unit' => $request->unit,
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
                'message' => 'Creation failed',
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
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'min_order' => 'sometimes|integer|min:1',
            'unit' => 'sometimes|string|max:50',
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
                'message' => 'Deletion failed',
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
            ->orderBy('issue_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $certificates
        ]);
    }

    /**
     * Upload certificate
     */
    public function uploadCertificate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
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
            $user = $request->user();
            $company = $user->company;

            $path = $request->file('file')->store('certificates', 'public');

            $certificate = Certificate::create([
                'company_id' => $company->id,
                'name' => $request->name,
                'issuer' => $request->issuer,
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'file_path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Certificate uploaded successfully',
                'data' => $certificate
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
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

        $history = $company->exportHistories()
            ->orderBy('year', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Add export history
     */
    public function addExportHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'country' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'product_category' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $company = $user->company;

            $history = ExportHistory::create([
                'company_id' => $company->id,
                'year' => $request->year,
                'country' => $request->country,
                'amount' => $request->amount,
                'product_category' => $request->product_category,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Export history added successfully',
                'data' => $history
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
