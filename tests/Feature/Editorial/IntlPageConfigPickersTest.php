<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\TranslationFactory;
use Illuminate\Support\Facades\DB;
use Modules\FrontEnd\Helpers\FeUtils;

/**
 * Phase 2C: a new language's page configuration is a copy of the English one, so its item pickers (homepage cruises,
 * cabins, …) hold English ids. They resolve to the language's own translations; English and Vietnamese are unchanged.
 * Fixture: EN cruise #3 (green-ruby-1).
 */
class IntlPageConfigPickersTest extends EditorialTestCase
{
    public function test_english_ids_resolve_to_the_japanese_translation(): void
    {
        $id = TranslationFactory::create('cruise', 3, $this->ja(), 1)['id'];
        DB::table('app_cruise')->where('id', $id)->update(['slug' => 'green-ruby-1', 'publish_state' => 'published']);

        $ja = FeUtils::getPageConfigSourceDataValue([3], 'cruise', (int) $this->ja()->id);
        $this->assertSame([$id], array_map(fn ($c) => (int) $c->id, $ja));
        $this->assertSame([$id], array_map(fn ($c) => (int) $c->id, FeUtils::getPageConfigSourceDataValue([$id], 'cruise', (int) $this->ja()->id)), 'own id kept');
        $this->assertSame([3], array_map(fn ($c) => (int) $c->id, FeUtils::getPageConfigSourceDataValue([3], 'cruise', 2)), 'English unchanged');
    }

    public function test_an_item_without_a_translation_is_left_out(): void
    {
        $this->assertSame([], FeUtils::getPageConfigSourceDataValue([3], 'cruise', (int) $this->ja()->id));
    }
}
