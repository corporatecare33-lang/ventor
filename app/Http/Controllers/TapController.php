<?php

namespace App\Http\Controllers;

use App\Enums\NfcProfileStatus;
use App\Models\HomeSection;
use App\Models\NfcProfile;
use App\Models\Product;
use App\Support\Seo;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TapController extends Controller
{
    public function landing(): View
    {
        return view('tap.landing', [
            'section' => HomeSection::where('key', 'tap_intro')->first(),
            'products' => Product::visible()->forCard()->where('nfc_enabled', true)->orderBy('sort_order')->get(),
            'seo' => Seo::make('LeatherX Tap — NFC smart wallets', 'LeatherX Tap wallets have a built-in NFC chip that shares your digital business profile with one tap. No app, no battery.')
                ->canonical(route('tap')),
        ]);
    }

    /** Public profile opened by the NFC chip / QR code. */
    public function show(NfcProfile $profile): View
    {
        abort_unless($profile->status === NfcProfileStatus::Active, 404);

        NfcProfile::whereKey($profile->id)->increment('views_count');

        return view('tap.profile', [
            'profile' => $profile,
            'seo' => Seo::make($profile->name, collect([$profile->designation, $profile->company])->filter()->implode(' at '))->noindex(),
        ]);
    }

    public function vcard(NfcProfile $profile): Response
    {
        abort_unless($profile->status === NfcProfileStatus::Active, 404);

        $escape = fn (?string $value) => str_replace([',', ';', "\n"], ['\,', '\;', '\n'], (string) $value);

        $lines = array_filter([
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:'.$escape($profile->name),
            $profile->company ? 'ORG:'.$escape($profile->company) : null,
            $profile->designation ? 'TITLE:'.$escape($profile->designation) : null,
            $profile->phone ? 'TEL;TYPE=CELL:'.$escape($profile->phone) : null,
            $profile->email ? 'EMAIL:'.$escape($profile->email) : null,
            $profile->website ? 'URL:'.$escape($profile->website) : null,
            'NOTE:'.$escape($profile->publicUrl()),
            'END:VCARD',
        ]);

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.str($profile->name)->slug().'.vcf"',
        ]);
    }

    /** QR code (SVG) of the public profile URL — a backup for phones without NFC. */
    public function qr(NfcProfile $profile): Response
    {
        abort_unless($profile->status === NfcProfileStatus::Active, 404);

        $svg = (new QRCode(new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'outputBase64' => false,
            'addQuietzone' => true,
            'svgUseFillAttributes' => true,
        ])))->render($profile->publicUrl());

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
