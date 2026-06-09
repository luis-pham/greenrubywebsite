@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    <div id="article" class="page-privacy-policy">
        <section class="privacy-hero position-relative">
            <svg class="privacy-hero-topo" viewBox="0 0 1440 240" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="50" rx="300" ry="170" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="50" rx="225" ry="120" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="260" ry="150" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="185" ry="105" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="privacy-hero-inner">
                <div class="privacy-hero-eyebrow">
                    <span class="privacy-eyebrow-line"></span>
                    Legal
                    <span class="privacy-eyebrow-line"></span>
                </div>
                <h1 class="privacy-hero-title font-heading">Privacy <em>Policy.</em></h1>
                <p class="privacy-hero-sub mb-0">How Green Ruby Cruises collects, uses, and protects your personal information.</p>
            </div>
        </section>

        <div class="privacy-jumpnav-wrap">
            <div class="container-fluid px-0">
                <div class="privacy-jumpnav-inner">
                    <div class="container">
                        <nav class="privacy-jumpnav list-filter" aria-label="Privacy policy sections">
                            <a href="#collect" class="privacy-jtab item active">Data We Collect</a>
                            <a href="#use" class="privacy-jtab item">How We Use It</a>
                            <a href="#rights" class="privacy-jtab item">Your Rights</a>
                            <a href="#sharing" class="privacy-jtab item">Data Sharing</a>
                            <a href="#cookies" class="privacy-jtab item">Cookies</a>
                            <a href="#retention" class="privacy-jtab item">Retention</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="privacy-main">
            <div class="privacy-meta-bar">
                <div class="privacy-meta-chip">Last updated: January 2026</div>
                <div class="privacy-meta-dot" aria-hidden="true"></div>
                <div class="privacy-meta-chip">Applies to: greenrubycruises.com</div>
                <div class="privacy-meta-dot" aria-hidden="true"></div>
                <div class="privacy-meta-chip">GDPR · Vietnam Law on Cybersecurity</div>
            </div>

            <div class="privacy-intro-box">
                <p>Green Ruby Cruises ("we", "our", "us") is committed to protecting your privacy. This policy explains what personal data we collect, why we collect it, and your rights regarding that data. By using our website or booking a cruise, you agree to the terms of this policy.</p>
            </div>

            <div class="privacy-section" id="collect">
                <p class="privacy-sec-num">01</p>
                <h2 class="privacy-sec-title">Data We Collect</h2>
                <p class="privacy-sec-body">We collect personal information you provide directly to us, as well as data collected automatically when you use our website.</p>

                <h3 class="privacy-sec-sub">Information You Provide</h3>
                <ul class="privacy-sec-list">
                    <li>Full name and contact details (email, phone number)</li>
                    <li>Passport or identification details (for booking purposes)</li>
                    <li>Dietary requirements or health information (if voluntarily provided)</li>
                    <li>Payment information (processed securely via third-party gateways)</li>
                    <li>Messages and enquiries sent via our contact form</li>
                </ul>

                <h3 class="privacy-sec-sub">Information Collected Automatically</h3>
                <ul class="privacy-sec-list">
                    <li>IP address and browser type</li>
                    <li>Pages visited and time spent on our website</li>
                    <li>Referral source (how you found us)</li>
                    <li>Device type and operating system</li>
                </ul>
            </div>

            <div class="privacy-section" id="use">
                <p class="privacy-sec-num">02</p>
                <h2 class="privacy-sec-title">How We Use Your Data</h2>
                <p class="privacy-sec-body">We use your personal data only for the purposes for which it was collected.</p>

                <h3 class="privacy-sec-sub">Primary Uses</h3>
                <ul class="privacy-sec-list">
                    <li>Processing and confirming cruise bookings</li>
                    <li>Communicating with you about your reservation</li>
                    <li>Providing customer support before, during, and after your voyage</li>
                    <li>Complying with legal and regulatory requirements</li>
                </ul>

                <h3 class="privacy-sec-sub">Secondary Uses (with your consent)</h3>
                <ul class="privacy-sec-list">
                    <li>Sending promotional offers and newsletters (opt-in only)</li>
                    <li>Improving our website and service quality</li>
                    <li>Conducting satisfaction surveys</li>
                </ul>

                <div class="privacy-highlight-box">
                    <p><strong>We do not sell your personal data.</strong> Your information is never sold, rented, or traded to third parties for marketing purposes.</p>
                </div>
            </div>

            <div class="privacy-section" id="rights">
                <p class="privacy-sec-num">03</p>
                <h2 class="privacy-sec-title">Your Rights</h2>
                <p class="privacy-sec-body">Under GDPR and applicable Vietnamese law, you have the following rights regarding your personal data.</p>
                <ul class="privacy-sec-list">
                    <li><strong>Right of access</strong> — Request a copy of the personal data we hold about you</li>
                    <li><strong>Right to rectification</strong> — Request correction of inaccurate data</li>
                    <li><strong>Right to erasure</strong> — Request deletion of your data, subject to legal obligations</li>
                    <li><strong>Right to object</strong> — Object to processing of your data for marketing purposes</li>
                    <li><strong>Right to portability</strong> — Request transfer of your data to another provider</li>
                </ul>
                <p class="privacy-sec-body">
                    To exercise any of these rights, contact us at
                    <a href="mailto:hello@greenrubycruises.com" class="privacy-link">hello@greenrubycruises.com</a>.
                    We will respond within 30 days.
                </p>
            </div>

            <div class="privacy-section" id="sharing">
                <p class="privacy-sec-num">04</p>
                <h2 class="privacy-sec-title">Data Sharing &amp; Third Parties</h2>
                <p class="privacy-sec-body">We may share your data with trusted third parties only as necessary to provide our services.</p>
                <ul class="privacy-sec-list">
                    <li>Payment processors (for secure transaction handling)</li>
                    <li>Email service providers (for booking confirmations)</li>
                    <li>Government or regulatory authorities (when required by law)</li>
                    <li>Our AI concierge system (anonymised interaction data only)</li>
                </ul>
                <p class="privacy-sec-body">All third-party partners are contractually bound to handle your data in compliance with applicable privacy laws.</p>
            </div>

            <div class="privacy-section" id="cookies">
                <p class="privacy-sec-num">05</p>
                <h2 class="privacy-sec-title">Cookies</h2>
                <p class="privacy-sec-body">Our website uses cookies to improve your browsing experience and analyse site traffic.</p>

                <h3 class="privacy-sec-sub">Types of Cookies We Use</h3>
                <ul class="privacy-sec-list">
                    <li><strong>Essential cookies</strong> — Required for the website to function (cannot be disabled)</li>
                    <li><strong>Analytics cookies</strong> — Help us understand how visitors use our site (Google Analytics)</li>
                    <li><strong>Preference cookies</strong> — Remember your language and settings</li>
                </ul>
                <p class="privacy-sec-body">You can manage cookie preferences through your browser settings or our cookie consent panel.</p>
            </div>

            <div class="privacy-section" id="retention">
                <p class="privacy-sec-num">06</p>
                <h2 class="privacy-sec-title">Data Retention</h2>
                <p class="privacy-sec-body">We retain your personal data only for as long as necessary to fulfil the purposes for which it was collected.</p>
                <ul class="privacy-sec-list">
                    <li>Booking data: 7 years (Vietnamese tax and legal requirements)</li>
                    <li>Marketing data: until you unsubscribe</li>
                    <li>Website analytics: 26 months</li>
                    <li>Contact enquiries: 2 years</li>
                </ul>
            </div>

            <div class="privacy-contact-box">
                <div class="privacy-contact-left">
                    <h3 class="privacy-contact-title">Privacy <em>Questions?</em></h3>
                    <p class="privacy-contact-sub">Our team responds to all privacy requests within 30 days.</p>
                </div>
                <div class="privacy-contact-right">
                    <div class="privacy-contact-item">
                        <span class="privacy-contact-label">Email</span>
                        <span class="privacy-contact-val">hello@greenrubycruises.com</span>
                    </div>
                    <div class="privacy-contact-item">
                        <span class="privacy-contact-label">Hotline</span>
                        <span class="privacy-contact-val">(+84) 386 026 886</span>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb, 'isVisible' => false])
    <script>
        (function () {
            var sections = document.querySelectorAll('#article.page-privacy-policy .privacy-section[id]');
            var tabs = document.querySelectorAll('#article.page-privacy-policy .privacy-jtab');

            if (!sections.length || !tabs.length) {
                return;
            }

            window.addEventListener('scroll', function () {
                var current = '';

                sections.forEach(function (section) {
                    var sectionTop = section.offsetTop - 120;
                    if (window.scrollY >= sectionTop) {
                        current = section.getAttribute('id');
                    }
                });

                tabs.forEach(function (tab) {
                    tab.classList.remove('active');
                    if (tab.getAttribute('href') === '#' + current) {
                        tab.classList.add('active');
                    }
                });
            });

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function (event) {
                    event.preventDefault();
                    var target = document.querySelector(tab.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        })();
    </script>
@endpush
