<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\KeywordMatcher;
use App\Services\Seo\OverlapDetector;
use PHPUnit\Framework\TestCase;

/**
 * Keyword normalisation and the conflict / healthy-overlap rules of the SEO guard, on cases taken from the
 * 25/10/2026 audit (docs/seo-audit/04, 13).
 */
class OverlapDetectorTest extends TestCase
{
    private const EN = 2;
    private const VI = 1;

    public function testNormalisationUnifiesSpellingsAndVietnamese()
    {
        $this->assertSame(KeywordMatcher::normalize('Ha Long Bay cruise 2 days 1 night'), KeywordMatcher::normalize('halong bay cruises 2D1N'));
        $this->assertSame(KeywordMatcher::normalize('du thuyền Hạ Long 2 ngày 1 đêm'), KeywordMatcher::normalize('du thuyen ha long 2d1n'));
        $this->assertSame(1.0, KeywordMatcher::similarity('Hanoi to Halong Bay', 'hanoi to ha long bay'));
        $this->assertLessThan(0.5, KeywordMatcher::similarity('best time to visit ha long bay', 'ha long bay kayaking'));
    }

    public function testIntentRelations()
    {
        $this->assertSame('same', KeywordMatcher::intentRelation('I', 'i'));
        $this->assertSame('adjacent', KeywordMatcher::intentRelation('C', 'T'));
        $this->assertSame('adjacent', KeywordMatcher::intentRelation('I', 'C'));
        $this->assertSame('different', KeywordMatcher::intentRelation('I', 'T'));
        $this->assertSame('unknown', KeywordMatcher::intentRelation(null, 'T'));
    }

    public function testSameKeywordSameIntentAsLiveOwnerIsAConflictToUpdate()
    {
        // Draft #237 "Hanoi to Halong Bay" vs the live transfer guide that owns the keyword.
        $r = OverlapDetector::analyse($this->candidate('Hanoi to Halong Bay', 'I'), [
            $this->owner(1, 'hanoi to ha long bay', 'I', '/guides/hanoi-to-halong-bay'),
        ]);
        $this->assertSame('conflict', $r['verdict']);
        $this->assertSame('update_existing', $r['recommendation']);
        $this->assertSame('/guides/hanoi-to-halong-bay', $r['target']['url']);
    }

    public function testConflictWithADraftOnlySuggestsMerging()
    {
        $r = OverlapDetector::analyse($this->candidate('how many days in ha long bay', 'I'), [
            ['type' => 'article', 'id' => 234, 'language_id' => self::EN, 'keywords' => ['How many days in Ha Long Bay'], 'intent' => 'I', 'url' => null, 'status' => 'draft'],
        ]);
        $this->assertSame('merge', $r['recommendation']);
        $this->assertSame(234, $r['target']['id']);
    }

    public function testNamedPlaceWithItsOwnDestinationPageIsAConflictEvenWhenHidden()
    {
        // Draft #253 "Sung Sot Cave: A Visitor's Guide" vs the hidden destination page.
        $c = $this->candidate('Sung Sot Cave visitor guide', 'I', "Sung Sot Cave: A Visitor's Guide to Ha Long Bay's Surprise Cave");
        $r = OverlapDetector::analyse($c, [
            ['type' => 'destination', 'id' => 1, 'language_id' => self::EN, 'keywords' => ['Sung Sot Cave', 'sung-sot-cave'], 'intent' => 'I', 'url' => '/destinations/sung-sot-cave', 'status' => 'hidden', 'entity' => true],
        ]);
        $this->assertSame('conflict', $r['verdict']);
        $this->assertSame('update_existing', $r['recommendation']);
        $this->assertTrue($r['matches'][0]['entity']);
    }

    public function testDifferentIntentIsAHealthyOverlap()
    {
        // An informational guide about Lan Ha Bay vs the transactional Green Ruby 2 page.
        $r = OverlapDetector::analyse($this->candidate('lan ha bay cruise', 'I'), [
            $this->owner(2, 'lan ha bay cruise', 'T', '/cruise/green-ruby-2-4.html'),
        ]);
        $this->assertSame('healthy_overlap', $r['verdict']);
        $this->assertSame('create_new', $r['recommendation']);
        $this->assertSame('different', $r['matches'][0]['intent']);
    }

