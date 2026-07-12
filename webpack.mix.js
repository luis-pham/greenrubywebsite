const mix = require('laravel-mix');
const fs = require('fs');
const path = require('path');

/**
 * Copy every file under srcRoot into destDir with a flat name (no subfolders).
 * Relative path segments are joined with "-" to avoid collisions (e.g. inter/style.css → inter-style.css).
 */
function copyFontsTreeToFlatDir(srcRoot, destDir) {
    const absSrc = path.resolve(__dirname, srcRoot);
    const absDest = path.resolve(__dirname, destDir);

    if (!fs.existsSync(absSrc)) {
        return;
    }

    fs.mkdirSync(absDest, { recursive: true });

    function walk(absDir) {
        for (const entry of fs.readdirSync(absDir, { withFileTypes: true })) {
            const abs = path.join(absDir, entry.name);
            if (entry.isDirectory()) {
                walk(abs);
                continue;
            }
            const ext = path.extname(entry.name).toLowerCase();

            // Fonts inside individual family folders (e.g. inter/inter-v11-....ttf)
            // are referenced in CSS as base filenames, so we must keep only the basename,
            // otherwise we get inter-inter-v11-... and the browser 404s.
            const FONT_EXTS = new Set(['.eot', '.ttf', '.woff', '.woff2', '.svg', '.otf']);

            let flatName;
            if (FONT_EXTS.has(ext)) {
                flatName = path.basename(abs);
            } else {
                // Keep old "flatten with family prefix" behavior for non-font assets (like style.css).
                const rel = path.relative(absSrc, abs);
                flatName = rel.split(path.sep).join('-');
            }

            fs.copyFileSync(abs, path.join(absDest, flatName));
        }
    }

    walk(absSrc);
}

mix.options({
    processCssUrls: true,
});

mix.styles([
    // ── Fonts ──────────────────────────────────────
    'public/assets/frontend/css/fonts.css',
    'public/assets/frontend/fonts/VideoJS/style.css',

    // ── Plugins (global) ──────────────────────────────────────
    'public/assets/frontend/plugins/bootstrap/css/bootstrap.min.css',
    'public/assets/frontend/plugins/owl-carousel/css/owl.carousel.min.css',
    'public/assets/frontend/plugins/owl-carousel/css/owl.theme.default.min.css',
    'public/assets/frontend/plugins/fancybox/jquery.fancybox.min.css',
    'public/assets/frontend/plugins/datetimepicker/css/tempusdominus-bootstrap-4.min.css',
    'public/assets/frontend/plugins/sweetalert2/sweetalert2.min.css',
    'public/assets/frontend/plugins/air-datepicker/air-datepicker.css',
    'public/assets/frontend/plugins/video-js/video-js.min.css',

    // ── Global CSS ────────────────────────────────────────────
    'public/assets/frontend/css/sweetalert.css',
    'public/assets/frontend/css/style.css',
    'public/assets/frontend/css/article-content.css',

    // ── Common components ─────────────────────────────────────
    'public/assets/frontend/css/common/modal-cabin-details.css',
    'public/assets/frontend/css/common/modal-info-popup.css',
    'public/assets/frontend/css/common/modal-service-detail.css',
    'public/assets/frontend/css/common/section-amenity.css',
    'public/assets/frontend/css/common/section-cabin.css',
    'public/assets/frontend/css/common/section-call-to-action.css',
    'public/assets/frontend/css/common/section-cover.css',
    'public/assets/frontend/css/common/gallery-filter.css',
    'public/assets/frontend/css/common/section-gallery.css',
    'public/assets/frontend/css/common/section-itinerary.css',
    'public/assets/frontend/css/common/section-testimonial.css',
    'public/assets/frontend/css/common/section-vr-360.css',

    // ── Modules ───────────────────────────────────────────────
    'public/assets/frontend/css/modules/about/index.css',
    'public/assets/frontend/css/modules/article/index.css',
    'public/assets/frontend/css/modules/article/show.css',
    'public/assets/frontend/css/modules/booking/index.css',
    'public/assets/frontend/css/modules/contact/index.css',
    'public/assets/frontend/css/modules/cruise/detail.css',
    'public/assets/frontend/css/modules/cruise/onboard.css',
    'public/assets/frontend/css/modules/error/index.css',
    'public/assets/frontend/css/modules/experience/index.css',
    'public/assets/frontend/css/modules/experience/show.css',
    'public/assets/frontend/css/modules/experience/section-experience.css',
    'public/assets/frontend/css/modules/faq/index.css',
    'public/assets/frontend/css/modules/gallery/index.css',
    'public/assets/frontend/css/modules/index/index.css',
    'public/assets/frontend/css/modules/itinerary/index.css',
    'public/assets/frontend/css/modules/itinerary/detail.css',
    'public/assets/frontend/css/modules/page/legal.css',
    'public/assets/frontend/css/modules/page/privacy-policy.css',
    'public/assets/frontend/css/modules/service/index.css',
], 'public/assets/frontend/dist/css/app.css');

