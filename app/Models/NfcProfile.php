<?php

namespace App\Models;

use App\Enums\NfcProfileStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Software-only digital profile opened by the LeatherX Tap NFC chip / QR code.
 * The chip itself is programmed with the public URL; no hardware API is involved.
 */
class NfcProfile extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'order_id', 'slug', 'name', 'designation', 'company', 'bio',
        'phone', 'email', 'website', 'facebook', 'instagram', 'linkedin', 'whatsapp', 'status',
    ];

    protected $casts = ['status' => NfcProfileStatus::class];

    protected static function booted(): void
    {
        static::creating(function (NfcProfile $profile) {
            $profile->slug ??= static::uniqueSlug($profile->name);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        do {
            $slug = Str::slug(Str::limit($name, 30, '')).'-'.Str::lower(Str::random(6));
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function publicUrl(): string
    {
        return route('nfc.show', $this->slug);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
