<?php

namespace App\Models;

use App\Models\Concerns\FlushesStorefrontCache;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use FlushesStorefrontCache;

    protected $fillable = ['title', 'slug', 'content', 'is_active', 'seo_title', 'seo_description'];

    protected $casts = ['is_active' => 'boolean'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
