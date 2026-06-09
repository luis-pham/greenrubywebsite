@php
    use Modules\FrontEnd\Helpers\FeLanguageUtils;
    use Modules\BackEnd\Helpers\Utilities;

    $language = FeLanguageUtils::getCurrentLanguage();
    $config = Utilities::getAllConfig($language);

    $url = (isset($url) && !empty($url)) ? $url : url('/');

    $name = array_key_exists('website-name', $config) ? ($config['website-name'] ?? null) : null;
    $description = array_key_exists('website-description', $config) ? ($config['website-description'] ?? null) : null;
    $phone = array_key_exists('hotline', $config) ? ($config['hotline'] ?? null) : null;
    $email = array_key_exists('email', $config) ? ($config['email'] ?? null) : null;
    $zalo = array_key_exists('zalo', $config) ? ($config['zalo'] ?? null) : null;
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

    $isValid = !empty($name) && !empty($url);

    if ($isValid) {
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => $name,
            'url'      => $url,
        ];

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
            
            $contactPoints[] = $cp; // Đẩy vào mảng
        }

        if (!empty($zalo)) {
            $contactPoints[] = [
                '@type'       => 'ContactPoint',
                'contactType' => 'Zalo Support',
                'url'         => $zalo 
            ];
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