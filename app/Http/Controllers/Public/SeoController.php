<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Excursions\Models\Excursion;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /bookings',
            'Disallow: /payment',
            'Disallow: /payments',
            'Sitemap: '.$baseUrl.'/sitemap.xml',
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $excursions = Excursion::query()
            ->active()
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()
            ->view('seo.sitemap', ['excursions' => $excursions])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
