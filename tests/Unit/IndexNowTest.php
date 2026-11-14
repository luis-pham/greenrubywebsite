<?php

namespace Tests\Unit;

use App\Services\Seo\IndexNow;
use PHPUnit\Framework\TestCase;

class IndexNowTest extends TestCase
{
    public function test_parses_urlset_with_and_without_lastmod(): void
    {
        $xml = '<?xml version="1.0"?><urlset><url><loc>https://greenrubycruises.com/a.html?x=1&amp;y=2</loc><lastmod>2026-11-13T07:12:33+07:00</lastmod>'
            . '<xhtml:link rel="alternate" hreflang="vi" href="https://greenrubycruises.com/vi/a.html"/></url>'
            . '<url><loc> https://greenrubycruises.com/b </loc></url></urlset>';

        $this->assertSame([
            'https://greenrubycruises.com/a.html?x=1&y=2' => '2026-11-13T07:12:33+07:00',
            'https://greenrubycruises.com/b' => '',
        ], IndexNow::parseSitemap($xml));
        $this->assertFalse(IndexNow::isIndex($xml));
    }

    public function test_parses_sitemap_index(): void
    {
        $xml = '<sitemapindex><sitemap><loc>https://greenrubycruises.com/sitemap-page.xml</loc><lastmod>2026-11-13</lastmod></sitemap></sitemapindex>';

        $this->assertTrue(IndexNow::isIndex($xml));
        $this->assertSame(['https://greenrubycruises.com/sitemap-page.xml' => '2026-11-13'], IndexNow::parseSitemap($xml));
    }

    public function test_changes_lists_new_updated_and_removed(): void
    {
        $old = ['/same' => '1', '/updated' => '1', '/removed' => '1'];
        $new = ['/same' => '1', '/updated' => '2', '/new' => ''];

        $this->assertSame(['/updated', '/new', '/removed'], IndexNow::changes($old, $new));
        $this->assertSame(['/new'], IndexNow::changes([], ['/new' => '']));
        $this->assertSame([], IndexNow::changes($new, $new));
    }

    public function test_on_host_drops_other_hosts(): void
    {
        $this->assertSame(
            ['https://greenrubycruises.com/a', 'https://GreenRubyCruises.com/b'],
            IndexNow::onHost(['https://greenrubycruises.com/a', 'https://GreenRubyCruises.com/b', 'https://write.greenrubycruises.com/c', '/rel'], 'greenrubycruises.com'),
        );
    }
}
