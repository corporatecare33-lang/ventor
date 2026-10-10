<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engraving_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('type', 20); // name | initials | quote | logo
            $table->string('text')->nullable();
            $table->string('font', 50)->nullable();
            $table->string('position', 50)->nullable();
            $table->text('logo_instruction')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('fee', 10, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending | approved | completed | rejected
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('nfc_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 64)->unique();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('company')->nullable();
            $table->string('bio', 500)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('status', 20)->default('active'); // pending | active | suspended
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        Schema::create('warranty_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->string('type', 20)->default('registration'); // registration | claim
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('order_number', 30);
            $table->string('product_name')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expires_at')->nullable();
            $table->text('issue_details')->nullable();
            $table->string('status', 20)->default('pending'); // pending | approved | rejected | resolved
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('corporate_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('designation')->nullable();
            $table->string('email');
            $table->string('phone', 20);
            $table->unsignedInteger('quantity');
            $table->string('product_interest')->nullable();
            $table->string('budget')->nullable();
            $table->date('deadline')->nullable();
            $table->boolean('branding_required')->default(false);
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new'); // new | contacted | quoted | won | lost
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('corporate_inquiries');
        Schema::dropIfExists('warranty_registrations');
        Schema::dropIfExists('nfc_profiles');
        Schema::dropIfExists('engraving_requests');
    }
};
