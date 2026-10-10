<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Support\Seo;
use App\Support\StorefrontCache;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __invoke(): View
    {
        $faqs = StorefrontCache::remember('faqs', fn () => Faq::active()->get()->groupBy('category'), 3600);

        $seo = Seo::make('Frequently asked questions', 'Answers about ordering, delivery, payment, LeatherX Tap NFC wallets, engraving, warranty and returns.')
            ->canonical(route('faq'))
            ->schema([
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->flatten()->map(fn (Faq $faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
                ])->values()->all(),
            ]);

        return view('pages.faq', compact('faqs', 'seo'));
    }
}
