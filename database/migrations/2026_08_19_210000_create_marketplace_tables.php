<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('pending_clearance', 12, 2)->default(0)->after('total_available_amount');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            $table->foreignId('reviewer_id')->nullable()->after('service_provider_id')->constrained('users')->nullOnDelete();
            $table->foreignId('reviewee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reviewer_type')->nullable();
            $table->boolean('is_public')->default(true);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('gigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_profiles')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft/pending/active/paused/rejected/deleted
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['status', 'category_id']);
        });

        Schema::create('gig_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('gigs')->cascadeOnDelete();
            $table->string('tier'); // basic/standard/premium
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('delivery_days');
            $table->unsignedInteger('revisions')->default(1);
            $table->json('features')->nullable();
            $table->timestamps();
            $table->unique(['gig_id', 'tier']);
        });

        Schema::create('gig_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('gigs')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('extra_days')->default(0);
            $table->unsignedInteger('max_qty')->default(1);
            $table->timestamps();
        });

        Schema::create('gig_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('gigs')->cascadeOnDelete();
            $table->string('type')->default('image');
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gig_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('gigs')->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->timestamps();
        });

        Schema::create('gig_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('gigs')->cascadeOnDelete();
            $table->string('question');
            $table->string('type')->default('text'); // text/file/choice
            $table->boolean('is_required')->default(true);
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('gig_skill', function (Blueprint $table) {
            $table->foreignId('gig_id')->constrained('gigs')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->primary(['gig_id', 'skill_id']);
        });

        Schema::create('seller_skill', function (Blueprint $table) {
            $table->foreignId('seller_id')->constrained('seller_profiles')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->primary(['seller_id', 'skill_id']);
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('seller_level')->nullable();
            $table->decimal('percent', 5, 2)->default(20);
            $table->decimal('min_fee', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('jobs_posted', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('budget_type')->default('fixed');
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('status')->default('open');
            $table->unsignedInteger('proposals_count')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->index(['status', 'category_id']);
        });

        Schema::create('job_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_posted')->cascadeOnDelete();
            $table->string('path');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });

        Schema::create('job_skill', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained('jobs_posted')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->primary(['job_id', 'skill_id']);
        });

        Schema::create('connect_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained('seller_profiles')->cascadeOnDelete();
            $table->integer('balance')->default(0);
            $table->integer('monthly_free')->default(10);
            $table->timestamp('last_refill_at')->nullable();
            $table->timestamps();
        });

        Schema::create('connect_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_profiles')->cascadeOnDelete();
            $table->integer('amount');
            $table->string('type'); // free/purchase/spend/refund
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_posted')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('seller_profiles')->cascadeOnDelete();
            $table->text('cover_letter')->nullable();
            $table->decimal('bid_amount', 12, 2);
            $table->unsignedInteger('delivery_days');
            $table->unsignedInteger('connects_spent')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['job_id', 'seller_id']);
        });

        Schema::create('proposal_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('delivery_days')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('proposal_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->string('path');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('job_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_posted')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('seller_profiles')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('source_type'); // gig_package|proposal
            $table->unsignedBigInteger('source_id');
            $table->string('title');
            $table->decimal('price', 12, 2);
            $table->decimal('extras_total', 12, 2)->default(0);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('seller_earning', 12, 2)->default(0);
            $table->string('currency', 8)->default('USD');
            $table->string('status')->default('pending_payment');
            $table->timestamp('requirements_submitted_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('auto_complete_at')->nullable();
            $table->unsignedInteger('revisions_allowed')->default(1);
            $table->unsignedInteger('revisions_used')->default(0);
            $table->boolean('is_late')->default(false);
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('booking_id')->constrained('orders')->nullOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('type'); // package|extra
            $table->string('title');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });

        Schema::create('order_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('delivery_days')->default(1);
            $table->string('status')->default('pending');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('order_milestones')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('held'); // held/released/refunded/partial
            $table->string('gateway_ref')->nullable();
            $table->timestamp('held_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('question');
            $table->text('answer')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('order_milestones')->nullOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('order_deliveries')->cascadeOnDelete();
            $table->string('path');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime')->nullable();
            $table->timestamps();
        });

        Schema::create('order_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained('order_deliveries')->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->timestamp('requested_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('order_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->string('status')->default('requested');
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type');
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_profiles')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('stripe');
            $table->string('gateway_ref')->nullable();
            $table->string('status')->default('requested');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->boolean('flagged')->default(false);
            $table->timestamps();
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->json('evidence')->nullable();
            $table->string('status')->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable');
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('user_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('banned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('saveable');
            $table->timestamps();
            $table->unique(['user_id', 'saveable_type', 'saveable_id']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('saved_items');
        Schema::dropIfExists('user_bans');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('order_cancellations');
        Schema::dropIfExists('order_activities');
        Schema::dropIfExists('order_revisions');
        Schema::dropIfExists('delivery_files');
        Schema::dropIfExists('order_deliveries');
        Schema::dropIfExists('order_requirements');
        Schema::dropIfExists('escrows');
        Schema::dropIfExists('order_milestones');
        Schema::dropIfExists('order_items');
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
        Schema::dropIfExists('orders');
        Schema::dropIfExists('job_invitations');
        Schema::dropIfExists('proposal_attachments');
        Schema::dropIfExists('proposal_milestones');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('connect_transactions');
        Schema::dropIfExists('connect_balances');
        Schema::dropIfExists('job_skill');
        Schema::dropIfExists('job_attachments');
        Schema::dropIfExists('jobs_posted');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('seller_skill');
        Schema::dropIfExists('gig_skill');
        Schema::dropIfExists('gig_requirements');
        Schema::dropIfExists('gig_faqs');
        Schema::dropIfExists('gig_gallery');
        Schema::dropIfExists('gig_extras');
        Schema::dropIfExists('gig_packages');
        Schema::dropIfExists('gigs');
        Schema::dropIfExists('portfolios');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('categories');
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewer_id');
            $table->dropConstrainedForeignId('reviewee_id');
            $table->dropColumn(['order_id', 'reviewer_type', 'is_public']);
        });
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('pending_clearance');
        });
    }
};
