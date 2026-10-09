<?php

namespace App\Controller\Admin;

use App\Entity\CarouselSlide;
use App\Form\CarouselSlideType;
use App\Repository\CarouselSlideRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/carrousel')]
#[IsGranted('ROLE_ADMIN')]
final class CarouselSlideAdminController extends AbstractController
{
    #[Route('', name: 'admin_carousel_index', methods: ['GET'])]
    public function index(CarouselSlideRepository $repository): Response
    {
        return $this->render('clinic/admin/carousel_index.html.twig', [
            'active_page' => 'admin',
            'slides' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_carousel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $slide = new CarouselSlide();
        $form = $this->createForm(CarouselSlideType::class, $slide, ['require_image' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->handleUpload($form, $slide, $imageUploader)) {
            $entityManager->persist($slide);
            $entityManager->flush();

            $this->addFlash('success', 'Image ajoutée au carrousel.');

            return $this->redirectToRoute('admin_carousel_index');
        }

        return $this->render('clinic/admin/carousel_form.html.twig', [
            'active_page' => 'admin',
            'slide' => $slide,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_carousel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CarouselSlide $slide, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $form = $this->createForm(CarouselSlideType::class, $slide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->handleUpload($form, $slide, $imageUploader)) {
            $entityManager->flush();

            $this->addFlash('success', 'Carrousel mis à jour.');

            return $this->redirectToRoute('admin_carousel_index');
        }

        return $this->render('clinic/admin/carousel_form.html.twig', [
            'active_page' => 'admin',
            'slide' => $slide,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_carousel_delete', methods: ['POST'])]
    public function delete(Request $request, CarouselSlide $slide, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-carousel-'.$slide->getId(), $request->getPayload()->getString('_token'))) {
            $imageUploader->remove($slide->getImageFilename());
            $entityManager->remove($slide);
            $entityManager->flush();

            $this->addFlash('success', 'Image supprimée du carrousel.');
        }

        return $this->redirectToRoute('admin_carousel_index');
    }

    private function handleUpload(FormInterface $form, CarouselSlide $slide, ImageUploader $imageUploader): bool
    {
        /** @var UploadedFile|null $file */
        $file = $form->get('imageFile')->getData();

        if (!$file) {
            return true;
        }

        try {
            $filename = $imageUploader->upload($file, 'carousel');
        } catch (FileException) {
            $form->get('imageFile')->addError(new FormError('Impossible d\'enregistrer l\'image sur le serveur.'));

            return false;
        }

        if ($slide->getImageFilename()) {
            $imageUploader->remove($slide->getImageFilename());
        }
        $slide->setImageFilename($filename);

        return true;
    }
}
