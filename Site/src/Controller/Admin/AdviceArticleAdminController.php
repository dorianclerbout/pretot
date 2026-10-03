<?php

namespace App\Controller\Admin;

use App\Entity\AdviceArticle;
use App\Form\AdviceArticleType;
use App\Repository\AdviceArticleRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/conseils')]
#[IsGranted('ROLE_ADMIN')]
final class AdviceArticleAdminController extends AbstractController
{
    #[Route('', name: 'admin_advice_index', methods: ['GET'])]
    public function index(AdviceArticleRepository $articleRepository): Response
    {
        return $this->render('clinic/admin/advice_index.html.twig', [
            'active_page' => 'admin',
            'articles' => $articleRepository->findAllOrdered(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_advice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploader $imageUploader, SluggerInterface $slugger, AdviceArticleRepository $articleRepository): Response
    {
        $article = new AdviceArticle();
        $form = $this->createForm(AdviceArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setSlug($this->generateUniqueSlug($article->getTitle(), $slugger, $articleRepository));
            $article->setContent(str_replace("\r\n", "\n", $article->getContent()));
            $this->handleImageUpload($form, $article, $imageUploader);

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article ajouté.');

            return $this->redirectToRoute('admin_advice_index');
        }

        return $this->render('clinic/admin/advice_form.html.twig', [
            'active_page' => 'admin',
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_advice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdviceArticle $article, EntityManagerInterface $entityManager, ImageUploader $imageUploader, SluggerInterface $slugger, AdviceArticleRepository $articleRepository): Response
    {
        $previousTitle = $article->getTitle();
        $form = $this->createForm(AdviceArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($article->getTitle() !== $previousTitle) {
                $article->setSlug($this->generateUniqueSlug($article->getTitle(), $slugger, $articleRepository, $article->getId()));
            }
            $article->setContent(str_replace("\r\n", "\n", $article->getContent()));
            $this->handleImageUpload($form, $article, $imageUploader);

            $entityManager->flush();

            $this->addFlash('success', 'Article mis à jour.');

            return $this->redirectToRoute('admin_advice_index');
        }

        return $this->render('clinic/admin/advice_form.html.twig', [
            'active_page' => 'admin',
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_advice_delete', methods: ['POST'])]
    public function delete(Request $request, AdviceArticle $article, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-advice-'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $imageUploader->remove($article->getImageFilename());
            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé.');
        }

        return $this->redirectToRoute('admin_advice_index');
    }

    private function generateUniqueSlug(string $title, SluggerInterface $slugger, AdviceArticleRepository $articleRepository, ?int $excludeId = null): string
    {
        $base = strtolower((string) $slugger->slug($title));
        $slug = $base;
        $suffix = 2;

        while (true) {
            $existing = $articleRepository->findOneBy(['slug' => $slug]);

            if (!$existing || $existing->getId() === $excludeId) {
                return $slug;
            }

            $slug = $base.'-'.$suffix++;
        }
    }

    private function handleImageUpload(FormInterface $form, AdviceArticle $article, ImageUploader $imageUploader): void
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('imageFile')->getData();

        if (!$imageFile) {
            return;
        }

        $imageUploader->remove($article->getImageFilename());
        $article->setImageFilename($imageUploader->upload($imageFile, 'advice'));
    }
}
