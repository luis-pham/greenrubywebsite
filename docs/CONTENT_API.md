# Content API (v1)

Read-only JSON API the Next.js frontend (`greenruby-web`) uses, server to server, to render every public page
of the Green Ruby site. The Blade site in `Modules/FrontEnd` stays the reference: each endpoint returns the
data the matching Blade page shows, using the same services, ordering, pagination and 404/redirect rules.

- Base URL: `{CMS_ORIGIN}/api/v1/content`
- Routes: `routes/content_api.php`. Controllers: `app/Http/Controllers/Api/Content/*`. Presenters: `app/Services/Content/*`.
- Admin-side cache invalidation: `app/Http/Middleware/RevalidateFrontend.php` (see [Cache tags](#cache-tags)).

---

## 1. Conventions (all endpoints)

| Topic | Rule |
|---|---|
| Auth | Header `X-Content-Key: <CONTENT_API_KEY>` is required. Missing or wrong key: `401 {"error":"unauthorized"}`. Key not configured on the CMS: `503 {"error":"content_api_not_configured"}`. |
| Rate limit | Laravel `api` group: **60 requests/min per client IP**. Over the limit the CMS returns **429 with an HTML body**, not JSON. Builds that fetch many pages from one IP should go slower or cache. `POST /contact` also has its own limit of 10/min. |
| Language | Query `lang=en|vi`. Missing or unknown values fall back to `en`. Everything in a payload is in that language. |
| Keys | camelCase everywhere. One exception: `GET /site` `config` keeps the raw `ad_config` keys. |
| Missing values | `null`. Lists are always arrays and may be empty. Empty strings are returned as `null`. |
| Images / files | Root-relative paths under `/upload/...` (e.g. `/upload/2026/03/10/about.png`), served by the CMS origin. Prefix them with the CMS/asset origin. External `http(s)` URLs are passed through unchanged. |
| HTML | Fields named `*Html` hold HTML that has already been sanitized with `safe_html()` (HTMLPurifier allow-list: p, br, strong/b, em/i, u, s, sub, sup, h1–h6, ul/ol/li, blockquote, pre, code, a[href,title,target,rel], img[src,alt,title,width,height], table elements, div, span, hr). External links get `target="_blank" rel="noreferrer noopener"`. It is safe to render with `dangerouslySetInnerHTML`. |
| Plain text | Fields without the `Html` suffix are plain text: tags stripped, entities decoded, whitespace collapsed (multi-line config text keeps `\n`). Escape them like any other text. |
| Ordering | The admin's order is kept: `ord` columns, the order of page-config picks, and nested-set order for categories. |
| Prices | Price fields are `null` unless `config('frontend.showPublicPrices')` (env `FRONTEND_SHOW_PUBLIC_PRICES`) is on. That flag is **off** today. `currency` is `"USD"` for `en` and `"VND"` for `vi`. |
| Slugs | Entity slugs are `Utilities::convertToAlias(name)`, the same as the Blade URLs. Blog article slugs come from `app_article.slug`. |
| Dates | `updatedAt` / `publishDate` are ISO-8601 with the CMS timezone offset (e.g. `2026-03-13T14:45:21+07:00`). Departure dates are `YYYY-MM-DD`. Times are `HH:mm`. |
| SEO | Every page payload has `seo: {title: string|null, description: string|null}`. Hub pages use `app_page.seo_title/seo_description` and fall back to the Blade controller default. Detail pages use the entity's `seo_title/seo_description` and fall back to name and summary/lead. **Titles never include the " \| Green Ruby Cruises" suffix the Blade site appends.** The frontend adds the brand itself, unless the CMS title already contains it. Paginated listings (FAQ, blog) append ` - Page N` / ` - Trang N` when page > 1, as Blade does. |
| `noIndex` | When `true`, render `robots: noindex, follow` (Blade does the same). |
| `alternate` | On detail payloads: the exact counterpart in the other language, or `null` when there is no unambiguous match. Only emit hreflang when it is not null. |
| 404 | Unknown ids/slugs, unpublished items and pages past the end return `404 {"error":"not_found"}`. So does any unknown path under the prefix. Validation-style bad input (non-numeric ids) is a 404 too. |
| Redirect payloads | Where the Blade site answers with a 301 (an other-language category slug), the API answers **200** with a body that contains only `{"redirect": {...}}`. The frontend should issue the 301 itself to the localized URL built from those fields. |

Shared TypeScript-like types used below:

```ts
type Lang = 'en' | 'vi';
type Seo = { title: string | null; description: string | null };
type Pagination = { page: number; perPage: number; total: number; lastPage: number }; // lastPage >= 1
type GroupRef = { id: number; slug: string; name: string };

// Page-config gallery entries (banners, icon lists, galleries, partner logos …)
type GalleryItem = {
  src: string;                 // "/upload/..."
  thumbnail: string;           // thumbnail file, else = src (for videos without a poster too)
  title: string | null;        // null when the CMS title is just a file name ("img-81.png")
  description: string | null;
  alt: string | null;          // description ?? title
  type: 'image' | 'video' | '360';
};
```

---

## 2. Endpoint index

| Method & path | Page(s) on the site | Section |
|---|---|---|
| `GET /site` | layout (header/footer/config) | [3.1](#31-get-site) |
| `GET /pages/homepage` | `/` | [3.2](#32-get-pageshomepage) |
| `GET /cabins` `GET /cabins/{id}` | cabin pages | [3.3](#33-get-cabins--get-cabinsid) |
| `GET /cruises` `GET /cruises/{id}` | `/cruises/{slug}-{id}` | [3.4](#34-get-cruises--get-cruisesid) |
| `GET /itineraries` `GET /itineraries/{cruiseId}/{itineraryId}` | `/itinerary`, itinerary detail | [3.5](#35-get-itineraries--get-itinerariescruiseiditineraryid) |
| `POST /contact` | contact form | [3.6](#36-post-contact) |
| `GET /experiences` `GET /experiences/{id}` | `/experiences`, `/experiences/{slug}-{id}.html` | [4.1](#41-get-experiences) |
| `GET /services` | `/services` | [4.3](#43-get-services) |
| `GET /gallery` | `/gallery`, `/gallery/{slug}`, `/gallery/…/page-N` | [4.4](#44-get-gallery) |
| `GET /faqs` | `/faq`, `/faq/{slug}`, `/faq/…/page-N` | [4.5](#45-get-faqs) |
| `GET /articles` | `/blog`, `/blog/{category}`, `…/page-N`, `?k=` search | [4.6](#46-get-articles) |
| `GET /articles/{categorySlug}/{slug}` | `/blog/{category}/{slug}.html` | [4.7](#47-get-articlescategoryslugslug) |
| `GET /articles/legacy/{id}` | old `/blog/{slug}-{id}.html` | [4.8](#48-get-articleslegacyid) |
| `GET /legal` | `/legal`, `/safety-policies`, `/terms-and-conditions`, `/privacy-policy`, `/payment-methods` | [4.9](#49-get-legal) |
| `GET /pages/{code}` | hub/static page config (about-us, contact, booking, …) | [4.10](#410-get-pagescode) |

Sitemaps (`/sitemap*.xml`) stay on the CMS and are not part of this API.

---

## 3. Existing endpoints

### 3.1 `GET /site`

Global layout data.

```ts
{
  language: { code: Lang };
  languages: { code: string; name: string; isDefault: boolean }[];
  // Raw ad_config rows of this language, with the "-{lang}" key suffix stripped. Always an object.
  // Values are raw strings: images here are NOT /upload-prefixed (e.g. "/2026/02/06/logo.png").
  config: Record<string, string>;
  menus: { primary: MenuItem[]; footer: MenuItem[] };
}
type MenuItem = { name: string; url: string /* relative, localized */; target: string /* "_self" default */; children: MenuItem[] };
```

Sample (en, trimmed):

```json
{"language":{"code":"en"},"languages":[{"code":"en","name":"English","isDefault":true}],
 "config":{"website-name":"Green Ruby Cruises","website-slogan":"Premium Eco-Luxury Home","website-logo":"/2026/02/06/logo.png","hotline":"(+84) 386026886","email":"hello@greenrubycruises.com","google-tag-manager-id":"GTM-KXF2N9R3","block-search-engine":"true"},
 "menus":{"primary":[{"name":"Home","url":"/","target":"_self","children":[]}],"footer":[{"name":"Explore","url":"#","target":"_self","children":[{"name":"Our Cruises","url":"/#section-cruise","target":"_self","children":[]}]}]}}
```

`hiddenPages` lists the app_page codes switched off in the admin for this language (`destinations`, `guide-first-time`, `guide-transfer`; see 4.11): the frontend does not link to them.

Tags: `content`, `site`, `site:{lang}`.

### 3.2 `GET /pages/homepage`

```ts
{
  seo: Seo;
  banner: { title: string|null; description: string|null; image: string }[];
  slogan: string | null;
  cruises: { id; slug; name; summary; image; cover; capacity: number|null; length: number|null; starRating: number|null }[];
  itineraries: { itineraryId; cruiseId; slug; name; description; cruiseName; duration: number; bay: 'ha-long'|'lan-ha'|null; image; destinations: string[] }[];
  cabins: CabinSummary[];                    // accommodation cabins only
  services: { id; name; description; image }[];
  experiences: { id; slug; name; summary; image; group: string|null /* group name */; duration: string|null }[];
  sustainability: { title; description; icon }[];
  awards: { title; description; icon }[];
  faqs: { question: string; answerHtml: string|null }[];
  support: string | null;                    // e.g. "24/7"
  readyToSail: string | null;
}
```

Sample (en, trimmed):

```json
{"seo":{"title":"Eco-Friendly Cruise Ha Long & Lan Ha Bay | Green Ruby","description":"Eco-luxury cruise on Ha Long Bay's quieter Lan Ha Bay. …"},
 "banner":[{"title":"The Greenest Decision\nYou'll Ever Enjoy.","description":"Eco-certified luxury on Vietnam's most pristine waters.","image":"/upload/2026/06/08/green-ruby-cruises-halong-bay-1.jpg"}],
 "slogan":"Luxury Without Trace.",
 "cruises":[{"id":3,"slug":"green-ruby-1","name":"Green Ruby 1","summary":"46 private cabins, …","image":"/upload/2026/06/08/green-ruby-cruises-halong-bay-1.jpg","cover":"/upload/2026/03/15/green-ruby-cruise.jpg","capacity":136,"length":110,"starRating":null}],
 "itineraries":[{"itineraryId":4,"cruiseId":3,"slug":"2-days-1-night-ha-long-bay-discovery","name":"2 Days 1 Night - Ha Long Bay Discovery","description":"Ha Long Bay · UNESCO World Heritage · Departs from Tuần Châu","cruiseName":"Green Ruby 1","duration":2,"bay":"ha-long","image":"/upload/2026/03/13/3n2d-lh.png","destinations":["Ha Long bay"]}],
 "cabins":[{"id":24,"slug":"imperial-suite","name":"Imperial Suite","summary":"…","image":"/upload/2026/03/09/img-75-550x824-jpg.png","view":"Ocean view","cabinClass":"Ocean View","capacity":3,"area":120,"cruise":{"id":3,"name":"Green Ruby 1"}}],
 "services":[{"id":22,"name":"Restaurants","description":"…","image":"/upload/2026/03/10/img-creating-an-authentic-dining-experience.jpg"}],
 "experiences":[{"id":8,"slug":"kayaking-adventure","name":"Kayaking Adventure","summary":"…","image":"/upload/2026/03/10/img-75-550x824-jpg-5.png","group":"Adventure tours","duration":"32"}],
 "sustainability":[{"title":"Low Emissions","description":"…","icon":"/upload/2026/02/06/icon-low-emissions.png"}],
 "awards":[{"title":"Green Globe · In Progress","description":null,"icon":"/upload/2026/02/06/icon-award-cup.png"}],
 "faqs":[{"question":"Does the ship stop at sightseeing spots for passengers to disembark?","answerHtml":"<p>Yes, …</p>"}],
 "support":"24/7","readyToSail":"Green Ruby launches October 2026 on Ha Long & Lan Ha Bay. …"}
```

Tags: `content`, `pages`, `page:homepage`, `cruises`, `itineraries`, `cabins`, `services`, `experiences`, `faqs`.

### 3.3 `GET /cabins` · `GET /cabins/{id}`

Only "accommodation" cabins (`app_group.slug` in `config('backend.listAccommodationSlug')`). Restaurants, decks and similar spaces are cruise facilities and are not listed here.

`GET /cabins?cruiseId=` (optional filter) → `{ items: CabinSummary[] }`

```ts
type CabinSummary = {
  id: number; slug: string; name: string; summary: string|null; image: string|null;
  view: string|null; cabinClass: string|null; capacity: number|null; area: number|null;
  cruise: { id: number; name: string } | null;
};
```

`GET /cabins/{id}` → 404 if the id is not an accommodation cabin in that language.

```ts
CabinSummary & {
  contentHtml: string;                       // "" when empty
  gallery: { src: string; alt: string }[];
  overCapacity: { adult: number; child6to12: number; child2to5: number; infant: number };
  discountPercent: number|null;
  rooms: { title: string; description: string|null }[];
  amenities: { name: string; icon: string|null }[];
  suitableFor: { name: string }[];
  pricing: { showPublic: boolean; currency: 'USD'|'VND'; fromPrice: number|null; matrix: { duration: number; guest: number; price: number }[] };
  cruise: { id: number; name: string; slug: string } | null;
  otherCabins: CabinSummary[];               // same cruise, excluding this one
  seo: Seo;                                  // seo_title ?: "{name} · {cruise name}"; seo_description ?: summary
  alternate: { lang: Lang; id: number } | null;
  updatedAt: string|null;
}
```

Sample `/cabins/24` (trimmed): see the `CabinSummary` above, plus
`"overCapacity":{"adult":3,"child6to12":0,"child2to5":0,"infant":0},"rooms":[{"title":"01 queen-sized bed or 02 single beds","description":null}],"pricing":{"showPublic":false,"currency":"USD","fromPrice":null,"matrix":[]},"cruise":{"id":3,"name":"Green Ruby 1","slug":"green-ruby-1"},"alternate":null,"updatedAt":"2026-06-09T11:57:36+07:00"`.

Tags: list `content`, `cabins`; detail `content`, `cabins`, `cabin:{id}`.

### 3.4 `GET /cruises` · `GET /cruises/{id}`

`GET /cruises` → `{ items: CruiseSummary[] }` (ordered by id)

```ts
type CruiseSummary = { id; slug; name; summary: string|null; image; cover; bay: 'ha-long'|'lan-ha'|null; capacity: number|null; length: number|null };
```

`GET /cruises/{id}` (404 if not in that language):

```ts
CruiseSummary & {
  contentHtml: string; starRating: number|null; totalFloor: number|null; yearBuilt: number|null;
  descriptionDesignHtml: string;
  greenTechnology: { title: string|null; description: string|null; icon: string|null }[];
  amenities: { name: string; icon: string|null }[];
  services: { included: ServiceRef[]; optional: (ServiceRef & { price: number|null })[] };
  cabins: CabinSummary[];
  facilities: { id; name; summary; image; group: string|null }[];  // non-accommodation app_cabin rows
  experiences: { id; slug; name; summary; image; duration: string|null }[];
  itineraries: ItinerarySummary[];
  gallery: { src: string; alt: string|null }[];
  seo: Seo;
  alternate: { lang: Lang; id: number; slug: string } | null;
  updatedAt: string|null;
}
type ServiceRef = { id: number; name: string; description: string|null; image: string|null };
```

Tags: list `content`, `cruises`; detail `content`, `cruises`, `cruise:{id}`, `cabins`, `itineraries`, `services`, `experiences`.

### 3.5 `GET /itineraries` · `GET /itineraries/{cruiseId}/{itineraryId}`

`GET /itineraries`:

```ts
{
  seo: Seo;
  items: ItinerarySummary[];
  pageConfig: { faqs: { question: string; answerHtml: string|null }[]; [camelKey: string]: unknown };
}
type ItinerarySummary = {
  itineraryId: number; cruiseId: number; slug: string; name: string; description: string|null;
  cruiseName: string|null; cruiseSlug: string|null; duration: number; bay: 'ha-long'|'lan-ha'|null;
  image: string|null; destinations: string[]; startTime: string|null; fromPrice: number|null;
  currency: 'USD'|'VND'; nextDeparture: string|null /* YYYY-MM-DD */;
};
```

`GET /itineraries/{cruiseId}/{itineraryId}`: 404 only when the itinerary does not exist in the language. The payload is always built for the **canonical** cruise. If `canonical.cruiseId`, `canonical.itineraryId` or `canonical.slug` differs from the URL, 301 to the canonical URL.

```ts
ItinerarySummary & {
  canonical: { cruiseId: number; itineraryId: number; slug: string };
  cover: string|null; descriptionHtml: string; seo: Seo;
  cruise: { id; name; slug; image; capacity; length };
  days: { day: number; title: string|null; details: { time: string|null; title: string|null; descriptionHtml: string|null }[] }[];
  importantNotes: string[];
  services: { included: ServiceRef[]; optional: (ServiceRef & { price: number|null })[] };
  activities: { id; slug; name; summary; image; duration: string|null }[];
  gallery: { src: string; alt: string|null }[];
  departures: string[];          // YYYY-MM-DD, future only, max 60
  cabins: CabinSummary[];
  faqs: { question: string; answerHtml: string|null }[];
  alternate: { lang: Lang; cruiseId: number; itineraryId: number; slug: string } | null;
  updatedAt: string|null;
}
```

Tags: list `content`, `itineraries`, `page:itinerary`; detail `content`, `itineraries`, `itinerary:{itineraryId}`, `cruise:{cruiseId}`, `cabins`, `services`, `experiences`.

### 3.6 `POST /contact`

Body (JSON or form): `{ name, phone, email, message, lang? }`. The rules are the same as the Blade contact form (`ContactRequest`). Sends the same admin e-mail.

- `201 {"success":true}`
- `422 {"success":false,"errors":{"name":["…"],"message":["…"]}}` (messages in `lang`)
- `500 {"success":false,"error":"send_failed"}`
- Throttle: 10/min per IP. Not cached.

---

## 4. New endpoints

### 4.1 `GET /experiences`

Mirrors `ExperienceController@index`. Query: `lang`.

```ts
{
  seo: Seo;                                   // app_page "exp-activity"
  page: {                                     // decoded "exp-activity" page config (see 4.10)
    gallery?: GalleryItem[];                  // "Gallery" section
    sustainability?: GalleryItem[];           // not rendered by Blade today
    readyToSailDescription?: string|null;     // CTA block
    readyToSailContent?: string|null;
  };
  tabs: { key: 'onboard'|'outdoor'; name: string; groupIds: number[] }[];  // Blade filter buttons (only tabs that have groups)
  groups: ExperienceGroup[];                  // app_group type experience, admin order
  featured: ExperienceSummary[];              // is_featured, newest id first (top slider)
  items: ExperienceSummary[];                 // all experiences (main grid; filter client-side by tab.groupIds)
}
type ExperienceGroup = GroupRef & { tab: 'onboard'|'outdoor'|null };
type ExperienceSummary = {
  id: number; slug: string; name: string; summary: string|null;
  image: string|null;        // portrait card image
  cover: string|null;        // 16:9 hero / OG image
  duration: string|null;     // minutes, as stored (e.g. "90")
  startTime: string|null;    // "HH:mm"
  endTime: string|null;
  notes: string[];           // short tags, e.g. ["Small group, Morning, relaxing"]
  isFeatured: boolean;
  group: ExperienceGroup | null;
  cruise: { id: number; slug: string; name: string } | null;   // null = available on every ship
};
```

Sample (en, trimmed):

```json
{"seo":{"title":"Ha Long & Lan Ha Bay Cruise Activities | Green Ruby","description":"Kayaking, sunrise on the bay, and exclusive onboard activities aboard Green Ruby. …"},
 "page":{"gallery":[{"src":"/upload/2026/03/10/tour-du-thuyen-ha-long-11.jpg","thumbnail":"/upload/2026/03/10/tour-du-thuyen-ha-long-11.jpg","title":null,"description":null,"alt":null,"type":"image"}],
         "sustainability":[{"src":"/upload/2026/03/10/solar-panel.png","thumbnail":"/upload/2026/03/10/solar-panel.png","title":"Solar-Powered Cruising","description":"200kW solar system reduces CO₂ emissions and keeps the bay clean.","alt":"200kW solar system reduces CO₂ emissions and keeps the bay clean.","type":"image"}],
         "readyToSailDescription":"Begin Your Journey","readyToSailContent":"Your unforgettable experience on Ha Long Bay awaits"},
 "tabs":[{"key":"onboard","name":"Onboard Activities","groupIds":[37,39]},{"key":"outdoor","name":"Outdoor Activities","groupIds":[36,38]}],
 "groups":[{"id":36,"slug":"bay-exploration","name":"Bay exploration","tab":"outdoor"}],
 "featured":[{"id":14,"slug":"cooking-class","name":"Cooking Class","summary":"Learn a Vietnamese dish with our chef.","image":"/upload/2026/03/10/img-75-550x824-jpg-4.png","cover":"/upload/2026/03/13/cooking-class-4.jpg","duration":"60","startTime":"15:00","endTime":"16:00","notes":["Small group, Interactive","Hands-on"],"isFeatured":true,"group":{"id":36,"slug":"bay-exploration","name":"Bay exploration","tab":"outdoor"},"cruise":null}],
 "items":[{"id":6,"slug":"sunset-on-the-bay","name":"Sunset on the Bay","summary":"Sip a cocktail as golden light dances over Ha Long Bay's limestone peaks.","image":"/upload/2026/03/10/img-75-550x824-jpg-3.png","cover":"/upload/2026/03/13/ratio16x9-1920.jpg","duration":"90","startTime":"09:09","endTime":"21:09","notes":["Small group, Morning, relaxing"],"isFeatured":true,"group":{"id":36,"slug":"bay-exploration","name":"Bay exploration","tab":"outdoor"},"cruise":null}]}
```

Tags: `content`, `experiences`, `pages`, `page:exp-activity`.

### 4.2 `GET /experiences/{id}`

Mirrors `ExperienceController@show`. 404 when the id is not numeric or the experience does not exist in `lang`.
Site URL: `/experiences/{slug}-{id}.html` (vi: `/vi/hoat-dong-trai-nghiem/{slug}-{id}.html`). If the slug in the URL ≠ `canonicalSlug`, 301 to the canonical URL (Blade does this).

```ts
ExperienceSummary & {
  contentHtml: string|null;
  seo: Seo;                                   // seo_title ?: name ; seo_description ?: summary
  gallery: { src: string; thumbnail: string; alt: string; type: 'image'|'video'|'360' }[];
  suitableFor: { id: number; name: string }[]; // "suitable audience" groups, admin order
  related: ExperienceSummary[];               // same group, max 5
  canonicalSlug: string;
  alternate: { lang: Lang; id: number; slug: string } | null;
  updatedAt: string|null;
}
```

The Blade detail page also shows the "ready to sail" CTA. Take it from `GET /experiences` → `page.readyToSail*` (or `GET /pages/exp-activity`).

Sample `/experiences/6` (en, trimmed):

```json
{"id":6,"slug":"sunset-on-the-bay","name":"Sunset on the Bay","summary":"Sip a cocktail as golden light dances over Ha Long Bay's limestone peaks.","image":"/upload/2026/03/10/img-75-550x824-jpg-3.png","cover":"/upload/2026/03/13/ratio16x9-1920.jpg","duration":"90","startTime":"09:09","endTime":"21:09","notes":["Small group, Morning, relaxing"],"isFeatured":true,
 "group":{"id":36,"slug":"bay-exploration","name":"Bay exploration","tab":"outdoor"},"cruise":null,
 "contentHtml":"sunset on the bay",
 "seo":{"title":"Sunset on the Bay","description":"Sip a cocktail as golden light dances over Ha Long Bay's limestone peaks."},
 "gallery":[{"src":"/upload/2026/03/10/istockphoto-168626919-612x612.jpg","thumbnail":"/upload/2026/03/10/istockphoto-168626919-612x612.jpg","alt":"Sunset on the Bay","type":"image"}],
 "suitableFor":[{"id":45,"name":"Friends group"},{"id":46,"name":"Family"}],
 "related":[{"id":14,"slug":"cooking-class","name":"Cooking Class","…":"ExperienceSummary"}],
 "canonicalSlug":"sunset-on-the-bay",
 "alternate":{"lang":"vi","id":28,"slug":"ngam-hoang-hon-tren-vinh"},
 "updatedAt":"2026-03-13T14:45:21+07:00"}
```

`alternate`: experiences are separate rows per language. The Blade hreflang pairs rows with the same `duration` and the same (non-placeholder) `image_link`. The API returns the counterpart only when **exactly one** row matches.

Tags: `content`, `experiences`, `experience:{id}`.

### 4.3 `GET /services`

Mirrors `ServiceController@index`. The Blade page shows **only the services picked in the "service" page config** (`service-service`), not every row of `app_service`. Blade groups them under the service groups (tabs "All" + one per group, in group order, hiding groups with no picked service).

```ts
{
  seo: Seo;                                    // app_page "service"
  page: {                                      // decoded "service" page config minus the picked services
    cabin?: CabinSummary[];                    // service-cabin picks
    event?: GalleryItem[];                     // event types (icon + title + description)
    weddingTitle?: string|null; weddingDescription?: string|null; weddingContent?: string|null;
    weddingFeaturedImage?: string|null; weddingIcon?: GalleryItem[];
    quoteRequestTitle?: string|null; quoteRequestDescription?: string|null; quoteRequestContent?: string|null; quoteRequestHotline?: string|null;
    readyToSailDescription?: string|null; readyToSailContent?: string|null;
  };
  groups: GroupRef[];                          // all service groups, admin order
  items: ServiceItem[];                        // picked services, admin pick order
  currency: 'USD'|'VND';
}
type ServiceItem = {
  id: number; name: string; description: string|null; image: string|null;
  type: 'included' | 'optional';               // Blade badge: Complimentary / Premium
  price: number|null;                          // optional services only, and only when public prices are on
  group: GroupRef | null;
};
```

Blade only renders `items`, `groups` and the ready-to-sail CTA. The wedding, event, quote and cabin keys exist in the CMS and are passed through for the new design.

Sample (en, trimmed):

```json
{"seo":{"title":"Luxury Cruise Weddings & Events Ha Long Bay | Green Ruby","description":"Wedding cruises, corporate charters, and private events on Ha Long Bay. …"},
 "page":{"cabin":[{"id":25,"slug":"royal-romance-suite","name":"Royal Romance Suite","…":"CabinSummary"}],
         "event":[{"src":"/upload/2026/03/10/ticket-svg-2.png","thumbnail":"/upload/2026/03/10/ticket-svg-2.png","title":"Bleisure (Work & Leisure)","description":"Combine in-depth conferences with high-end leisure. …","alt":"…","type":"image"}],
         "weddingTitle":"Eternal Love Stories","weddingDescription":"Masterpiece Wedding & Engagement Ceremony","weddingContent":"Transform your big day into a legend …","weddingFeaturedImage":"/upload/2026/03/10/mask-group-3.png",
         "weddingIcon":[{"src":"/upload/2026/03/09/mosque.png","thumbnail":"/upload/2026/03/09/mosque.png","title":"Supperroom Wedding Hall","description":"Exchange vows under the glorious sunset and refreshing sea breeze.","alt":"…","type":"image"}],
         "quoteRequestTitle":"Get a Proposal","quoteRequestDescription":"Request a Quote &  Consultation","quoteRequestContent":"Whether it's a business conference …","quoteRequestHotline":"1900 8888 (Extension 2)",
         "readyToSailDescription":"Ready for an Unforgettable Trip?","readyToSailContent":"Book today to receive special offers …"},
 "groups":[{"id":40,"slug":"dining","name":"Dining"},{"id":41,"slug":"transportation","name":"Transportation"},{"id":42,"slug":"entertainment","name":"Entertainment"},{"id":43,"slug":"health","name":"Health"},{"id":64,"slug":"other","name":"Other"}],
 "items":[{"id":10,"name":"Drone Photography & Film","description":"…","image":"/upload/2026/03/12/giay-phep-bay-flycam-0909121730.jpg","type":"optional","price":null,"group":{"id":42,"slug":"entertainment","name":"Entertainment"}}],
 "currency":"USD"}
```

Tags: `content`, `services`, `cabins`, `pages`, `page:service`.

### 4.4 `GET /gallery`

Mirrors `GalleryController@index` + `GalleryService`. Query: `lang`, `category` (optional slug), `page` (default 1).

- Categories are fixed page-config keys with fixed slugs per language:

  | config key | en slug | vi slug |
  |---|---|---|
  | `gallery-file` | `onboard-life` | `cuoc-song-tren-tau` |
  | `gallery-file-cabins-suites` | `cabins-suites` | `cabin-suite` |
  | `gallery-file-activities` | `activities` | `hoat-dong` |
  | `gallery-file-nature-bay-views` | `nature-bay-views` | `thien-nhien-va-vinh` |
  | `gallery-file-guests-moments` | `guests-moments` | `khoang-khac-du-khach` |
- Every listing starts with the VR/360 items (`gallery-file-vr-360`), followed by the category's items (or all categories when there is no `category`). Blade renders `type:'360'` items in a separate "VR 360" block and the others in the grid. Pagination counts both (20 per page).
- `category` may be either language's slug. `canonicalCategorySlug` is the slug in `lang`; if it differs from the requested one, 301 to it (Blade does).
- 404: unknown `category`, or `page` > `lastPage` when there are items.

```ts
{
  seo: Seo;                                     // app_page "gallery"
  categories: { slug: string; name: string; count: number /* own items, VR excluded */; seo: Seo /* "SEO danh mục" section, null = frontend default */ }[];  // config order
  canonicalCategorySlug: string | null;         // null = "All"
  items: { src: string; thumbnail: string; title: string|null; alt: string|null; type: 'image'|'video'|'360' }[];
  pagination: Pagination;
  featuredExperiences: ExperienceSummary[];     // "memories" strip at the bottom
}
```

Sample `/gallery?category=activities` (en, trimmed):

```json
{"seo":{"title":"Ha Long Bay Cruise Photo Gallery | Green Ruby","description":"See Green Ruby's eco-luxury cabins, dining, and Lan Ha Bay views. …"},
 "categories":[{"slug":"onboard-life","name":"Onboard life","count":8},{"slug":"cabins-suites","name":"Cabins Suites","count":3},{"slug":"activities","name":"Activities","count":3},{"slug":"nature-bay-views","name":"Nature Bay Views","count":2},{"slug":"guests-moments","name":"Guests Moments","count":1}],
 "canonicalCategorySlug":"activities",
 "items":[{"src":"/upload/2026/03/10/eagle-360-1.mp4","thumbnail":"/upload/2026/03/10/eagle-360-1.jpg","title":null,"alt":null,"type":"360"},{"src":"/upload/2026/03/10/perform-tai-chi-in-morning-a-perfect-ti….jpg","thumbnail":"…","title":null,"alt":null,"type":"image"}],
 "pagination":{"page":1,"perPage":20,"total":4,"lastPage":1},
 "featuredExperiences":[{"id":14,"slug":"cooking-class","…":"ExperienceSummary"}]}
```

Tags: `content`, `gallery`, `experiences`, `pages`, `page:gallery`.

### 4.5 `GET /faqs`

Mirrors `FaqController@index` / `@category`. Query: `lang`, `group` (optional group slug), `page` (default 1), `k` (optional keyword, matched against question and answer).

- Order: `app_faq.ord`. 20 per page (`config('frontend.paginationLimit')`).
- 404: unknown `group`, or `page` > `lastPage` when there are results.
- Redirect: if `group` is a slug of the **other** language and has a counterpart (same id, else same `ord`), the response is
  `200 {"redirect":{"group":"<localized slug>","page":number|null}}`. 301 to `/faq/{group}` (or `/faq/{group}/page-{page}` when `page` is not null), vi `/vi/cau-hoi-thuong-gap/{group}[/trang-{page}]`.

```ts
{
  seo: Seo;                       // group: seo_title ?: name; hub: app_page "faq" seo_title ?: "FAQ"/"Câu hỏi thường gặp" (+ " - Page N"); description: group / app_page "faq" seo_description, else null (frontend default)
  group: GroupRef | null;         // current group (null = all)
  groups: (GroupRef & { count: number })[];  // filter tabs, admin order (count ignores k)
  items: FaqItem[];
  pagination: Pagination;
  keyword: string | null;
}
type FaqItem = {
  id: number;
  question: string;               // plain text (CMS stores the question as HTML)
  questionHtml: string|null;      // what Blade renders
  answerHtml: string|null;
  group: GroupRef | null;         // Blade shows the group label above each question
};
```

Sample (en, trimmed):

```json
{"seo":{"title":"FAQ","description":"Eco-luxury cruises on Ha Long Bay and the quieter Lan Ha Bay, Vietnam. …"},"group":null,
 "groups":[{"id":57,"slug":"itineraries-and-destinations","name":"Itineraries and Destinations","count":1},{"id":56,"slug":"bookings-payments","name":"Bookings & Payments","count":3},{"id":58,"slug":"life-on-board","name":"Life on Board","count":2},{"id":59,"slug":"green-technology","name":"Green Technology","count":0}],
 "items":[{"id":5,"question":"What payment methods does Green Ruby support?","questionHtml":"<p>What payment methods does Green Ruby support?</p>","answerHtml":"<p>Green Ruby supports a variety of payment methods …</p>","group":{"id":56,"slug":"bookings-payments","name":"Bookings & Payments"}}],
 "pagination":{"page":1,"perPage":20,"total":6,"lastPage":1},"keyword":null}
```

Redirect sample: `/faqs?group=dat-cho-va-thanh-toan` (en) → `{"redirect":{"group":"bookings-payments","page":null}}`.

Use the items to build the FAQPage JSON-LD, as Blade does.

Tags: `content`, `faqs`.

### 4.6 `GET /articles`

Mirrors `ArticleController@index` / `@category`. Query: `lang`, `category` (optional slug), `page` (default 1), `k` (optional keyword, matched against title, sub-title, lead and content).

- Only published articles, newest `publish_date` first, 10 per page (`config('frontend.paginationArticleLimit')`). A category listing includes its sub-categories.
- 404: unknown `category` (or `root`), or `page` > `lastPage` when there are results.
- Redirect: if `category` is a slug of the **other** language with a counterpart (same id, else the same tree position `lvl` + `lft`), the response is `200 {"redirect":{"category":"<localized slug>","page":number|null}}`. 301 to `/blog/{category}[/page-{page}]` (vi `/vi/blog/{category}[/trang-{page}]`).
- `noIndex` is `true` for a category with zero articles (Blade: crawlable but noindex).

```ts
{
  seo: Seo;              // no category: app_page "article" (fallback "Blog"); category: seo_title ?: name, seo_description ?: website-description. + " - Page N"
  noIndex: boolean;
  category: CategoryDetail | null;
  categories: CategoryNode[];          // full non-root tree, nested-set order
  featured: ArticleSummary[];          // is_featured (within category incl. children when filtered), not paginated
  items: ArticleSummary[];             // current page
  popular: ArticleSummary[];           // "article-popular" page-config picks (sidebar)
  pagination: Pagination;
  keyword: string | null;
}
type ArticleSummary = {
  id: number; slug: string; title: string; subTitle: string|null;
  lead: string|null;                   // plain text
  image: string|null;
  publishDate: string|null;            // ISO
  isFeatured: boolean;
  category: { id: number; slug: string; name: string } | null;
};
type CategoryNode = { id: number; slug: string; name: string; count: number /* published, incl. children */; children: CategoryNode[] };
type CategoryDetail = {
  id: number; slug: string; name: string; description: string|null;
  parent: { id: number; slug: string; name: string } | null;   // null when the parent is the root
  breadcrumb: { slug: string; name: string }[];                // top-level ancestor … this category
};
```

Article URL: `/blog/{category.slug}/{slug}.html` (vi `/vi/blog/{category.slug}/{slug}.html`).
Blade's category filter bar = `category ? (children of category, else siblings) : top-level categories`, with an "All" tab. You can derive it from `categories` + `category.parent`.
There is no author field. Blade's JSON-LD uses the Organization as `author`.

Sample (en, trimmed). The local CMS has **no articles**, so this shows the real empty state:

```json
{"seo":{"title":"Ha Long Bay Cruise Guides & Travel Tips | Green Ruby Blog","description":"Expert guides on eco cruising, Lan Ha vs Ha Long Bay, best time to visit, and sustainable travel in Vietnam."},
 "noIndex":false,"category":null,
 "categories":[{"id":573,"slug":"green-tourism","name":"Green Tourism","count":0,"children":[]},{"id":571,"slug":"journey","name":"Journey","count":0,"children":[]},{"id":574,"slug":"experiences","name":"Experiences","count":0,"children":[]},{"id":572,"slug":"events","name":"Events","count":0,"children":[]}],
 "featured":[],"items":[],"popular":[],"pagination":{"page":1,"perPage":10,"total":0,"lastPage":1},"keyword":null}
```

Example item (verified against test rows inserted inside a rolled-back transaction):

```json
{"id":221,"slug":"lan-ha-vs-ha-long-bay","title":"Lan Ha vs Ha Long Bay","subTitle":"Which bay?","lead":"Short lead & more","image":"/upload/2026/03/10/unnamed.jpg","publishDate":"2026-09-01T08:00:00+07:00","isFeatured":true,"category":{"id":571,"slug":"journey","name":"Journey"}}
```

Redirect sample: `/articles?category=hanh-trinh` (en) → `{"redirect":{"category":"journey","page":null}}`.

Tags: `content`, `articles`, `pages`, `page:article`.

### 4.7 `GET /articles/{categorySlug}/{slug}`

Mirrors `ArticleController@show`. 404 when the pair does not match a **published** article in `lang` (`AppArticleService::findByCategoryAndSlug`). If `canonical` differs from the URL (e.g. letter case), 301 to the canonical URL.

```ts
ArticleSummary & {
  category: CategoryDetail | ArticleSummary['category'];   // full detail when the category exists
  leadHtml: string|null;              // Blade renders the lead as HTML above the content
  contentHtml: string|null;           // FeUtils::bindArticleContent + safe_html (see below)
  seo: Seo;                           // seo_title ?: title ; seo_description ?: lead
  related: ArticleSummary[];          // same category (not children), newest first, max 5
  popular: ArticleSummary[];          // sidebar
  canonical: { categorySlug: string; slug: string };
  alternate: { lang: Lang; id: number; categorySlug: string; slug: string } | null;
  updatedAt: string|null;
}
```

`contentHtml` goes through the Blade pipeline. SEO keywords (`app_seo_keyword`) become links, but only the first occurrence and only inside `<p>`/`<blockquote>`. Every `<img>` is wrapped in `<a href="{img src}">`, which the site uses for a lightbox. CMS-relative URLs stay root-relative (`/upload/...`, `/en/...`).
`alternate`: Blade hreflang only pairs the same article id in the other language. Ids are unique across languages, so in practice this is always `null`.

Sample (trimmed; test data inside a rolled-back transaction, since the local DB has no articles):

```json
{"id":221,"slug":"lan-ha-vs-ha-long-bay","title":"Lan Ha vs Ha Long Bay","subTitle":"Which bay?","lead":"Short lead & more","image":"/upload/2026/03/10/unnamed.jpg","publishDate":"2026-09-01T08:00:00+07:00","isFeatured":true,
 "category":{"id":571,"slug":"journey","name":"Journey","description":null,"parent":null,"breadcrumb":[{"slug":"journey","name":"Journey"}]},
 "leadHtml":"<p>Short <b>lead</b> &amp; more</p>",
 "contentHtml":"<p>Read about the eco cruise on <a href=\"https://example.com/x\" target=\"_blank\" rel=\"noreferrer noopener\">example</a> and <a href=\"/en/itinerary\">itineraries</a>.</p><p><a href=\"/upload/2026/03/10/unnamed.jpg\"><img src=\"/upload/2026/03/10/unnamed.jpg\" alt=\"Bay\" /></a></p>",
 "seo":{"title":"Lan Ha vs Ha Long Bay","description":"Short lead & more"},
 "related":[{"id":222,"slug":"second-post","…":"ArticleSummary"}],"popular":[],
 "canonical":{"categorySlug":"journey","slug":"lan-ha-vs-ha-long-bay"},"alternate":null,"updatedAt":"2026-10-14T19:33:21+07:00"}
```

Tags: `content`, `articles`, `article:{id}`.

### 4.8 `GET /articles/legacy/{id}`

For the old URLs `/blog/{anything}-{id}.html` (`ArticleController@redirectLegacyShow`). The slug part of the old URL is ignored, as in Blade. 404 if the id is not a published article in `lang`.

```ts
{ id: number; categorySlug: string|null; slug: string|null }
// categorySlug null => the article has no category; Blade redirects to the blog index (/blog).
```

Sample: `{"id":221,"categorySlug":"journey","slug":"lan-ha-vs-ha-long-bay"}`. Answer with a 301 to `/blog/journey/lan-ha-vs-ha-long-bay.html`.
`legacy` is reserved: a blog category with the slug `legacy` would collide with this route.

Tags: `content`, `articles`.

### 4.9 `GET /legal`

Everything the five legal URLs render (`PageController`). All five are `noindex, follow` and show the "popular articles" sidebar.

| Site URL (en / vi) | Data |
|---|---|
| `/legal` / `/vi/phap-ly` | `title`, `seo`, `sections` (accordion; Blade skips sections whose `html` is null) |
| `/safety-policies` / `/vi/chinh-sach-an-toan` | `pages.safetyPolicies` |
| `/terms-and-conditions` / `/vi/dieu-khoan-dieu-kien` | `pages.termsAndConditions` |
| `/privacy-policy` / `/vi/chinh-sach-bao-mat` | `pages.privacyPolicy` (see note) |
| `/payment-methods` / `/vi/phuong-thuc-thanh-toan` | `pages.paymentMethods` |

```ts
{
  seo: Seo;                    // app_page "legal" (fallback "Legal"/"Pháp lý")
  title: string;
  noIndex: true;
  sections: {                  // from the "legal" page config (legal-*-content)
    safety: LegalSection; terms: LegalSection; privacy: LegalSection; paymentMethods: LegalSection;
  };
  pages: {                     // each standalone page uses its own page code's "*-content" key
    safetyPolicies: LegalPage; termsAndConditions: LegalPage; privacyPolicy: LegalPage; paymentMethods: LegalPage;
  };
  popularArticles: ArticleSummary[];
  updatedAt: string|null;      // latest legal-*-content change
}
type LegalSection = { title: string; html: string|null };
type LegalPage = { code: string; title: string; seo: Seo; html: string|null; updatedAt: string|null };
```

Notes:
- The Blade `/privacy-policy` page is a **hard-coded English template** with a fixed title ("Privacy Policy") and description. It does not read the CMS. `pages.privacyPolicy.html` exposes the CMS text (`privacy-policies-content`) so the new site can be CMS-driven.
- In **vi**, the four standalone `*-content` values are empty in the CMS (`html: null`), while `sections.*.html` has content. Falling back to the matching section is reasonable.

Sample (en, trimmed):

```json
{"seo":{"title":"Legal","description":"Eco-luxury cruises on Ha Long Bay and the quieter Lan Ha Bay, Vietnam. …"},"title":"Legal","noIndex":true,
 "sections":{"safety":{"title":"Safety Policies","html":"<p>The safety of our customers is our top priority. …</p>"},"terms":{"title":"Terms and Conditions","html":"<p>By booking services through our website or system, …</p>"},"privacy":{"title":"Privacy Policies","html":"<p>Green Ruby Cruises respects your privacy. …</p>"},"paymentMethods":{"title":"Payment Methods","html":"<p>We support multiple payment methods …</p>"}},
 "pages":{"safetyPolicies":{"code":"safety-policies","title":"Safety Policies","seo":{"title":"Safety Policies","description":"Eco-luxury cruises …"},"html":"<p>The safety of our customers …</p>","updatedAt":"2026-03-10T08:56:58+07:00"},
          "privacyPolicy":{"code":"privacy-policies","title":"Privacy Policy","seo":{"title":"Privacy Policy","description":"How Green Ruby Cruises collects, uses, and protects your personal information. GDPR compliant."},"html":"<p>We are committed to protecting the personal information …</p>","updatedAt":"2026-03-10T08:58:03+07:00"},
          "termsAndConditions":{"…":"LegalPage"},"paymentMethods":{"…":"LegalPage"}},
 "popularArticles":[],"updatedAt":"2026-06-09T17:44:22+07:00"}
```

Tags: `content`, `legal`, `articles`, `pages`, `page:legal`.

### 4.10 `GET /pages/{code}`

The generic page-config reader for hub and static pages. `homepage` keeps its dedicated shape (3.2). Allowed codes:
`about-us`, `gallery`, `faq`, `article`, `exp-activity`, `service`, `contact`, `booking`, `legal`, `itinerary`, `safety-policies`, `terms-and-conditions`, `privacy-policies`, `payment-methods`. Any other code returns 404.

```ts
{
  code: string;
  seo: Seo;                      // app_page seo (fallbacks as on the Blade page)
  noIndex: boolean;              // true for booking, legal, safety-policies, terms-and-conditions, privacy-policies, payment-methods
  config: Record<string, unknown>;   // always an object (may be {})
  heroImages?: { src: string; alt: string|null }[];   // about-us & booking only: images of scheduled itineraries (Blade hero/story)
  updatedAt: string|null;
}
```

How `config` is decoded (`PageConfigContentService::decode`, from the same rows as `FeUtils::getPageConfigByCode`):

| CMS input type | Key | Value |
|---|---|---|
| textbox / textarea / selectbox | `camel(key minus "{code}-")`, e.g. `about-us-eco-title` → `ecoTitle` | `string|null` (plain; textarea keeps `\n`) |
| text editor | same + `Html` suffix, e.g. `legal-safety-policies-content` → `safetyPoliciesContentHtml` | sanitized HTML `string|null` |
| image | same | `"/upload/..."` or `null` |
| gallery | same | `GalleryItem[]` |
| sourceData picker | same | hydrated list in admin order: article → `ArticleSummary`, faq → `FaqItem`, cruise → `CruiseSummary`, cabin → `CabinSummary`, service → `ServiceItem`, exp-activity → `ExperienceSummary`, cruise-itinerary → `ItinerarySummary` (no price/next departure), amenity → `{id,name,description,icon}` |

Keys present today:

| code | config keys |
|---|---|
| `about-us` | `banner` GalleryItem[] (hero; `title`/`description` = heading/sub-heading), `ecoTitle`, `ecoDescription`, `ecoContent`, `ecoFeaturedImage`, `sustainability` GalleryItem[] (eco cards), `enviromentTitle`, `enviromentDescription`, `enviromentList` GalleryItem[] (community actions; "enviroment" is the CMS spelling), `statisticTitle`, `statisticDescription`, `statisticWastewaterTreated` (Blade appends "%"), `statisticReducedAnnually` ("+"), `statisticRenewableSolarPower` ("kW"), `partnerTitle`, `partnerDescription`, `partnerList` GalleryItem[] (logos), `readyToSailDescription`, `readyToSailContent` |
| `exp-activity` | `gallery`, `sustainability`, `readyToSailDescription`, `readyToSailContent` |
| `service` | `cabin`, `event`, `wedding*`, `quoteRequest*`, `readyToSail*`, `service` (ServiceItem[]) |
| `gallery` | `file`, `fileCabinsSuites`, `fileActivities`, `fileNatureBayViews`, `fileGuestsMoments`, `fileVr360` (GalleryItem[]); prefer `GET /gallery` |
| `article` | `popular` (ArticleSummary[]) |
| `legal` | `safetyPoliciesContentHtml`, `termsAndConditionsContentHtml`, `privacyPoliciesContentHtml`, `paymentMethodsContentHtml` |
| `safety-policies` / `terms-and-conditions` / `privacy-policies` / `payment-methods` | `contentHtml` |
| `contact`, `booking`, `itinerary`, `faq` | none today (`{}`); use them for `seo`. `faq` has no `app_page` row, so its SEO is the fallback "FAQ". |

About page (Blade): when `ecoFeaturedImage` is null, the CTA image falls back to `banner[0].src`. When all three statistics are null, the stats row is hidden.

Sample `/pages/about-us` (en, trimmed):

```json
{"code":"about-us","seo":{"title":"About Green Ruby | Eco-Luxury Cruises on Ha Long Bay","description":"Vietnam's eco-luxury cruise line on Ha Long & Lan Ha Bay. …"},"noIndex":false,
 "config":{"banner":[{"src":"/upload/2026/03/10/about.png","thumbnail":"/upload/2026/03/10/about.png","title":"About Green Ruby Cruises","description":"Where luxury meets sustainability — sailing in harmony with nature","alt":"Where luxury meets sustainability — sailing in harmony with nature","type":"image"}],
   "ecoTitle":"Green Ruby Cruises","ecoDescription":"Sailing Towards a Greener Tomorrow","ecoContent":"Born from the belief that true luxury respects nature, …","ecoFeaturedImage":"/upload/2026/03/10/img-81-600x901-jpg-9.png",
   "sustainability":[{"src":"/upload/2026/03/10/img-81-600x901-jpg-10.png","thumbnail":"…","title":"Solar Power System","description":"Our 200 kW solar panel array …","alt":"…","type":"image"}],
   "enviromentTitle":"Actions that Make a Difference","enviromentDescription":"Our commitment extends beyond our ships\nto the communities and environment we cherish",
   "enviromentList":[{"src":"/upload/2026/03/10/img-81-600x901-jpg-14.png","thumbnail":"…","title":"Partnering with Local Communities","description":"We source over 80% of our provisions …","alt":"…","type":"image"}],
   "statisticTitle":"Our Positive Impact","statisticDescription":"Measurable results from our sustainability initiatives","statisticWastewaterTreated":null,"statisticReducedAnnually":null,"statisticRenewableSolarPower":null,
   "partnerTitle":"Recognition & Partnerships","partnerDescription":"Working toward recognised sustainability standards …",
   "partnerList":[{"src":"/upload/2026/03/10/logo-vidio-1.png","thumbnail":"/upload/2026/03/10/logo-vidio-1.png","title":null,"description":null,"alt":null,"type":"image"}],
   "readyToSailDescription":"Join Us in Sailing Sustainably","readyToSailContent":"Explore our itineraries and be part of the change. …"},
 "heroImages":[{"src":"/upload/2026/03/13/2n3d-hl-1.png","alt":"3 Days 2 Night — Ha Long Bay Discovery"}],
 "updatedAt":"2026-06-16T16:46:20+07:00"}
```

Sample `/pages/contact` (en): `{"code":"contact","seo":{"title":"Contact Green Ruby Cruises | Ha Long Bay","description":"Get in touch with Green Ruby for bookings, private charters, and event enquiries on Ha Long & Lan Ha Bay."},"noIndex":false,"config":{},"updatedAt":"2026-06-16T16:46:20+07:00"}`.
Contact details (hotline, e-mail, address, WhatsApp, socials) come from `GET /site` → `config`.

Tags: `content`, `pages`, `page:{code}`, plus the entity tags of hydrated pickers: about-us/booking `itineraries`; service `services`, `cabins`; exp-activity `experiences`; article `articles`; gallery `gallery`; legal codes `legal`.

### 4.11 `GET /destinations` and `GET /destinations/{slug}`

Destinations of the Next.js `/destinations` hub (admin: Quản lý du thuyền → Điểm đến; table `app_destination`, one row per
language, EN/VI pairs in `content_translations` type `destination`). Only `status = published` rows are returned; `editor_notes`
and the editorial status never are. Coordinates are sent only when an editor ticked "verified".

```ts
type DestinationSummary = {
  id: number; slug: string; name: string;
  bay: 'ha-long' | 'lan-ha';
  category: 'cave' | 'island' | 'village' | 'lagoon' | 'other';
  summary: string|null;
  image: { src: string; alt: string; credit: string|null } | null;
  coordinates: { lat: number; lng: number } | null;   // verified only
  isGreenRubyStop: boolean;                            // linked to at least one listed itinerary
  itineraryIds: number[];                              // those itineraries (same language)
};
// GET /destinations
{ seo: Seo /* app_page "destinations", null when empty */; items: DestinationSummary[] /* admin order */ }
// GET /destinations/{slug}  (404 for unknown or unpublished slugs; slugs are per language)
DestinationSummary & {
  contentHtml: string|null;
  facts: { label: string; value: string }[];
  faq: { question: string; answer: string }[];      // plain text
  sources: { title: string; url: string }[];
  itineraries: { cruiseId; itineraryId; slug; name; cruiseName; duration; bay }[];
  related: DestinationSummary[];                     // 3: same bay first, then same category
  seo: Seo; alternate: { lang; slug } | null;        // explicit translation link to a published row only
  factCheckedAt: string|null; updatedAt: string|null;
}
```

Tags: `content`, `destinations`, `destination:{slug}`, `page:destinations`. Import/refresh the first eight with
`php artisan destinations:import [--update] [--status=review]` (data: `database/data/destinations-initial.php`).
`GET /pages/{code}` also accepts `destinations`, `guide-first-time` and `guide-transfer` (SEO only; null when empty).

**Show/hide switch** (`app_page.is_visible`, admin: "Hiển thị trên website" on the page's SEO card, per language): for these three codes `GET /pages/{code}` returns `visible`, and `GET /destinations` returns `visible` plus no items (so every detail 404s) while the hub is off. Hidden pages are dropped from the sitemaps and `hiddenPages` in `GET /site`; saving the page revalidates `site` (and `destinations`).

---

## 5. Cache tags

The frontend tags each cached fetch with the tags listed under each endpoint. After an admin save, `RevalidateFrontend` POSTs `{secret, tags}` to `FRONTEND_REVALIDATE_URL`.

| Admin route group (`backend.<x>.*`, writes only) | Tags revalidated |
|---|---|
| `amenity` | `cabins`, `cruises` |
| `article` | `articles`, `legal`, `article:{id}` |
| `cabin` | `cabins`, `cruises`, `cabin:{id}` |
| `category` (blog) | `articles` |
| `config` (site settings) | `site` |
| `cruise` | `cruises`, `cabins`, `itineraries`, `cruise:{id}` |
| `destination` | `destinations` |
| `exp-activity` | `experiences`, `itineraries`, `cruises`, `gallery`, `experience:{id}` |
| `faq` | `faqs` |
| `file` (media library) | `content` (everything) |
| `group` | `cabins`, `experiences`, `services`, `faqs` |
| `itinerary` | `itineraries`, `cruises`, `destinations`, `itinerary:{id}` |
| `menuFrontEnd` | `site` |
| `page-config` (`{pageCode}`) | `pages`, `page:{pageCode}`, plus: article → `articles`, `legal`; exp-activity → `experiences`; faq → `faqs`; gallery → `gallery`; itinerary → `itineraries`; service → `services`; legal / safety-policies / terms-and-conditions / privacy-policies / payment-methods → `legal` |
| `service` | `services`, `itineraries`, `cruises` |
| `testimonial` | `testimonials` |

Tags to use per endpoint (always include `content`):

| Endpoint | Tags |
|---|---|
| `/site` | `site`, `site:{lang}` |
| `/pages/homepage` | `pages`, `page:homepage`, `cruises`, `itineraries`, `cabins`, `services`, `experiences`, `faqs` |
| `/cabins`, `/cabins/{id}` | `cabins` (+ `cabin:{id}`) |
| `/cruises`, `/cruises/{id}` | `cruises` (+ `cruise:{id}`, `cabins`, `itineraries`, `services`, `experiences`) |
| `/itineraries`, `/itineraries/{c}/{i}` | `itineraries`, `page:itinerary` / `itinerary:{i}`, `cruise:{c}`, `cabins`, `services`, `experiences` |
| `/experiences` | `experiences`, `pages`, `page:exp-activity` |
| `/experiences/{id}` | `experiences`, `experience:{id}` |
| `/services` | `services`, `cabins`, `pages`, `page:service` |
| `/gallery` | `gallery`, `experiences`, `pages`, `page:gallery` |
| `/faqs` | `faqs` |
| `/articles` | `articles`, `pages`, `page:article` |
| `/articles/{c}/{s}` | `articles`, `article:{id}` |
| `/articles/legacy/{id}` | `articles` |
| `/legal` | `legal`, `articles`, `pages`, `page:legal` |
| `/pages/{code}` | `pages`, `page:{code}` + the entity tags listed in 4.10 |

Not covered: `app_seo_keyword` (no admin route; affects article `contentHtml`) and `app_page` SEO fields edited outside page-config. Those changes show up after the frontend's time-based revalidation.
