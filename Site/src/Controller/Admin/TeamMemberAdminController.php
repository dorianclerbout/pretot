<?php

namespace App\Controller\Admin;

use App\Entity\TeamMember;
use App\Form\TeamMemberType;
use App\Repository\TeamMemberRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/equipe')]
#[IsGranted('ROLE_ADMIN')]
final class TeamMemberAdminController extends AbstractController
{
    #[Route('', name: 'admin_team_index', methods: ['GET'])]
    public function index(TeamMemberRepository $teamMemberRepository): Response
    {
        return $this->render('clinic/admin/team_index.html.twig', [
            'active_page' => 'admin',
            'team_members' => $teamMemberRepository->findAllOrdered(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $teamMember = new TeamMember();
        $form = $this->createForm(TeamMemberType::class, $teamMember);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $teamMember, $imageUploader);
            $entityManager->persist($teamMember);
            $entityManager->flush();

            $this->addFlash('success', 'Membre ajouté à l’équipe.');

            return $this->redirectToRoute('admin_team_index');
        }

        return $this->render('clinic/admin/team_form.html.twig', [
            'active_page' => 'admin',
            'team_member' => $teamMember,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TeamMember $teamMember, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        $form = $this->createForm(TeamMemberType::class, $teamMember);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $teamMember, $imageUploader);
            $entityManager->flush();

            $this->addFlash('success', 'Présentation mise à jour.');

            return $this->redirectToRoute('admin_team_index');
        }

        return $this->render('clinic/admin/team_form.html.twig', [
            'active_page' => 'admin',
            'team_member' => $teamMember,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_team_delete', methods: ['POST'])]
    public function delete(Request $request, TeamMember $teamMember, EntityManagerInterface $entityManager, ImageUploader $imageUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-team-'.$teamMember->getId(), $request->getPayload()->getString('_token'))) {
            $imageUploader->remove($teamMember->getImageFilename());
            $entityManager->remove($teamMember);
            $entityManager->flush();

            $this->addFlash('success', 'Membre supprimé.');
        }

        return $this->redirectToRoute('admin_team_index');
    }

    private function handleImageUpload(FormInterface $form, TeamMember $teamMember, ImageUploader $imageUploader): void
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('imageFile')->getData();

        if (!$imageFile) {
            return;
        }

        $imageUploader->remove($teamMember->getImageFilename());
        $teamMember->setImageFilename($imageUploader->upload($imageFile, 'team'));
    }
}