<?php

namespace App\Seo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ClinicDirectory
{
    public function __construct(
        #[Autowire('%env(string:GOOGLE_PLACES_PLACE_ID)%')]
        public readonly string $placeId = '',
        public readonly string $name = 'Clinique vétérinaire du Prétot',
        public readonly string $legalName = 'DES VETERINAIRES DU PRETOT',
        public readonly string $streetAddress = '18 rue du Village Prétot',
        public readonly string $postalCode = '50400',
        public readonly string $city = 'Granville',
        public readonly string $region = 'Normandie',
        public readonly string $country = 'FR',
        public readonly string $phone = '+33233906874',
        public readonly string $phoneDisplay = '02 33 90 68 74',
        public readonly string $emergencyPhone = '+33233906874',
        public readonly string $emergencyPhoneDisplay = '02 33  90 68 74',
        public readonly float $latitude = 48.83727,
        public readonly float $longitude = -1.546801,
        public readonly string $hoursShort = 'Lun. - Ven. 8h30 - 12h / 14h - 19h · Sam. 8h30 - 12h / 14h - 17h',
        public readonly string $ogImagePath = 'images/hero-dog.jpg',
        public readonly string $logoPath = 'logo.png',
    ) {
    }

    public function formattedAddress(): string
    {
        return sprintf('%s, %s %s', $this->streetAddress, $this->postalCode, $this->city);
    }

    public function mapsSearchUrl(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->formattedAddress());
    }

    public function mapsEmbedUrl(): string
    {
        return 'https://www.google.com/maps?q='.rawurlencode($this->formattedAddress()).'&output=embed';
    }

    public function mapsPlaceUrl(): string
    {
        $url = $this->mapsSearchUrl();
        if ($this->placeId !== '') {
            $url .= '&query_place_id='.rawurlencode($this->placeId);
        }

        return $url;
    }

    /**
     * @return array<string, mixed>
     */
    public function veterinaryCare(
        string $canonicalUrl,
        string $description,
        string $logoUrl,
        string $imageUrl,
        ?float $rating = null,
        int $ratingCount = 0,
    ): array {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'VeterinaryCare',
            '@id' => $canonicalUrl.'#veterinarycare',
            'name' => $this->name,
            'legalName' => $this->legalName,
            'url' => $canonicalUrl,
            'image' => $imageUrl,
            'logo' => $logoUrl,
            'description' => $description,
            'telephone' => $this->phone,
            'priceRange' => '€€',
            'currenciesAccepted' => 'EUR',
            'paymentAccepted' => 'Cash, Credit Card',
            'availableLanguage' => ['French'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->streetAddress,
                'addressLocality' => $this->city,
                'postalCode' => $this->postalCode,
                'addressRegion' => $this->region,
                'addressCountry' => $this->country,
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'hasMap' => $this->mapsPlaceUrl(),
            'areaServed' => [
                ['@type' => 'City', 'name' => 'Granville'],
                ['@type' => 'City', 'name' => 'Donville-les-Bains'],
                ['@type' => 'City', 'name' => 'Saint-Pair-sur-Mer'],
                ['@type' => 'AdministrativeArea', 'name' => 'Manche'],
            ],
            'openingHoursSpecification' => $this->openingHoursSpecification(),
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => $this->phone,
                    'contactType' => 'customer service',
                    'areaServed' => 'FR',
                    'availableLanguage' => 'French',
                    'hoursAvailable' => $this->openingHoursSpecification(),
                ],
                [
                    '@type' => 'ContactPoint',
                    'telephone' => $this->emergencyPhone,
                    'contactType' => 'emergency',
                    'areaServed' => 'FR',
                    'availableLanguage' => 'French',
                    'hoursAvailable' => [
                        '@type' => 'OpeningHoursSpecification',
                        'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                        'opens' => '00:00',
                        'closes' => '23:59',
                    ],
                ],
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Soins vétérinaires',
                'itemListElement' => [
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Médecine générale vétérinaire']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Vaccination et prévention']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Chirurgie vétérinaire']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Imagerie et analyses']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Nutrition et comportement']],
                ],
            ],
        ];

        if ($this->placeId !== '') {
            $data['sameAs'] = [$this->mapsPlaceUrl()];
            $data['identifier'] = $this->placeId;
        }

        if ($rating !== null && $ratingCount > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($rating, 1),
                'bestRating' => 5,
                'worstRating' => 1,
                'ratingCount' => $ratingCount,
            ];
        }

        return $data;
    }

    /**
     * @param list<array{name: string, url: string}> $items
     *
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array
    {
        $elements = [];
        foreach ($items as $index => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function article(
        string $headline,
        string $description,
        string $url,
        string $imageUrl,
        string $publisherLogoUrl,
    ): array {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $headline,
            'description' => $description,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'image' => $imageUrl,
            'inLanguage' => 'fr-FR',
            'author' => ['@type' => 'Organization', 'name' => $this->name],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->name,
                'logo' => ['@type' => 'ImageObject', 'url' => $publisherLogoUrl],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function webSite(string $homeUrl): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->name,
            'url' => $homeUrl,
            'inLanguage' => 'fr-FR',
            'publisher' => ['@type' => 'Organization', 'name' => $this->name],
        ];
    }

    public function jsonLd(array $data): string
    {
        return json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function openingHoursSpecification(): array
    {
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $slots = [];

        foreach ($weekdays as $day) {
            $slots[] = $this->hoursSlot($day, '08:30', '12:00');
            $slots[] = $this->hoursSlot($day, '14:00', '19:00');
        }

        $slots[] = $this->hoursSlot('Saturday', '08:30', '12:00');
        $slots[] = $this->hoursSlot('Saturday', '14:00', '17:00');

        return $slots;
    }

    /**
     * @return array<string, string>
     */
    private function hoursSlot(string $day, string $opens, string $closes): array
    {
        return [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $day,
            'opens' => $opens,
            'closes' => $closes,
        ];
    }
}
