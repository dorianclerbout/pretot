<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SeoControllerTest extends WebTestCase
{
    public function testRobotsTxtPointsToSitemapAndHidesPrivateAreas(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/plain; charset=UTF-8');
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertStringContainsString('Disallow: /admin', $content);
        self::assertStringContainsString('Disallow: /connexion', $content);
        self::assertStringContainsString('Sitemap: ', $content);
        self::assertStringContainsString('/sitemap.xml', $content);
    }

    public function testLoginIsNoindexed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="fr"]');
        self::assertSelectorExists('meta[name="robots"][content="noindex, nofollow"]');
        self::assertSelectorExists('#contenu');
    }
}
