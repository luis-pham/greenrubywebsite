<?php

namespace Tests\Feature\Booking;

use App\Http\Controllers\Api\BookingApiController;
use App\Models\Booking;
use App\Models\Payment;
use App\Payments\BookingPricer;
use App\Payments\PriceList;
use App\Payments\TermsAcceptance;
use App\Services\Content\ContentTranslations;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * i18n Phase 1A (greenruby-web docs/i18n-audit/13 §3, owner decisions B–D of 31/10/2026): currency comes from
 * the price list, never from the display language; only price-list rows are bookable (ja rows are text only and
 * book through the linked English rows); cabin, itinerary and cruise must belong together; past departures are
 * refused whatever the deposit switches say; English bookings get an English duration label.
 */
class BookingIntegrityTest extends BookingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::table('ad_language')->insert(['id' => 3, 'code' => 'ja', 'is_default' => false]);

        Schema::drop('app_cabin');
        Schema::create('app_cabin', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('cruise_id')->nullable();
            $t->unsignedInteger('capacity')->default(2);
            foreach (['over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant'] as $c) {
                $t->unsignedInteger($c)->default(0);
            }
            $t->string('occupancy_limits', 100)->nullable();
        });
        Schema::create('app_cabin_price', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('cabin_id');
            $t->unsignedInteger('duration');
            $t->unsignedInteger('guest');
            $t->decimal('price', 15, 2);
        });
        Schema::create('app_itinerary', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('duration')->nullable();
        });
        Schema::create('app_service', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('type');
            $t->decimal('price', 15, 2)->default(0);
        });
        Schema::create('content_translations', function (Blueprint $t) {
            $t->increments('id');
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->char('group_key', 36);
            $t->unsignedInteger('language_id');
        });
        ContentTranslations::flush();

        // English (USD) on cruise 3 / itinerary 3, Vietnamese (VND) on cruise 1 / itinerary 8, both 3 days;
        // a Japanese text row (cabin 90) linked to English cabin 24; cruise 4 sails only itinerary 4.
        DB::table('app_itinerary')->insert([
            ['id' => 3, 'language_id' => 2, 'duration' => 3],
            ['id' => 4, 'language_id' => 2, 'duration' => 2],
            ['id' => 8, 'language_id' => 1, 'duration' => 3],
        ]);
        DB::table('app_cruise_itinerary')->delete();
        DB::table('app_cruise_itinerary')->insert([
            ['cruise_id' => 3, 'itinerary_id' => 3, 'start_at' => '2026-03-20 08:00:00'],
            ['cruise_id' => 4, 'itinerary_id' => 4, 'start_at' => '2026-03-21 08:00:00'],
            ['cruise_id' => 1, 'itinerary_id' => 8, 'start_at' => '2026-03-22 08:00:00'],
        ]);
        DB::table('app_cabin')->insert([
            ['id' => 24, 'language_id' => 2, 'cruise_id' => 3, 'capacity' => 2],
            ['id' => 25, 'language_id' => 2, 'cruise_id' => 4, 'capacity' => 2],
            ['id' => 38, 'language_id' => 1, 'cruise_id' => 1, 'capacity' => 2],
            ['id' => 90, 'language_id' => 3, 'cruise_id' => 3, 'capacity' => 2],
        ]);
        DB::table('app_cabin_price')->insert([
            ['cabin_id' => 24, 'duration' => 3, 'guest' => 2, 'price' => 700],
            ['cabin_id' => 25, 'duration' => 3, 'guest' => 2, 'price' => 650],
            ['cabin_id' => 38, 'duration' => 3, 'guest' => 2, 'price' => 30000000],
        ]);
        DB::table('content_translations')->insert([
            ['entity_type' => 'cabin', 'entity_id' => 24, 'group_key' => 'g-imperial', 'language_id' => 2],
            ['entity_type' => 'cabin', 'entity_id' => 38, 'group_key' => 'g-imperial', 'language_id' => 1],
            ['entity_type' => 'cabin', 'entity_id' => 90, 'group_key' => 'g-imperial', 'language_id' => 3],
        ]);
    }

    private function price(array $booking): array
    {
        $pricer = new BookingPricer();

        return [$pricer->price($booking), $pricer->error];
    }

    private function cabin(int $id, int $adults = 2): array
    {
        return [['cabin_id' => $id, 'quantity' => 1, 'adults' => $adults]];
    }

    // --- Occupancy (agent contract III.2, owner 2026-11-02) ------------------------------------

    private function guests(int $id, int $adults, int $c612 = 0, int $c25 = 0, int $infants = 0): array
    {
        return ['itinerary_id' => '3-3', 'cabins' => [['cabin_id' => $id, 'quantity' => 1, 'adults' => $adults,
            'children_6_12' => $c612, 'children_2_5' => $c25, 'infants' => $infants]]];
    }

    public function test_occupancy_limits_allow_two_adults_with_two_children_or_three_adults_alone(): void
    {
        DB::table('app_cabin')->where('id', 24)->update(['occupancy_limits' => '2+2, 3+0', 'over_capacity_adult' => 3]);
        DB::table('app_cabin_price')->insert(['cabin_id' => 24, 'duration' => 3, 'guest' => 1, 'price' => 500]);
        $fits = fn (array $b) => $this->price($b)[0] !== null;

        $this->assertTrue($fits($this->guests(24, 2, 1, 0, 1)), 'two adults, a child and an infant');
        $this->assertTrue($fits($this->guests(24, 3)), 'three adults');
        $this->assertTrue($fits($this->guests(24, 1, 2)), 'one adult and two children');
        $this->assertFalse($fits($this->guests(24, 3, 0, 0, 1)), 'a third adult takes the children\'s place, infants included');
        $this->assertFalse($fits($this->guests(24, 2, 1, 1, 1)), 'three children, counting the infant');
        $this->assertFalse($fits($this->guests(24, 4)), 'four adults');
    }

    public function test_occupancy_limits_replace_the_per_type_limits(): void
    {
        // Imperial: 4 adults + 4 children although capacity is 3 and over_capacity_adult says 3.
        DB::table('app_cabin')->where('id', 24)->update(['capacity' => 3, 'over_capacity_adult' => 3, 'occupancy_limits' => '4+4']);
        DB::table('app_cabin_price')->insert(['cabin_id' => 24, 'duration' => 3, 'guest' => 3, 'price' => 900]);

        $this->assertNotNull($this->price($this->guests(24, 4, 2, 1, 1))[0]);
        $this->assertNull($this->price($this->guests(24, 5))[0]);
        $this->assertNull($this->price($this->guests(24, 4, 3, 1, 1))[0]);
    }

    public function test_cabins_without_occupancy_limits_keep_the_per_type_rule(): void
    {
        $this->assertNotNull($this->price($this->guests(24, 3, 2))[0], 'capacity 2: up to 3 adults and 2 children of each age');
        $this->assertNull($this->price($this->guests(24, 4))[0]);
    }

    // --- Price lists ---------------------------------------------------------------------------

    public function test_price_lists_give_currency_by_list_not_by_display_language(): void
    {
        $this->assertSame('usd', PriceList::currencyFor('en'));
        $this->assertSame('vnd', PriceList::currencyFor('vi'));
        $this->assertNull(PriceList::currencyFor('ja'));

        $this->assertSame('en', PriceList::inventoryLanguageCode('ja'));
        $this->assertSame('en', PriceList::inventoryLanguageCode('EN'));
        $this->assertSame('vi', PriceList::inventoryLanguageCode('vi'));
        $this->assertSame('all', PriceList::inventoryLanguageCode('all'));
        $this->assertSame('xx', PriceList::inventoryLanguageCode('xx'));

        $this->assertSame('USD', PriceList::displayCurrency('en'));
        $this->assertSame('VND', PriceList::displayCurrency('vi'));
        $this->assertSame('USD', PriceList::displayCurrency('ja'));
        $this->assertSame('USD', PriceList::displayCurrency('xx'));
    }

    public function test_japanese_rows_book_through_the_linked_english_row(): void
    {
        $this->assertSame(24, PriceList::inventoryId('cabin', 24, 2));
        $this->assertSame(38, PriceList::inventoryId('cabin', 38, 1));
        $this->assertSame(24, PriceList::inventoryId('cabin', 90, 3));

        DB::table('app_cabin')->insert(['id' => 91, 'language_id' => 3, 'cruise_id' => 3]);
        $this->assertNull(PriceList::inventoryId('cabin', 91, 3), 'an unlinked ja row has no inventory');
    }

    // --- Pricing parity and relationships ------------------------------------------------------

    public function test_english_and_vietnamese_bookings_keep_their_own_price_list(): void
    {
        [$en] = $this->price(['itinerary_id' => '3-3', 'cabins' => $this->cabin(24)]);
        $this->assertSame('usd', $en['currency']);
        $this->assertSame(700.0, $en['total']);

        [$vi] = $this->price(['itinerary_id' => '8-1', 'cabins' => $this->cabin(38)]);
        $this->assertSame('vnd', $vi['currency']);
        $this->assertSame(30000000.0, $vi['total']);

        [$plain] = $this->price(['itinerary_id' => 3, 'cabins' => $this->cabin(24)]);
        $this->assertSame(700.0, $plain['total'], 'a plain itinerary id still works');
    }

    public function test_a_japanese_text_row_is_never_booking_inventory(): void
    {
        [$priced, $error] = $this->price(['itinerary_id' => '3-3', 'cabins' => $this->cabin(90)]);
        $this->assertNull($priced);
        $this->assertSame('price_list_unknown', $error);
    }

    public function test_cabins_itinerary_and_cruise_must_belong_together(): void
    {
        $cases = [
            'itinerary_language_mismatch' => ['itinerary_id' => '8-1', 'cabins' => $this->cabin(24)],
            'cabin_cruise_mixed' => ['itinerary_id' => '3-3', 'cabins' => array_merge($this->cabin(24), $this->cabin(25))],
            'cabin_cruise_mismatch' => ['itinerary_id' => '3-4', 'cabins' => $this->cabin(24)],
            'itinerary_cruise_mismatch' => ['itinerary_id' => '3-4', 'cabins' => $this->cabin(25)],
            'itinerary_unknown' => ['itinerary_id' => '999-3', 'cabins' => $this->cabin(24)],
        ];
        foreach ($cases as $expected => $booking) {
            [$priced, $error] = $this->price($booking);
            $this->assertNull($priced, $expected);
            $this->assertSame($expected, $error);
        }
    }

    // --- Departure dates (policy C) -------------------------------------------------------------

    public function test_past_departures_are_refused_even_with_deposits_switched_off(): void
    {
        $this->assertFalse(config('booking.deposit_enabled'));

        $this->init([], '2026-03-20')->assertStatus(422)->assertJson(['code' => 'departure_date_past']);
        $this->init([], '2026-10-26')->assertStatus(422)->assertJson(['code' => 'departure_date_past']);
        $this->assertSame([], FakeGateway::$calls);
        $this->assertSame(0, Payment::count());

        $this->init([], self::TODAY)->assertOk();
        $this->init([], '2027-02-15')->assertOk();
    }

    public function test_reservation_requests_refuse_past_dates_but_may_come_without_a_date(): void
    {
        $booking = ['full_name' => 'Test Guest', 'email' => 'guest@example.test', 'itinerary_id' => '5-7', 'cabins' => [['cabin_id' => 1, 'cabin_name' => 'Suite', 'adults' => 2]]];

        $this->postJson('/api/booking/inquiry', ['booking' => $booking + ['departure_date' => '2026-04-09']])
            ->assertStatus(422)->assertJson(['code' => 'departure_date_past']);
        $this->assertSame(0, Booking::count());

        $this->postJson('/api/booking/inquiry', ['booking' => $booking])->assertStatus(201);
        $this->postJson('/api/booking/inquiry', ['booking' => $booking + ['departure_date' => '2027-02-15']])->assertStatus(201);
    }

    // --- Terms and labels (policy D) ------------------------------------------------------------

    public function test_terms_accepted_on_a_page_without_own_terms_are_recorded_as_english(): void
    {
        $this->assertSame('en', TermsAcceptance::locale('en'));
        $this->assertSame('vi', TermsAcceptance::locale('vi'));
        $this->assertSame('en', TermsAcceptance::locale('ja'));
        $this->assertSame('en', TermsAcceptance::locale('EN'));
        $this->assertNull(TermsAcceptance::locale(null));
        $this->assertNull(TermsAcceptance::locale(''));

        $this->migrate();
        config(['booking.terms_required' => true, 'booking.terms_version' => '2027-01-01']);
        $this->init(['terms' => ['accepted' => true, 'version' => '2027-01-01', 'locale' => 'ja']])->assertOk();
        $this->assertSame('en', Booking::first()->terms_locale);
    }

    public function test_duration_label_follows_the_booked_rows_language(): void
    {
        $label = fn (int $days, string $lang) => (fn () => self::durationLabel($days, $lang))->call(new BookingApiController());

        $this->assertSame('2 Days / 1 Night', $label(2, 'en'));
        $this->assertSame('3 Days / 2 Nights', $label(3, 'en'));
        $this->assertSame('1 Day', $label(1, 'en'));
        $this->assertSame('2 Ngày 1 Đêm', $label(2, 'vi'), 'Vietnamese label unchanged');
        $this->assertSame('3 Ngày 2 Đêm', $label(3, 'vi'));
        $this->assertSame('', $label(0, 'en'));
    }
}
