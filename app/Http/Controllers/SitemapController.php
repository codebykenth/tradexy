<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Static Base Links
        $xml .= '<url><loc>'.url('/').'</loc><lastmod>'.now()->toAtomString().'</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>';
        $xml .= '<url><loc>'.route('login').'</loc><priority>0.5</priority></url>';
        $xml .= '<url><loc>'.route('register').'</loc><priority>0.8</priority></url>';
        $xml .= '</urlset>';

        // CRITICAL FOR VERCEL: Force 'text/xml' headers so Vercel doesn't close the channel
        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
