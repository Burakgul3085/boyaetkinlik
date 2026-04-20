<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ColoringPage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Arama motorları için dinamik sitemap.xml (anasayfa, iletişim, kategoriler, boyama sayfaları).
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml.v1', 3600, function () {
            $urls = [];

            $urls[] = [
                'loc' => route('home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
                'lastmod' => null,
            ];

            $urls[] = [
                'loc' => route('contact.show'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
                'lastmod' => null,
            ];

            Category::query()
                ->orderBy('id')
                ->each(function (Category $category) use (&$urls) {
                    $urls[] = [
                        'loc' => route('categories.show', ['slug' => $category->slug]),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                        'lastmod' => $category->updated_at,
                    ];
                });

            ColoringPage::query()
                ->orderBy('id')
                ->each(function (ColoringPage $page) use (&$urls) {
                    $urls[] = [
                        'loc' => route('products.show', ['coloringPage' => $page]),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                        'lastmod' => $page->updated_at,
                    ];
                });

            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            foreach ($urls as $u) {
                $out .= '  <url>'."\n";
                $out .= '    <loc>'.self::xmlEscape($u['loc']).'</loc>'."\n";
                if (! empty($u['lastmod'])) {
                    $out .= '    <lastmod>'.$u['lastmod']->toAtomString().'</lastmod>'."\n";
                }
                $out .= '    <changefreq>'.self::xmlEscape($u['changefreq']).'</changefreq>'."\n";
                $out .= '    <priority>'.self::xmlEscape($u['priority']).'</priority>'."\n";
                $out .= '  </url>'."\n";
            }

            $out .= '</urlset>';

            return $out;
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private static function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