    public function testNarrowerPageUnderAHubIsHealthy()
    {
        // One itinerary under the itinerary hub: same commercial family, narrower topic.
        $r = OverlapDetector::analyse($this->candidate('ha long bay cruise itinerary 2 days 1 night', 'C'), [
            $this->owner(3, 'ha long bay cruise itinerary', 'C', '/itinerary', 'hub'),
        ]);
        $this->assertSame('healthy_overlap', $r['verdict']);
    }

    public function testRestatedContentIsAConflictEvenWithAnotherKeyword()
    {
        $text = str_repeat('Guests check in at the Green Ruby lounge at Tuan Chau from about 10:45 and the tender leaves at 11:45 for the ship. ', 12);
        $c = array_merge($this->candidate('tuan chau cruise port check in', 'I'), ['content' => $text]);
        $r = OverlapDetector::analyse($c, [
            ['type' => 'owner', 'id' => 4, 'language_id' => self::EN, 'keywords' => ['tuan chau check in boarding'], 'intent' => 'C', 'url' => '/guides/hanoi-to-halong-bay', 'status' => 'live', 'content' => $text],
        ]);
        $this->assertSame('conflict', $r['verdict']);
        $this->assertGreaterThanOrEqual(0.4, $r['matches'][0]['content']);
    }

    public function testOtherLanguageAndSelfAreIgnored()
    {
        $c = array_merge($this->candidate('hanoi to ha long bay', 'I'), ['self' => ['article' => 9, 'plan' => null, 'url' => null]]);
        $r = OverlapDetector::analyse($c, [
            ['type' => 'article', 'id' => 9, 'language_id' => self::EN, 'keywords' => ['hanoi to ha long bay'], 'intent' => 'I', 'url' => '/x', 'status' => 'live'],
            ['type' => 'owner', 'id' => 5, 'language_id' => self::VI, 'keywords' => ['hanoi to ha long bay'], 'intent' => 'I', 'url' => '/vi/cam-nang/ha-noi-di-ha-long', 'status' => 'live'],
        ]);
        $this->assertSame('clear', $r['verdict']);
    }

    public function testSameTopicInTheOtherBayIsNotAConflict()
    {
        // Ha Long and Lan Ha are separate products: the Lan Ha cruise page does not own the Ha Long query.
        $r = OverlapDetector::analyse($this->candidate('ha long bay luxury cruise', 'C'), [
            $this->owner(2, 'lan ha bay luxury cruise', 'C', '/cruise/green-ruby-2-4.html'),
        ]);
        $this->assertNotSame('conflict', $r['verdict']);
        $this->assertLessThan(0.85, KeywordMatcher::similarity('ha long bay luxury cruise', 'lan ha bay luxury cruise'));
    }

    public function testCabinCategoryWithItsOwnPageIsAConflict()
    {
        // Draft #260 "Halong Bay cruise suite" vs the Imperial Suite page.
        $r = OverlapDetector::analyse($this->candidate('Imperial Suite Ha Long Bay cruise', 'C', 'Imperial Suite on a Ha Long Bay cruise'), [
            ['type' => 'cabin', 'id' => 24, 'language_id' => self::EN, 'keywords' => ['Imperial Suite'], 'intent' => 'C', 'url' => '/cabin/imperial-suite-24.html', 'status' => 'live', 'entity' => true],
        ]);
        $this->assertSame('conflict', $r['verdict']);
        $this->assertSame('update_existing', $r['recommendation']);
    }

    public function testUnrelatedTopicIsClear()
    {
        $r = OverlapDetector::analyse($this->candidate('halong bay typhoon season', 'I'), [
            $this->owner(1, 'hanoi to ha long bay', 'I', '/guides/hanoi-to-halong-bay'),
            $this->owner(6, 'ha long bay kayaking', 'I', '/experiences/kayaking-8.html'),
        ]);
        $this->assertSame('clear', $r['verdict']);
    }

    private function candidate(string $keyword, ?string $intent, string $title = ''): array
    {
        return ['language_id' => self::EN, 'keyword' => $keyword, 'secondary' => [], 'intent' => $intent, 'title' => $title, 'content' => '', 'self' => []];
    }

    private function owner(int $id, string $keyword, string $intent, string $url, ?string $role = null): array
    {
        return ['type' => 'owner', 'id' => $id, 'language_id' => self::EN, 'keywords' => [$keyword], 'intent' => $intent, 'url' => $url, 'status' => 'live', 'role' => $role];
    }
}
