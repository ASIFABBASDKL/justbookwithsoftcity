<?php

namespace App\Http\Controllers\ServiceAndPricing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceAndPricing;
use App\Models\ServiceProvider;

class ServiceAndPricingController extends Controller
{
    //
    public function storeServiceProvider(Request $request, $providerId)
    {
        $provider = ServiceProvider::findOrFail($providerId);

        $data = $request->validate([
            'category' => 'nullable|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'service_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'img' => 'nullable|string',
            'service_price' => 'required|string|max:50',
            'service_duration' => 'nullable|string|max:50',
        ]);

        $data['service_provider_id'] = $provider->id;

        $service = ServiceAndPricing::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Service created successfully',
            'data' => $service,
        ], 200);
    }
    public function getServicesByProvider($providerId)
    {
        $services = ServiceAndPricing::where('service_provider_id', $providerId)->get();

        if ($services->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No services found for this provider.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Services fetched successfully!',
            'data' => $services
        ], 200);
    }

    public function getAllCategories()
    {
        $categories = ServiceAndPricing::select('category')
            ->distinct()
            ->pluck('category'); // sirf ek column ki list dega

        return response()->json([
            'status' => true,
            'categories' => $categories
        ]);
    }
    public function getSubcategoriesByCategory($categoryName)
    {
        $subcategories = ServiceAndPricing::where('category', $categoryName)
            ->whereNotNull('subcategory')
            ->select('subcategory')
            ->distinct()
            ->pluck('subcategory');

        return response()->json([
            'status' => true,
            'category' => $categoryName,
            'subcategories' => $subcategories
        ]);
    }


    public function getProvidersByCategoryAndSubcategory($categoryName, $subcategoryName)
    {
        $services = ServiceAndPricing::with('provider')
            ->where('category', $categoryName)
            ->where('subcategory', $subcategoryName)
            ->get();

        return response()->json([
            'status' => true,
            'category' => $categoryName,
            'subcategory' => $subcategoryName,
            'providers' => $services
        ]);
    }

    public function updateService(Request $request, $serviceId)
    {
        $service = ServiceAndPricing::findOrFail($serviceId);
    
        $data = $request->validate([
            'category' => 'nullable|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'service_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'img' => 'nullable|string',
            'service_price' => 'nullable|string|max:50',
            'service_duration' => 'nullable|string|max:50',
        ]);
    
        $service->update($data);
    
        return response()->json([
            'status' => true,
            'message' => 'Service updated successfully',
            'data' => $service,
        ], 200);
    }
    public function deleteService($serviceId)
    {
    // ✅ Service find karo
    $service = ServiceAndPricing::find($serviceId);

    if (!$service) {
        return response()->json([
            'status' => false,
            'message' => 'Service not found',
        ], 404);
    }

    // ✅ Delete service
    $service->delete();

    return response()->json([
        'status' => true,
        'message' => 'Service deleted successfully',
    ], 200);
    }


}
