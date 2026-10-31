<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\KeywordMatcher;
use PHPUnit\Framework\TestCase;

/** Phase 1E-e: Japanese keywords keep their characters; EN/VI normalisation is unchanged. */
class KeywordMatcherJapaneseTest extends TestCase
{
    public function test_en_vi_keys_are_unchanged(): void
    {
        $this->assertSame('ha long bay cruise 2d1n', KeywordMatcher::normalize('Halong Bay cruise 2 days 1 night'));
        $this->assertSame('bay ha long 2d1n', KeywordMatcher::normalize('Vịnh Hạ Long 2 ngày 1 đêm'));
        $this->assertSame(['ha', 'long', 'bay', 'cruise'], KeywordMatcher::tokens('the Ha Long Bay cruise'));
    }

    public function test_japanese_is_kept_and_folded(): void
    {
        $this->assertSame('ハロン湾 クルーズ', KeywordMatcher::normalize('ハロン湾・クルーズ'));
        $this->assertSame(KeywordMatcher::normalize('はろん湾'), KeywordMatcher::normalize('ハロン湾'), 'hiragana = katakana');
        $this->assertSame(KeywordMatcher::normalize('ＨＡＬＯＮＧ　ベイ'), KeywordMatcher::normalize('halong ベイ'), 'NFKC: full-width Latin and spaces');
        $this->assertNotSame('', KeywordMatcher::normalize('ハロン湾'), 'never erased by ASCII folding');
    }

    public function test_japanese_similarity_uses_character_bigrams(): void
    {
        $this->assertSame(['ハロ', 'ロン', 'ン湾'], KeywordMatcher::tokens('ハロン湾'));
        $this->assertSame(1.0, KeywordMatcher::similarity('ハロン湾クルーズ', 'はろん湾クルーズ'));
        $this->assertGreaterThan(0.4, KeywordMatcher::similarity('ハロン湾クルーズ', 'ハロン湾クルーズ 2泊3日'));
        $this->assertLessThan(0.3, KeywordMatcher::similarity('ハロン湾クルーズ', 'ベトナム料理'));
    }
}
