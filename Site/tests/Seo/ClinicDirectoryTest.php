<?php

namespace App\Tests\Seo;

use App\Seo\ClinicDirectory;
use PHPUnit\Framework\TestCase;

final class ClinicDirectoryTest extends TestCase
{
    public function testVeterinaryCareIncludesLocalBusinessNAP(): void
    {
        $clinic = new ClinicDirectory(placeId: 'ChIJtest');
        $data = $clinic->veterinaryCare(
            'https://example.test/',
            'Clinique vétérinaire à Granville.',
            'https://example.test/logo.png',
            'https://example.test/og.jpg',
            4.7,
            32,
        );

        self::assertSame('VeterinaryCare', $data['@type']);
        self::assertSame('Clinique vétérinaire du Prétot', $data['name']);
        self::assertSame('18 rue du Village Prétot', $data['address']['streetAddress']);
        self::assertSame('50400', $data['address']['postalCode']);
        self::assertSame('Granville', $data['address']['city'] ?? $data['address']['addressLocality']);
        self::assertSame('+33233906874', $data['telephone']);
        self::assertSame(4.7, $data['aggregateRating']['ratingValue']);
        self::assertSame(32, $data['aggregateRating']['ratingCount']);
        self::assertStringContainsString('query_place_id=ChIJtest', $data['hasMap']);
        self::assertNotEmpty($data['openingHoursSpecification']);
    }

    public function testJsonLdEscapesScriptBreakers(): void
    {
        $clinic = new ClinicDirectory();
        $json = $clinic->jsonLd(['description' => '</script><script>alert(1)']);

        self::assertStringNotContainsString('</script>', $json);
        self::assertIsArray(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testBreadcrumbListPositionsAreSequential(): void
    {
        $clinic = new ClinicDirectory();
        $data = $clinic->breadcrumbList([
            ['name' => 'Accueil', 'url' => 'https://example.test/'],
            ['name' => 'Conseils', 'url' => 'https://example.test/conseils'],
        ]);

        self::assertSame(1, $data['itemListElement'][0]['position']);
        self::assertSame(2, $data['itemListElement'][1]['position']);
        self::assertSame('Conseils', $data['itemListElement'][1]['name']);
    }
}
