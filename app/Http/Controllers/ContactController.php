<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('pages.contact', [
            'seo' => Seo::make('Contact us', 'Get in touch with LeatherX for orders, engraving, warranty or corporate gifting.')->canonical(route('contact')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'website' => ['prohibited'], // honeypot
        ]);

        ContactMessage::create(collect($data)->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)->all());

        return back()->with('success', 'Thanks for reaching out — we usually reply within a few hours.');
    }
}
