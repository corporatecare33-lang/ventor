<?php

namespace App\Http\Controllers;

use App\Enums\InquiryStatus;
use App\Models\CorporateInquiry;
use App\Models\HomeSection;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorporateController extends Controller
{
    public function create(): View
    {
        return view('services.corporate', [
            'section' => HomeSection::where('key', 'corporate')->first(),
            'products' => Product::visible()->forCard()->whereHas('category', fn ($q) => $q->where('slug', 'corporate-gifts'))->get(),
            'seo' => Seo::make('Corporate gifting & bulk orders', 'Premium branded leather gifts for employees and clients. Logo engraving, custom gift boxes and nationwide delivery.')
                ->canonical(route('corporate')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'contact_person' => ['required', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'quantity' => ['required', 'integer', 'min:10', 'max:100000'],
            'product_interest' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'branding_required' => ['boolean'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        CorporateInquiry::create([
            ...collect($data)->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)->all(),
            'branding_required' => $request->boolean('branding_required'),
            'status' => InquiryStatus::New,
        ]);

        return back()->with('success', 'Thank you! Our corporate team will send you a quotation within one working day.');
    }
}
