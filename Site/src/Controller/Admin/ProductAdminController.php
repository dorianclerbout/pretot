<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/boutique')]
#[IsGranted('ROLE_ADMIN')]
final class ProductAdminController extends AbstractController
{
    #[Route('', name: 'admin_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('clinic/admin/product_index.html.twig', [
            'active_page' => 'admin',
            'products' => $productRepository->findAllOrdered(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $product, $imageUploader);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Article ajouté à la boutique.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('clinic/admin/product_form.html.twig', [
            'active_page' => 'admin',
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $product, $imageUploader);

            $entityManager->flush();

            $this->addFlash('success', 'Article mis à jour.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('clinic/admin/product_form.html.twig', [
            'active_page' => 'admin',
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-product-'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $imageUploader->remove($product->getImageFilename());
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé.');
        }

        return $this->redirectToRoute('admin_product_index');
    }

    private function handleImageUpload(FormInterface $form, Product $product, ImageUploader $imageUploader): void
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('imageFile')->getData();

        if (!$imageFile) {
            return;
        }

        $imageUploader->remove($product->getImageFilename());
        $product->setImageFilename($imageUploader->upload($imageFile, 'products'));
    }
}
