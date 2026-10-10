<?php

namespace App\Http\Controllers;

use App\Enums\EngravingStatus;
use App\Enums\EngravingType;
use App\Models\EngravingRequest;
use App\Models\HomeSection;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EngravingController extends Controller
{
    public function create(): View
    {
        return view('services.engraving', [
            'section' => HomeSection::where('key', 'engraving')->first(),
            'products' => Product::visible()->where('engraving_available', true)->orderBy('name')->get(['id', 'name', 'engraving_price']),
            'seo' => Seo::make('Laser engraving & personalisation', 'Personalise LeatherX wallets and bags with names, initials, quotes or your company logo using precision laser engraving.')
                ->canonical(route('engraving')),
        ]);
    }

    /** Stand-alone engraving inquiry (e.g. for items bought earlier or custom work). */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'product_id' => ['nullable', Rule::exists('products', 'id')->where('engraving_available', true)],
            'type' => ['required', Rule::enum(EngravingType::class)],
            'text' => ['nullable', 'required_unless:type,logo', 'string', 'max:40'],
            'font' => ['nullable', Rule::in(EngravingRequest::FONTS)],
            'position' => ['nullable', Rule::in(EngravingRequest::POSITIONS)],
            'logo_instruction' => ['nullable', 'required_if:type,logo', 'string', 'max:1000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ], ['phone.regex' => 'Please enter a valid Bangladeshi mobile number.']);

        EngravingRequest::create([
            ...collect($data)->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)->all(),
            'user_id' => $request->user()?->id,
            'status' => EngravingStatus::Pending,
        ]);

        return back()->with('success', 'Your engraving request has been received. We will contact you shortly.');
    }
}
