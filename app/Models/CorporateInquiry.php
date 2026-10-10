<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use Illuminate\Database\Eloquent\Model;

class CorporateInquiry extends Model
{
    protected $fillable = [
        'company_name', 'contact_person', 'designation', 'email', 'phone', 'quantity', 'product_interest',
        'budget', 'deadline', 'branding_required', 'message', 'status', 'admin_note',
    ];

    protected $casts = ['status' => InquiryStatus::class, 'deadline' => 'date', 'branding_required' => 'boolean'];
}
