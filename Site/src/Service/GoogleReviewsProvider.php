<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class GoogleReviewsProvider
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        #[Autowire('%env(string:GOOGLE_PLACES_API_KEY)%')]
        private readonly string $apiKey,
        #[Autowire('%env(string:GOOGLE_PLACES_PLACE_ID)%')]
        private readonly string $placeId,
    ) {
    }

    /** @return array{rating: float|null, rating_count: int, maps_url: string, reviews: list<array{author: string, rating: int, text: string, published_at: string}>} */
    public function getReviews(): array
    {
        $fallback = [
            'rating' => null,
            'rating_count' => 0,
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=Clinique+V%C3%A9t%C3%A9rinaire+du+Pr%C3%A9tot&query_place_id=' . rawurlencode($this->placeId),
            'reviews' => [],
        ];

        if ($this->apiKey === '' || $this->placeId === '') {
            return $fallback;
        }

        try {
            return $this->cache->get('google_reviews_' . hash('sha256', $this->placeId), function (ItemInterface $item) use ($fallback): array {
                $item->expiresAfter(21600);
                $response = $this->httpClient->request('GET', 'https://places.googleapis.com/v1/places/' . rawurlencode($this->placeId), [
                    'headers' => [
                        'X-Goog-Api-Key' => $this->apiKey,
                        'X-Goog-FieldMask' => 'rating,userRatingCount,reviews,googleMapsUri',
                    ],
                    'query' => ['languageCode' => 'fr', 'regionCode' => 'FR'],
                ]);
                $place = $response->toArray(false);

                if ($response->getStatusCode() >= 400) {
                    throw new \RuntimeException('Google Places API request failed.');
                }

                $reviews = [];
                foreach ($place['reviews'] ?? [] as $review) {
                    if ((int) ($review['rating'] ?? 0) < 4) {
                        continue;
                    }
                    $reviews[] = [
                        'author' => $review['authorAttribution']['displayName'] ?? 'Client Google',
                        'rating' => (int) ($review['rating'] ?? 0),
                        'text' => $review['text']['text'] ?? '',
                        'published_at' => $review['relativePublishTimeDescription'] ?? '',
                    ];
                }

                return [
                    'rating' => isset($place['rating']) ? (float) $place['rating'] : null,
                    'rating_count' => (int) ($place['userRatingCount'] ?? 0),
                    'maps_url' => $place['googleMapsUri'] ?? $fallback['maps_url'],
                    'reviews' => $reviews,
                ];
            });
        } catch (\Throwable) {
            return $fallback;
        }
    }
}