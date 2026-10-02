<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\GigPackage;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $role = $request->get('as', 'buyer');
        $q = Order::query()->with(['items'])->latest();
        if ($role === 'seller') {
            $q->where('seller_id', $request->user()->id);
        } else {
            $q->where('buyer_id', $request->user()->id);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        return OrderResource::collection($q->paginate(20))->additional(['status' => true, 'message' => 'Orders']);
    }

    public function show(Request $request, Order $order)
    {
        $this->assertParty($order, $request->user());
        $order->load(['items', 'requirements', 'deliveries.files', 'activities', 'milestones', 'escrows', 'conversation']);

        return ApiResponse::success(new OrderResource($order));
    }

    public function store(CreateOrderRequest $request)
    {
        $package = GigPackage::with('gig.seller')->findOrFail($request->gig_package_id);
        $order = $this->orders->createFromGig($request->user(), $package, $request->extra_ids ?? []);

        return ApiResponse::success(new OrderResource($order), 'Order created', 201);
    }

    public function pay(Request $request, Order $order)
    {
        $request->validate(['idempotency_key' => 'nullable|string|max:64']);
        $order = $this->orders->pay($order, $request->user(), $request->idempotency_key);

        return ApiResponse::success(new OrderResource($order), 'Payment captured (simulated)');
    }

    public function requirements(Request $request, Order $order)
    {
        $request->validate(['answers' => 'required|array|min:1']);
        $order = $this->orders->submitRequirements($order, $request->user(), $request->answers);

        return ApiResponse::success($order, 'Requirements submitted');
    }

    public function deliver(Request $request, Order $order)
    {
        $request->validate([
            'note' => 'nullable|string',
            'milestone_id' => 'nullable|exists:order_milestones,id',
            'files' => 'nullable|array',
            'files.*' => 'file|max:20480',
        ]);
        $files = [];
        foreach ($request->file('files', []) as $file) {
            $path = $file->store('orders/deliveries', 'public');
            $files[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ];
        }
        $delivery = $this->orders->deliver($order, $request->user(), $request->note ?? '', $files, $request->milestone_id);

        return ApiResponse::success($delivery, 'Delivered', 201);
    }

    public function revision(Request $request, Order $order)
    {
        $request->validate(['reason' => 'required|string']);
        $order = $this->orders->requestRevision($order, $request->user(), $request->reason);

        return ApiResponse::success(new OrderResource($order), 'Revision requested');
    }

    public function complete(Request $request, Order $order)
    {
        $this->assertParty($order, $request->user());
        if ((int) $order->buyer_id !== (int) $request->user()->id) {
            $this->deny('Only the buyer can approve.');
        }
        $order = $this->orders->complete($order, $request->user());

        return ApiResponse::success(new OrderResource($order), 'Order completed');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->assertParty($order, $request->user());
        $request->validate(['reason' => 'required|string']);
        $cancel = $this->orders->requestCancel($order, $request->user(), $request->reason);

        return ApiResponse::success($cancel, 'Cancellation requested');
    }

    public function acceptCancel(Request $request, Order $order)
    {
        $this->assertParty($order, $request->user());
        $order = $this->orders->acceptCancel($order, $request->user());

        return ApiResponse::success(new OrderResource($order), 'Order cancelled');
    }

    public function dispute(Request $request, Order $order)
    {
        $this->assertParty($order, $request->user());
        $request->validate(['reason' => 'required|string', 'description' => 'nullable|string']);
        $this->orders->raiseDispute($order, $request->user(), $request->reason, $request->description);
        $dispute = \App\Models\Dispute::create([
            'order_id' => $order->id,
            'raised_by' => $request->user()->id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'open',
            'sla_due_at' => now()->addDays(3),
        ]);

        return ApiResponse::success($dispute, 'Dispute opened', 201);
    }

    private function assertParty(Order $order, $user): void
    {
        if ((int) $order->buyer_id !== (int) $user->id && (int) $order->seller_id !== (int) $user->id) {
            if (! $user->hasAnyRole(['admin', 'moderator'])) {
                $this->deny();
            }
        }
    }
}
