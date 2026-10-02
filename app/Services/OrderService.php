<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Gig;
use App\Models\GigExtra;
use App\Models\GigPackage;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderCancellation;
use App\Models\OrderDelivery;
use App\Models\OrderRequirement;
use App\Models\OrderRevision;
use App\Models\PlatformSetting;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected CommissionService $commission,
        protected PaymentService $payments,
    ) {
    }

    public function createFromGig(User $buyer, GigPackage $package, array $extraIds = []): Order
    {
        $gig = $package->gig()->with(['extras', 'requirements', 'seller.user'])->firstOrFail();
        if ($gig->status !== 'active') {
            throw ValidationException::withMessages(['gig' => 'This gig is not available.']);
        }
        if (! $buyer->is_buyer) {
            throw ValidationException::withMessages(['buyer' => 'Only buyers can place orders.']);
        }

        $extras = $gig->extras->whereIn('id', $extraIds);
        $extrasTotal = (float) $extras->sum('price');
        $extraDays = (int) $extras->sum('extra_days');
        $subtotal = (float) $package->price + $extrasTotal;
        $breakdown = $this->commission->breakdown($subtotal, $gig->category_id, $gig->seller->level ?? 'new');

        return DB::transaction(function () use ($buyer, $gig, $package, $extras, $extrasTotal, $extraDays, $subtotal, $breakdown) {
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $gig->seller->user_id,
                'source_type' => 'gig_package',
                'source_id' => $package->id,
                'title' => $gig->title.' — '.$package->title,
                'price' => $package->price,
                'extras_total' => $extrasTotal,
                'platform_fee' => $breakdown['platform_fee'],
                'seller_earning' => $breakdown['seller_earning'],
                'currency' => 'USD',
                'status' => 'pending_payment',
                'revisions_allowed' => $package->revisions,
            ]);

            $order->items()->create([
                'type' => 'package',
                'title' => $package->tier.': '.$package->title,
                'price' => $package->price,
                'qty' => 1,
            ]);

            foreach ($extras as $extra) {
                $order->items()->create([
                    'type' => 'extra',
                    'title' => $extra->title,
                    'price' => $extra->price,
                    'qty' => 1,
                ]);
            }

            $this->log($order, $buyer->id, 'created', ['subtotal' => $subtotal, 'extra_days' => $extraDays]);
            $this->ensureConversation($order);

            return $order->load(['items', 'requirements']);
        });
    }

    public function createFromProposal(User $buyer, Proposal $proposal): Order
    {
        if ((int) $proposal->job->buyer_id !== (int) $buyer->id) {
            throw ValidationException::withMessages(['proposal' => 'Not your job.']);
        }
        if ($proposal->status !== 'pending' && $proposal->status !== 'shortlisted') {
            throw ValidationException::withMessages(['proposal' => 'Proposal cannot be accepted.']);
        }

        return DB::transaction(function () use ($buyer, $proposal) {
            $proposal->load(['job', 'seller.user', 'milestones']);
            $breakdown = $this->commission->breakdown((float) $proposal->bid_amount, $proposal->job->category_id, $proposal->seller->level ?? 'new');

            $order = Order::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $proposal->seller->user_id,
                'source_type' => 'proposal',
                'source_id' => $proposal->id,
                'title' => $proposal->job->title,
                'price' => $proposal->bid_amount,
                'extras_total' => 0,
                'platform_fee' => $breakdown['platform_fee'],
                'seller_earning' => $breakdown['seller_earning'],
                'status' => 'pending_payment',
                'revisions_allowed' => 2,
            ]);

            $order->items()->create([
                'type' => 'package',
                'title' => 'Proposal bid',
                'price' => $proposal->bid_amount,
                'qty' => 1,
            ]);

            foreach ($proposal->milestones as $m) {
                $order->milestones()->create([
                    'title' => $m->title,
                    'amount' => $m->amount,
                    'delivery_days' => $m->delivery_days,
                    'sort_order' => $m->sort_order,
                    'status' => 'pending',
                ]);
            }

            $proposal->update(['status' => 'accepted']);
            $proposal->job->update(['status' => 'awarded']);
            Proposal::where('job_id', $proposal->job_id)->where('id', '!=', $proposal->id)->where('status', 'pending')
                ->update(['status' => 'rejected']);

            app(ConnectService::class)->refundRejected($proposal);

            $this->log($order, $buyer->id, 'created_from_proposal', ['proposal_id' => $proposal->id]);
            $this->ensureConversation($order);

            return $order->load(['items', 'milestones']);
        });
    }

    public function pay(Order $order, User $buyer, ?string $idempotencyKey = null): Order
    {
        if ((int) $order->buyer_id !== (int) $buyer->id) {
            abort(response()->json(['status' => false, 'message' => 'Forbidden'], 403));
        }
        if ($order->status !== 'pending_payment') {
            throw ValidationException::withMessages(['order' => 'Order is not awaiting payment.']);
        }
        if ($idempotencyKey) {
            $order->idempotency_key = $idempotencyKey;
        }

        return DB::transaction(function () use ($order) {
            if ($order->milestones()->exists()) {
                $first = $order->milestones()->orderBy('sort_order')->first();
                $this->payments->holdEscrow($order, $first->id);
                $first->update(['status' => 'funded', 'funded_at' => now()]);
            } else {
                $this->payments->holdEscrow($order);
            }

            $this->payments->createInvoice($order);
            $order->status = 'active';
            $gig = $order->source_type === 'gig_package' ? GigPackage::find($order->source_id)?->gig : null;
            if ($gig && $gig->requirements()->exists()) {
                // timer starts when requirements submitted
            } else {
                $days = $this->deliveryDays($order);
                $order->due_at = now()->addDays($days);
            }
            $order->save();
            $this->log($order, $order->buyer_id, 'paid');

            return $order->fresh(['items', 'escrows', 'invoice']);
        });
    }

    public function submitRequirements(Order $order, User $buyer, array $answers): Order
    {
        if ((int) $order->buyer_id !== (int) $buyer->id) {
            abort(response()->json(['status' => false, 'message' => 'Forbidden'], 403));
        }

        foreach ($answers as $row) {
            OrderRequirement::create([
                'order_id' => $order->id,
                'question' => $row['question'] ?? 'Requirement',
                'answer' => $row['answer'] ?? null,
                'file_path' => $row['file_path'] ?? null,
            ]);
        }

        $order->requirements_submitted_at = now();
        $days = $this->deliveryDays($order);
        $order->due_at = now()->addDays($days);
        $order->save();
        $this->log($order, $buyer->id, 'requirements_submitted');

        return $order->fresh(['requirements']);
    }

    public function deliver(Order $order, User $seller, string $note, array $files = [], ?int $milestoneId = null): OrderDelivery
    {
        if ((int) $order->seller_id !== (int) $seller->id) {
            abort(response()->json(['status' => false, 'message' => 'Forbidden'], 403));
        }
        if (! in_array($order->status, ['active', 'revision_requested'], true)) {
            throw ValidationException::withMessages(['order' => 'Cannot deliver in current status.']);
        }

        $version = (int) $order->deliveries()->max('version') + 1;
        $delivery = OrderDelivery::create([
            'order_id' => $order->id,
            'milestone_id' => $milestoneId,
            'seller_id' => $seller->id,
            'note' => $note,
            'version' => $version,
            'delivered_at' => now(),
        ]);

        foreach ($files as $file) {
            $delivery->files()->create($file);
        }

        $days = (int) PlatformSetting::number('auto_complete_days', 3);
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'auto_complete_at' => now()->addDays($days),
            'is_late' => $order->due_at && now()->greaterThan($order->due_at),
        ]);

        if ($milestoneId) {
            $order->milestones()->where('id', $milestoneId)->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }

        $this->log($order, $seller->id, 'delivered', ['version' => $version]);

        return $delivery->load('files');
    }

    public function requestRevision(Order $order, User $buyer, string $reason): Order
    {
        if ((int) $order->buyer_id !== (int) $buyer->id) {
            abort(response()->json(['status' => false, 'message' => 'Forbidden'], 403));
        }
        if ($order->status !== 'delivered') {
            throw ValidationException::withMessages(['order' => 'Order is not delivered.']);
        }
        if ($order->revisions_used >= $order->revisions_allowed) {
            throw ValidationException::withMessages(['revisions' => 'No revisions left.']);
        }

        $latest = $order->deliveries()->latest('id')->first();
        OrderRevision::create([
            'order_id' => $order->id,
            'delivery_id' => $latest?->id,
            'buyer_id' => $buyer->id,
            'reason' => $reason,
            'requested_at' => now(),
        ]);

        $order->increment('revisions_used');
        $order->update(['status' => 'revision_requested', 'auto_complete_at' => null]);
        $this->log($order, $buyer->id, 'revision_requested');

        return $order->fresh();
    }

    public function complete(Order $order, ?User $actor = null, bool $auto = false): Order
    {
        if (! in_array($order->status, ['delivered', 'revision_requested'], true) && ! $auto) {
            throw ValidationException::withMessages(['order' => 'Order cannot be completed.']);
        }

        return DB::transaction(function () use ($order, $actor, $auto) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'auto_complete_at' => null,
            ]);
            $this->payments->releaseEscrow($order);
            if ($order->source_type === 'gig_package') {
                GigPackage::find($order->source_id)?->gig?->increment('orders_count');
            }
            $this->log($order, $actor?->id, $auto ? 'auto_completed' : 'completed');

            return $order->fresh();
        });
    }

    public function requestCancel(Order $order, User $user, string $reason): OrderCancellation
    {
        $cancel = OrderCancellation::create([
            'order_id' => $order->id,
            'requested_by' => $user->id,
            'reason' => $reason,
            'status' => 'requested',
        ]);
        $this->log($order, $user->id, 'cancel_requested');

        return $cancel;
    }

    public function acceptCancel(Order $order, User $user): Order
    {
        $pending = $order->cancellations()->where('status', 'requested')->latest('id')->first();
        if (! $pending) {
            throw ValidationException::withMessages(['cancel' => 'No cancellation request.']);
        }
        if ((int) $pending->requested_by === (int) $user->id) {
            throw ValidationException::withMessages(['cancel' => 'The other party must accept.']);
        }

        return $this->forceCancel($order, $user, (float) $order->price + (float) $order->extras_total, 'mutual');
    }

    public function forceCancel(Order $order, ?User $actor, float $refund, string $how = 'admin'): Order
    {
        return DB::transaction(function () use ($order, $actor, $refund, $how) {
            $this->payments->refundEscrow($order, $refund);
            $order->update(['status' => 'cancelled']);
            $order->cancellations()->where('status', 'requested')->update([
                'status' => 'accepted',
                'refund_amount' => $refund,
            ]);
            $this->log($order, $actor?->id, 'cancelled', ['how' => $how, 'refund' => $refund]);

            return $order->fresh();
        });
    }

    public function raiseDispute(Order $order, User $user, string $reason, ?string $description = null): Order
    {
        $order->update(['status' => 'disputed']);
        $this->log($order, $user->id, 'disputed', ['reason' => $reason]);

        return $order;
    }

    public function autoCompleteDue(): int
    {
        $count = 0;
        Order::where('status', 'delivered')
            ->whereNotNull('auto_complete_at')
            ->where('auto_complete_at', '<=', now())
            ->get()
            ->each(function (Order $order) use (&$count) {
                $this->complete($order, null, true);
                $count++;
            });

        return $count;
    }

    public function markLate(): int
    {
        return Order::where('status', 'active')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->where('is_late', false)
            ->update(['is_late' => true]);
    }

    private function deliveryDays(Order $order): int
    {
        if ($order->source_type === 'gig_package') {
            $package = GigPackage::find($order->source_id);
            $extraDays = 0;
            if ($package) {
                $extraIds = $order->items()->where('type', 'extra')->pluck('title');
                $extraDays = (int) GigExtra::where('gig_id', $package->gig_id)->whereIn('title', $extraIds)->sum('extra_days');
            }

            return ((int) ($package?->delivery_days ?? 3)) + $extraDays;
        }

        $proposal = Proposal::find($order->source_id);

        return (int) ($proposal?->delivery_days ?? 7);
    }

    private function log(Order $order, ?int $actorId, string $action, array $meta = []): void
    {
        OrderActivity::create([
            'order_id' => $order->id,
            'actor_id' => $actorId,
            'action' => $action,
            'meta' => $meta ?: null,
        ]);
    }

    private function ensureConversation(Order $order): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'order_id' => $order->id,
            ]
        );
    }
}
