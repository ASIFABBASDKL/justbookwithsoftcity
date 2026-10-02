<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGigRequest;
use App\Http\Resources\GigResource;
use App\Models\Gig;
use App\Models\SellerProfile;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GigController extends Controller
{
    public function index(Request $request)
    {
        $q = Gig::query()->with(['packages', 'category', 'seller.user'])->active();

        if ($request->filled('category_id')) {
            $q->where('category_id', $request->category_id);
        }
        if ($request->filled('q')) {
            $q->where(function ($qq) use ($request) {
                $qq->where('title', 'like', '%'.$request->q.'%')
                    ->orWhere('description', 'like', '%'.$request->q.'%');
            });
        }
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $q->whereHas('packages', function ($p) use ($request) {
                if ($request->filled('min_price')) {
                    $p->where('price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $p->where('price', '<=', $request->max_price);
                }
            });
        }
        if ($request->filled('delivery_days')) {
            $q->whereHas('packages', fn ($p) => $p->where('delivery_days', '<=', $request->delivery_days));
        }
        if ($request->filled('min_rating')) {
            $q->where('avg_rating', '>=', $request->min_rating);
        }
        if ($request->filled('seller_level')) {
            $q->whereHas('seller', fn ($s) => $s->where('level', $request->seller_level));
        }

        $sort = $request->get('sort', 'recommended');
        match ($sort) {
            'newest' => $q->latest(),
            'best_selling' => $q->orderByDesc('orders_count'),
            'price_asc' => $q->orderBy(
                \App\Models\GigPackage::select('price')->whereColumn('gig_id', 'gigs.id')->orderBy('price')->limit(1)
            ),
            'price_desc' => $q->orderByDesc(
                \App\Models\GigPackage::select('price')->whereColumn('gig_id', 'gigs.id')->orderBy('price')->limit(1)
            ),
            default => $q->orderByDesc('is_featured')->orderByDesc('orders_count'),
        };

        return GigResource::collection($q->paginate(20))->additional([
            'status' => true,
            'message' => 'Gigs fetched',
        ]);
    }

    public function show(Gig $gig)
    {
        $gig->increment('clicks');
        $gig->load(['packages', 'extras', 'gallery', 'faqs', 'requirements', 'seller.user', 'category', 'skills']);

        return ApiResponse::success(new GigResource($gig), 'Gig detail');
    }

    public function store(StoreGigRequest $request)
    {
        $seller = $request->user()->sellerProfile;
        if (! $seller) {
            $this->deny('Become a seller first.');
        }

        $gig = Gig::create([
            'seller_id' => $seller->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title).'-'.Str::random(5),
            'description' => $request->description,
            'status' => 'draft',
            'seo_title' => $request->title,
        ]);

        $this->syncRelated($gig, $request->validated());

        return ApiResponse::success($gig->load(['packages', 'extras', 'faqs', 'requirements']), 'Gig created', 201);
    }

    public function update(StoreGigRequest $request, Gig $gig)
    {
        $this->assertOwner($gig, $request->user());
        $gig->update($request->only(['title', 'description', 'category_id']));
        $gig->packages()->delete();
        $gig->extras()->delete();
        $gig->faqs()->delete();
        $gig->requirements()->delete();
        $this->syncRelated($gig, $request->validated());

        return ApiResponse::success($gig->fresh(['packages', 'extras']), 'Gig updated');
    }

    public function submit(Request $request, Gig $gig)
    {
        $this->assertOwner($gig, $request->user());
        $gig->update(['status' => 'pending']);

        return ApiResponse::success($gig, 'Gig submitted for approval');
    }

    public function pause(Request $request, Gig $gig)
    {
        $this->assertOwner($gig, $request->user());
        $gig->update(['status' => $gig->status === 'paused' ? 'active' : 'paused']);

        return ApiResponse::success($gig, 'Gig status updated');
    }

    public function destroy(Request $request, Gig $gig)
    {
        $this->assertOwner($gig, $request->user());
        $gig->update(['status' => 'deleted']);

        return ApiResponse::success(null, 'Gig deleted');
    }

    public function uploadGallery(Request $request, Gig $gig)
    {
        $this->assertOwner($gig, $request->user());
        $request->validate(['file' => 'required|file|max:10240', 'type' => 'nullable|in:image,video,pdf']);
        $path = $request->file('file')->store('gigs/gallery', 'public');
        $item = $gig->gallery()->create([
            'type' => $request->get('type', 'image'),
            'path' => $path,
            'sort_order' => $gig->gallery()->count(),
        ]);

        return ApiResponse::success($item, 'Uploaded', 201);
    }

    private function syncRelated(Gig $gig, array $data): void
    {
        foreach ($data['packages'] ?? [] as $package) {
            $gig->packages()->create($package);
        }
        foreach ($data['extras'] ?? [] as $extra) {
            $gig->extras()->create($extra);
        }
        foreach ($data['faqs'] ?? [] as $faq) {
            $gig->faqs()->create($faq);
        }
        foreach ($data['requirements'] ?? [] as $req) {
            $gig->requirements()->create($req);
        }
        if (! empty($data['skill_ids'])) {
            $gig->skills()->sync($data['skill_ids']);
        }
    }

    private function assertOwner(Gig $gig, $user): void
    {
        if ((int) $gig->seller_id !== (int) $user->sellerProfile?->id) {
            $this->deny();
        }
    }
}
