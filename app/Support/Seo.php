<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Per-page SEO data: title, description, canonical, Open Graph and JSON-LD.
 * Controllers build one and pass it to the view as `$seo`; the layout renders it.
 */
class Seo
{
    public ?string $title = null;

    public ?string $description = null;

    public ?string $image = null;

    public string $type = 'website';

    public ?string $canonical = null;

    public bool $noindex = false;

    /** @var array<int, array<string, mixed>> */
    public array $schemas = [];

    public static function make(?string $title = null, ?string $description = null): self
    {
        $seo = new self;
        $seo->title = $title;
        $seo->description = $description;

        return $seo;
    }

    public function image(?string $url): self
    {
        $this->image = $url;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function canonical(?string $url): self
    {
        $this->canonical = $url;

        return $this;
    }

    public function noindex(bool $noindex = true): self
    {
        $this->noindex = $noindex;

        return $this;
    }

    /** @param array<string, mixed> $schema */
    public function schema(array $schema): self
    {
        $this->schemas[] = ['@context' => 'https://schema.org', ...$schema];

        return $this;
    }

    /** @param array<int, array{0: string, 1: ?string}> $crumbs [name, url] pairs */
    public function breadcrumbs(array $crumbs): self
    {
        return $this->schema([
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn ($crumb, $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb[0],
                'item' => $crumb[1] ?? null,
            ]))->all(),
        ]);
    }

    public function fullTitle(): string
    {
        $site = setting('site_name', config('app.name'));

        if (! $this->title) {
            return setting('seo_home_title') ?: $site;
        }

        return $this->title.setting('seo_title_suffix', " | {$site}");
    }

    public function metaDescription(): string
    {
        return str($this->description ?: setting('seo_default_description', ''))->stripTags()->squish()->limit(160)->toString();
    }

    public function imageUrl(): ?string
    {
        if ($this->image) {
            return $this->image;
        }

        $default = setting('seo_og_image');

        return $default ? Storage::disk('public')->url($default) : null;
    }

    public function robots(): string
    {
        return $this->noindex || ! setting('seo_allow_indexing', true) ? 'noindex, nofollow' : 'index, follow';
    }
}