mix.scripts([
    // ── Plugins ───────────────────────────────────────────────
    'public/assets/frontend/plugins/jquery/jquery-3.5.1.min.js',
    'public/assets/frontend/plugins/sweetalert2/sweetalert2.min.js',
    'public/assets/frontend/plugins/moment/moment.min.js',
    'public/assets/frontend/plugins/datetimepicker/locales/vi.js',
    'public/assets/frontend/plugins/datetimepicker/js/tempusdominus-bootstrap-4.min.js',
    'public/assets/frontend/plugins/bootstrap/js/bootstrap.bundle.min.js',
    'public/assets/frontend/plugins/owl-carousel/js/owl.carousel.min.js',
    'public/assets/frontend/plugins/fancybox/jquery.fancybox.min.js',
    'public/assets/frontend/plugins/air-datepicker/air-datepicker.js',
    'public/assets/frontend/plugins/video-js/video.min.js',
    'public/assets/frontend/plugins/video-js/videojs-vr.min.js',

    // ── Global ────────────────────────────────────────────────
    'public/assets/frontend/js/common/sweetalert.js',
    'public/assets/frontend/js/script.js',

    // ── Common components ─────────────────────────────────────
    'public/assets/frontend/js/common/modal-cabin-details.js',
    'public/assets/frontend/js/common/modal-service-detail.js',
    'public/assets/frontend/js/common/section-amenity.js',
    'public/assets/frontend/js/common/section-cabin.js',
    'public/assets/frontend/js/common/section-cover.js',
    'public/assets/frontend/js/common/section-gallery.js',
    'public/assets/frontend/js/common/section-itinerary.js',
    'public/assets/frontend/js/common/section-testimonial.js',
    'public/assets/frontend/js/common/section-vr-360.js',

    // ── Modules ───────────────────────────────────────────────
    'public/assets/frontend/js/modules/about/index.js',
    'public/assets/frontend/js/modules/article/index.js',
    'public/assets/frontend/js/modules/article/show.js',
    'public/assets/frontend/js/modules/booking/validation.js',
    'public/assets/frontend/js/modules/booking/cabin-price-calculator.js',
    'public/assets/frontend/js/modules/booking/index.js',
    'public/assets/frontend/js/modules/booking/cabin-price-integration.js',
    'public/assets/frontend/js/modules/cruise/detail.js',
    'public/assets/frontend/js/modules/experience/index.js',
    'public/assets/frontend/js/modules/experience/show.js',
    'public/assets/frontend/js/modules/experience/section-experience.js',
    'public/assets/frontend/js/modules/faq/index.js',
    'public/assets/frontend/js/modules/gallery/index.js',
    'public/assets/frontend/js/modules/index/index.js',
    'public/assets/frontend/js/modules/itinerary/index.js',
    'public/assets/frontend/js/modules/itinerary/detail.js',
    'public/assets/frontend/js/modules/service/index.js',
], 'public/assets/frontend/dist/js/app.js');

mix.styles([
    // ── Fonts ──────────────────────────────────────
    'public/assets/frontend/css/fonts.css',

    // ── Plugins (global) ──────────────────────────────────────
    'public/assets/frontend/plugins/bootstrap/css/bootstrap.min.css',
    'public/assets/frontend/plugins/owl-carousel/css/owl.carousel.min.css',
    'public/assets/frontend/plugins/owl-carousel/css/owl.theme.default.min.css',

    // ── Global CSS ────────────────────────────────────────────
    'public/assets/frontend/css/style.css',

    // ── Common components ─────────────────────────────────────
    'public/assets/frontend/css/common/modal-cabin-details.css',
    'public/assets/frontend/css/common/section-amenity.css',
    'public/assets/frontend/css/common/section-cabin.css',
    'public/assets/frontend/css/common/section-call-to-action.css',
    'public/assets/frontend/css/common/section-cover.css',
    'public/assets/frontend/css/common/section-itinerary.css',
    'public/assets/frontend/css/common/section-testimonial.css',

    // ── Homepage module ───────────────────────────────────────
    'public/assets/frontend/css/modules/index/index.css',
], 'public/assets/frontend/dist/css/home.css');

mix.scripts([
    // ── Plugins ───────────────────────────────────────────────
    'public/assets/frontend/plugins/jquery/jquery-3.5.1.min.js',
    'public/assets/frontend/plugins/owl-carousel/js/owl.carousel.min.js',

    // ── Global + above-the-fold homepage ──────────────────────
    'public/assets/frontend/js/script.js',
    'public/assets/frontend/js/common/section-cover.js',
    'public/assets/frontend/js/modules/index/home-loader.js',
], 'public/assets/frontend/dist/js/home-core.js');

mix.scripts([
    // ── Below-fold carousels / modals (loaded after window load) ─
    'public/assets/frontend/plugins/bootstrap/js/bootstrap.bundle.min.js',
    'public/assets/frontend/js/common/modal-cabin-details.js',
    'public/assets/frontend/js/common/section-amenity.js',
    'public/assets/frontend/js/common/section-cabin.js',
    'public/assets/frontend/js/common/section-itinerary.js',
    'public/assets/frontend/js/common/section-testimonial.js',
    'public/assets/frontend/js/modules/index/index.js',
], 'public/assets/frontend/dist/js/home-deferred.js');

mix.after(() => {
    copyFontsTreeToFlatDir(
        'public/assets/frontend/fonts',
        'public/assets/frontend/dist/css'
    );
});

mix.minify('public/assets/frontend/dist/css/app.css')
   .minify('public/assets/frontend/dist/js/app.js')
   .minify('public/assets/frontend/dist/js/home-core.js')
   .minify('public/assets/frontend/dist/js/home-deferred.js')
   .version([
       'public/assets/frontend/dist/css/app.css',
       'public/assets/frontend/dist/css/home.css',
       'public/assets/frontend/dist/js/app.js',
       'public/assets/frontend/dist/js/home-core.js',
       'public/assets/frontend/dist/js/home-deferred.js',
   ]);