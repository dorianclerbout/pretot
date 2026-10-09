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
    public function admin(): Response
    {
        return $this->render('clinic/admin.html.twig', ['active_page' => 'admin']);
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