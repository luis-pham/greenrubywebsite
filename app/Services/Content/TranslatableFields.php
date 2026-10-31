<?php

namespace App\Services\Content;

/**
 * What a translation is made of, per content type (phase 1E-b; plan §3 "TranslatableFields"). One whitelist used by
 * TranslationFactory (what a new translation copies), Freshness (what makes a translation outdated) and
 * BusinessFields (what a text-only language may never write).
 *
 *   text      translatable text: copied into the new draft as the text to translate, hashed for freshness
 *   copy      presentation and references copied unchanged (images, ordering, coordinates)
 *   business  prices, capacities, specifications, times: never copied; read-only for a language without its own
 *             price list (I-9, I-10). The values shown come from the price-list row (en for ja).
 *   parents   column => content type of the referenced row: mapped to that row's translation in the target
 *             language (required: refused when it has none; optional: left empty). Type 'group' is a structural
 *             app_group (cabin facility type, experience tab, service group): not a translation group, it is the
 *             target language's group of the same type and slug, created by `php artisan i18n:seed-locale`.
 *   children  child tables copied with the row (text + copy columns), keyed by their foreign key
 *   grouped   true: the row joins the source's content_translations group; false: linked to its source only
 *             through translation_meta (FAQ items, services, amenities have no translation groups)
 *   slug      the type has a stored URL slug: never copied for an intl language (an editor sets an ASCII slug)
 */
final class TranslatableFields
{
    public const TYPES = [
        'cruise' => [
            'table' => 'app_cruise', 'label' => 'Du thuyền', 'grouped' => true, 'slug' => true,
            'text' => ['name', 'summary', 'content', 'description_design', 'green_technology', 'seo_title', 'seo_description'],
            'copy' => ['image_link', 'cover_link'],
            'business' => ['star_rating', 'capacity', 'total_floor', 'total_cabin', 'dimension_length', 'year_built'],
        ],
        'cabin' => [
            'table' => 'app_cabin', 'label' => 'Cabin', 'grouped' => true, 'slug' => true,
            'text' => ['name', 'summary', 'content', 'view', 'cabin_class', 'seo_title', 'seo_description'],
            'copy' => ['image_link', 'image_gallery', 'ord'],
            'business' => ['capacity', 'over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant', 'capacity_exceeded', 'area', 'discount_percent'],
            'parents' => ['cruise_id' => ['type' => 'cruise', 'required' => true], 'group_id' => ['type' => 'group', 'required' => true]],
            'children' => ['app_cabin_room' => ['key' => 'cabin_id', 'text' => ['title', 'description'], 'copy' => ['ord']]],
        ],
        'itinerary' => [
            'table' => 'app_itinerary', 'label' => 'Hành trình', 'grouped' => true, 'slug' => true,
            'text' => ['name', 'description', 'important_note', 'seo_title', 'seo_description'],
            'copy' => ['image_link', 'cover_link', 'destination'],
            'business' => ['bay', 'duration', 'start_time'],
        ],
        'experience' => [
            'table' => 'app_exp_activity', 'label' => 'Hoạt động trải nghiệm', 'grouped' => true, 'slug' => true,
            'text' => ['name', 'summary', 'content', 'note', 'duration', 'seo_title', 'seo_description'],
            'copy' => ['image_link', 'cover_link', 'is_featured'],
            'business' => ['start_time', 'end_time'],
            'parents' => ['cruise_id' => ['type' => 'cruise', 'required' => false], 'group_id' => ['type' => 'group', 'required' => false]],
        ],
        'destination' => [
            'table' => 'app_destination', 'label' => 'Điểm đến', 'grouped' => true, 'slug' => true,
            'text' => ['name', 'heading', 'summary', 'content', 'facts', 'faq', 'image_alt', 'seo_title', 'seo_description'],
            'copy' => ['bay', 'category', 'sources', 'latitude', 'longitude', 'coords_verified', 'coords_source', 'image_link', 'image_credit', 'ord'],
            'business' => [],
        ],
        'article' => [
            'table' => 'app_article', 'label' => 'Bài viết', 'grouped' => true, 'slug' => true,
            'text' => ['title', 'sub_title', 'lead', 'content', 'image_alt', 'focus_keyword', 'seo_title', 'seo_description'],
            'copy' => ['image_link', 'image_credit', 'is_featured'],
            'business' => [],
            'parents' => ['category_id' => ['type' => 'article_category', 'required' => true]],
        ],
        'article_category' => [
            'table' => 'app_category', 'label' => 'Chuyên mục bài viết', 'grouped' => true, 'slug' => true,
            'text' => ['name', 'description', 'seo_title', 'seo_description'],
            'copy' => ['icon'],
            'business' => [],
        ],
        'faq_group' => [
            'table' => 'app_group', 'label' => 'Nhóm câu hỏi thường gặp', 'grouped' => true, 'slug' => true,
            'text' => ['name', 'description', 'seo_title', 'seo_description'],
            'copy' => ['image_link', 'ord', 'type', 'tab', 'category_key'],
            'business' => [],
        ],
        'faq' => [
            'table' => 'app_faq', 'label' => 'Câu hỏi thường gặp', 'grouped' => false, 'slug' => false,
            'text' => ['question', 'answer'],
            'copy' => ['ord'],
            'business' => [],
            'parents' => ['group_id' => ['type' => 'faq_group', 'required' => true]],
        ],
        'service' => [
            'table' => 'app_service', 'label' => 'Dịch vụ', 'grouped' => false, 'slug' => false,
            'text' => ['name', 'description'],
            'copy' => ['image_link', 'type'],
            'business' => ['price'],
            'parents' => ['group_id' => ['type' => 'group', 'required' => false]],
        ],
        'amenity' => [
            'table' => 'app_amenity', 'label' => 'Tiện ích', 'grouped' => false, 'slug' => false,
            'text' => ['name', 'description'],
            'copy' => ['icon', 'ord'],
            'business' => [],
        ],
    ];

    /** Itinerary days are two levels deep (day → detail lines): copied by TranslationFactory::copyItineraryDays. */
    public const ITINERARY_DAY_TEXT = ['title', 'description'];

    public static function has(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    public static function of(string $type): array
    {
        if (!self::has($type)) {
            throw new \InvalidArgumentException("Unknown translatable type '{$type}'.");
        }

        return self::TYPES[$type] + ['parents' => [], 'children' => []];
    }

    public static function text(string $type): array
    {
        return self::of($type)['text'];
    }

    public static function business(string $type): array
    {
        return self::of($type)['business'];
    }

    /** The type whose rows live in $table (null for a table that is not translatable). */
    public static function typeOfTable(string $table): ?string
    {
        foreach (self::TYPES as $type => $meta) {
            if ($meta['table'] === $table) {
                return $type;
            }
        }

        return null;
    }
}
