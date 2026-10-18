<?php

namespace App\Tests\Service;

use App\Service\GoogleReviewsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GoogleReviewsProviderTest extends TestCase
{
    public function testItMapsGooglePlaceDetailsToReviews(): void
    {
        $response = new MockResponse(json_encode([
            'rating' => 4.7,
            'userRatingCount' => 128,
            'googleMapsUri' => 'https://maps.google.com/example',
            'reviews' => [[
                'rating' => 5,
                'text' => ['text' => 'Une équipe très attentive.'],
                'authorAttribution' => ['displayName' => 'Camille'],
                'relativePublishTimeDescription' => 'il y a un mois',
            ]],
        ], JSON_THROW_ON_ERROR));
        $provider = new GoogleReviewsProvider(
            new MockHttpClient($response),
            new ArrayAdapter(),
            'test-api-key',
            'test-place-id',
        );

        self::assertSame([
            'rating' => 4.7,
            'rating_count' => 128,
            'maps_url' => 'https://maps.google.com/example',
            'reviews' => [[
                'author' => 'Camille',
                'rating' => 5,
                'text' => 'Une équipe très attentive.',
                'published_at' => 'il y a un mois',
            ]],
        ], $provider->getReviews());
    }
}