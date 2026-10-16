<?php

namespace App\Controller;

use App\Repository\AdviceArticleRepository;
use App\Repository\CarouselSlideRepository;
use App\Repository\PartnerRepository;
use App\Repository\ProductRepository;
use App\Repository\TeamMemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClinicController extends AbstractController
{
    #[Route('/', name: 'clinic_home')]
    public function home(TeamMemberRepository $teamMemberRepository, CarouselSlideRepository $carouselSlideRepository): Response
    {
        return $this->render('clinic/home.html.twig', [
            'active_page' => 'home',
            'slides' => $carouselSlideRepository->findVisibleOrdered(),
            'team_members' => $teamMemberRepository->findVisibleOrdered(),
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