<?php

namespace App\Controller;

use App\Repository\AdviceArticleRepository;
use App\Repository\CarouselSlideRepository;
use App\Repository\PartnerRepository;
use App\Repository\ProductRepository;
use App\Repository\TeamMemberRepository;
use App\Service\GoogleReviewsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ClinicController extends AbstractController
{
    #[Route('/', name: 'clinic_home')]
    public function home(
        TeamMemberRepository $teamMemberRepository,
        CarouselSlideRepository $carouselSlideRepository,
        GoogleReviewsProvider $googleReviewsProvider,
    ): Response
    {
        return $this->render('clinic/home.html.twig', [
            'active_page' => 'home',
            'slides' => $carouselSlideRepository->findVisibleOrdered(),
            'team_members' => $teamMemberRepository->findVisibleOrdered(),
            'google_reviews' => $googleReviewsProvider->getReviews(),
        ]);
    }

    #[Route('/boutique', name: 'clinic_shop')]
    public function shop(ProductRepository $productRepository): Response
    {
        return $this->render('clinic/shop.html.twig', [
            'active_page' => 'shop',
            'products' => $productRepository->findVisibleOrdered(),
            'purchase_platform_url' => $this->getParameter('app.purchase_platform_url'),
        ]);
    }

    #[Route('/clinique', name: 'clinic_about')]
    public function about(TeamMemberRepository $teamMemberRepository): Response
    {
        return $this->render('clinic/about.html.twig', [
            'active_page' => 'about',
            'team_members' => $teamMemberRepository->findVisibleOrdered(),
        ]);
    }

    #[Route('/services', name: 'clinic_services')]
    public function services(): Response
    {
        return $this->render('clinic/services.html.twig', ['active_page' => 'services']);
    }

    #[Route('/conseils', name: 'clinic_advice')]
    public function advice(AdviceArticleRepository $articleRepository): Response
    {
        return $this->render('clinic/advice.html.twig', [
            'active_page' => 'advice',
            'articles' => $articleRepository->findVisibleOrdered(),
        ]);
    }

    #[Route('/conseils/{slug}', name: 'clinic_advice_show')]
    public function adviceShow(string $slug, AdviceArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneVisibleBySlug($slug);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        return $this->render('clinic/advice_show.html.twig', [
            'active_page' => 'advice',
            'article' => $article,
        ]);
    }

    #[Route('/contact', name: 'clinic_contact')]
    public function contact(PartnerRepository $partnerRepository): Response
    {
        return $this->render('clinic/contact.html.twig', [
            'active_page' => 'contact',
            'partners' => $partnerRepository->findVisibleOrdered(),
        ]);
    }

    #[Route('/mentions-legales', name: 'clinic_legal')]
    public function legal(): Response
    {
        return $this->render('clinic/legal.html.twig', ['active_page' => 'legal']);
    }

    #[Route('/robots.txt', name: 'clinic_robots', format: 'txt')]
    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /connexion\nSitemap: ";
        $content .= $this->generateUrl('clinic_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL)."\n";

        return new Response($content, headers: ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    #[Route('/sitemap.xml', name: 'clinic_sitemap', format: 'xml')]
    public function sitemap(AdviceArticleRepository $articleRepository): Response
    {
        $routes = ['clinic_home', 'clinic_about', 'clinic_services', 'clinic_shop', 'clinic_advice', 'clinic_contact', 'clinic_legal'];
        $urls = array_map(fn (string $route): array => [
            'loc' => $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL),
        ], $routes);

        foreach ($articleRepository->findVisibleOrdered() as $article) {
            $urls[] = ['loc' => $this->generateUrl('clinic_advice_show', ['slug' => $article->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)];
        }

        return new Response(
            $this->renderView('clinic/sitemap.xml.twig', ['urls' => $urls]),
            headers: ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }

    #[Route('/admin', name: 'clinic_admin')]
    public function admin(
        ProductRepository $productRepository,
        AdviceArticleRepository $articleRepository,
        TeamMemberRepository $teamMemberRepository,
        PartnerRepository $partnerRepository,
        CarouselSlideRepository $carouselSlideRepository,
    ): Response
    {
        $dashboardContent = [
            [
                'label' => 'Produits',
                'route' => 'admin_product_index',
                'online' => $productRepository->count(['visible' => true]),
                'total' => $productRepository->count([]),
            ],
            [
                'label' => 'Articles conseils',
                'route' => 'admin_advice_index',
                'online' => $articleRepository->count(['visible' => true]),
                'total' => $articleRepository->count([]),
            ],
            [
                'label' => 'Équipe',
                'route' => 'admin_team_index',
                'online' => $teamMemberRepository->count(['visible' => true]),
                'total' => $teamMemberRepository->count([]),
            ],
            [
                'label' => 'Partenaires',
                'route' => 'admin_partner_index',
                'online' => $partnerRepository->count(['visible' => true]),
                'total' => $partnerRepository->count([]),
            ],
            [
                'label' => 'Images du carrousel',
                'route' => 'admin_carousel_index',
                'online' => $carouselSlideRepository->count(['visible' => true]),
                'total' => $carouselSlideRepository->count([]),
            ],
        ];

        return $this->render('clinic/admin.html.twig', [
            'active_page' => 'admin',
            'dashboard_content' => $dashboardContent,
            'products_online' => $dashboardContent[0]['online'],
            'articles_online' => $dashboardContent[1]['online'],
            'content_online' => array_sum(array_column($dashboardContent, 'online')),
            'content_total' => array_sum(array_column($dashboardContent, 'total')),
        ]);
    }

    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('Cette route est interceptée par le firewall Symfony.');
    }
}