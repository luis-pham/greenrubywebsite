@php
    use Modules\FrontEnd\Helpers\FeLanguageUtils;
    use Modules\BackEnd\Helpers\Utilities;

    $language = FeLanguageUtils::getCurrentLanguage();
    $config = Utilities::getAllConfig($language);

    $name = array_key_exists('website-name', $config) ? ($config['website-name'] ?? null) : null;
    $description = array_key_exists('website-description', $config) ? ($config['website-description'] ?? null) : null;
    $phone = array_key_exists('hotline', $config) ? ($config['hotline'] ?? null) : null;
    $email = array_key_exists('email', $config) ? ($config['email'] ?? null) : null;
    $whatsapp = array_key_exists('whatsapp', $config) ? ($config['whatsapp'] ?? null) : null;
    $logo = array_key_exists('website-logo', $config) && !empty($config['website-logo'])
        ? asset(FeUtils::getImageLink($config['website-logo']))
        : null;

    $sameAs = [];
    foreach (['tripadvisor', 'facebook', 'instagram', 'tiktok', 'youtube', 'linkedin'] as $k) {
        if (array_key_exists($k, $config) && !empty($config[$k])) {
            $sameAs[] = $config[$k];
        }
    }

    $isValid = !empty($name);

    if ($isValid) {
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type'    => ['TravelAgency', 'LocalBusiness'],
            '@id'      => config('frontend.organizationSchemaId'),
            'name'     => $name,
            'url'      => config('frontend.organizationCanonicalUrl'),
            'address'  => array_merge(
                ['@type' => 'PostalAddress'],
                config('frontend.organizationAddress')
            ),
            'geo' => array_merge(
                ['@type' => 'GeoCoordinates'],
                config('frontend.organizationGeo')
            ),
            'areaServed' => config('frontend.organizationAreaServed'),
        ];

        if (!empty($phone)) {
            $schemaData['telephone'] = $phone;
        }

        if (!empty($logo)) {
            $schemaData['logo'] = $logo;
        }

        if (!empty($description)) {
            $schemaData['description'] = $description;
        }

        $contactPoints = [];

        if (!empty($phone) || !empty($email)) {
            $cp = [
                '@type'       => 'ContactPoint',
                'contactType' => 'Customer Service'
            ];
            if (!empty($phone)) $cp['telephone'] = $phone;
            if (!empty($email)) $cp['email'] = $email;

            $contactPoints[] = $cp;
        }

        if (!empty($whatsapp)) {
            $contactPoints[] = [
                '@type'       => 'ContactPoint',
                'contactType' => 'WhatsApp Support',
                'telephone'   => $whatsapp
            ];
        }

        if (!empty($contactPoints)) {
            $schemaData['contactPoint'] = $contactPoints;
        }

        if (is_array($sameAs) && !empty($sameAs)) {
            $sameAs = array_values(array_filter($sameAs, function ($v) {
                return is_string($v) && trim($v) !== '';
            }));
            if (!empty($sameAs)) {
                $schemaData['sameAs'] = $sameAs;
            }
        }
    }
@endphp

@if(isset($isValid) && $isValid)
<script type="application/ld+json">
    {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endif
