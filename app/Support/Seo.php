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

    /** schema.org WebPage subtype for this page (WebPage, CollectionPage, ItemPage, AboutPage, ContactPage, FAQPage…). */
    public string $pageType = 'WebPage';

    public bool $isHome = false;

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

    public function pageType(string $type): self
    {
        $this->pageType = $type;

        return $this;
    }

    public function home(): self
    {
        $this->isHome = true;

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

    /** Title used for social cards (without the site suffix). */
    public function shareTitle(): string
    {
        return $this->title ?: (setting('seo_home_title') ?: setting('site_name', config('app.name')));
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

        return $default ? Storage::disk('public')->url($default) : asset('images/og-default.jpg');
    }

    /**
     * Page schemas plus the site-wide Organization / WebSite graph. Organization details are
     * taken only from Website settings — nothing is invented when a field is empty.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allSchemas(): array
    {
        if ($this->noindex) {
            return $this->schemas;
        }

        $site = setting('site_name', config('app.name'));
        $home = url('/');
        $logo = setting('site_logo') ? Storage::disk('public')->url(setting('site_logo')) : asset('apple-touch-icon.png');
        $sameAs = array_values(array_filter([setting('social_facebook'), setting('social_instagram'), setting('social_youtube'), setting('social_linkedin'), setting('social_tiktok')]));

        $organization = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $home.'#organization',
            'name' => $site,
            'url' => $home,
            'logo' => $logo,
            'email' => setting('contact_email') ?: null,
            'telephone' => setting('contact_phone') ?: null,
            'sameAs' => $sameAs ?: null,
        ]);

        $graph = [$organization];

        $page = array_filter([
            '@context' => 'https://schema.org',
            '@type' => $this->pageType,
            'name' => $this->title ?: $site,
            'description' => $this->metaDescription() ?: null,
            'url' => $this->canonical ?? url()->current(),
            'inLanguage' => 'en-BD',
            'isPartOf' => ['@id' => $home.'#website'],
            'publisher' => ['@id' => $home.'#organization'],
        ]);

        if ($this->isHome) {
            $graph[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                '@id' => $home.'#website',
                'name' => $site,
                'url' => $home,
                'inLanguage' => 'en-BD',
                'publisher' => ['@id' => $home.'#organization'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('shop').'?q={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ];

            // A physical store is only described when the business has entered a real address and phone.
            if (setting('contact_address') && setting('contact_phone')) {
                $graph[] = array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Store',
                    '@id' => $home.'#store',
                    'name' => $site,
                    'url' => $home,
                    'image' => $logo,
                    'telephone' => setting('contact_phone'),
                    'email' => setting('contact_email') ?: null,
                    'address' => ['@type' => 'PostalAddress', 'streetAddress' => setting('contact_address'), 'addressCountry' => 'BD'],
                    'parentOrganization' => ['@id' => $home.'#organization'],
                ]);
            }
        }

        return [...$graph, $page, ...$this->schemas];
    }

    public function robots(): string
    {
        return $this->noindex || ! setting('seo_allow_indexing', true) ? 'noindex, nofollow' : 'index, follow';
    }
}
