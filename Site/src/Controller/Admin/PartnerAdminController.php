<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use App\Form\PartnerType;
use App\Repository\PartnerRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/partenaires')]
#[IsGranted('ROLE_ADMIN')]
final class PartnerAdminController extends AbstractController
{
    #[Route('', name: 'admin_partner_index', methods: ['GET'])]
    public function index(PartnerRepository $partnerRepository): Response
    {
        return $this->render('clinic/admin/partner_index.html.twig', [
            'active_page' => 'admin',
            'partners' => $partnerRepository->findAllOrdered(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_partner_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $partner = new Partner();
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleLogoUpload($form, $partner, $imageUploader);

            $entityManager->persist($partner);
            $entityManager->flush();

            $this->addFlash('success', 'Partenaire ajouté.');

            return $this->redirectToRoute('admin_partner_index');
        }

        return $this->render('clinic/admin/partner_form.html.twig', [
            'active_page' => 'admin',
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_partner_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partner $partner, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleLogoUpload($form, $partner, $imageUploader);

            $entityManager->flush();

            $this->addFlash('success', 'Partenaire mis à jour.');

            return $this->redirectToRoute('admin_partner_index');
        }

        return $this->render('clinic/admin/partner_form.html.twig', [
            'active_page' => 'admin',
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_partner_delete', methods: ['POST'])]
    public function delete(Request $request, Partner $partner, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-partner-'.$partner->getId(), $request->getPayload()->getString('_token'))) {
            $imageUploader->remove($partner->getLogoFilename());
            $entityManager->remove($partner);
            $entityManager->flush();

            $this->addFlash('success', 'Partenaire supprimé.');
        }

        return $this->redirectToRoute('admin_partner_index');
    }

    private function handleLogoUpload(FormInterface $form, Partner $partner, ImageUploader $imageUploader): void
    {
        /** @var UploadedFile|null $logoFile */
        $logoFile = $form->get('logoFile')->getData();

        if (!$logoFile) {
            return;
        }

        $imageUploader->remove($partner->getLogoFilename());
        $partner->setLogoFilename($imageUploader->upload($logoFile, 'partners'));
    }
}
