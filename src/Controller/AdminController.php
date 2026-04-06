<?php

namespace App\Controller;

use App\Form\UserRoleType;
use App\Repository\CommentaireRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(UserRepository $userRepository, ReservationRepository $reservationRepository, CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
            'reservations' => $reservationRepository->findBy([], ['dateReservation' => 'DESC']),
            'commentaires' => $commentaireRepository->findBy([], ['date' => 'DESC']),
        ]);
    }

    #[Route('/admin/comment/delete/{id}', name: 'admin_comment_delete', methods: ['POST'])]
    public function deleteComment(int $id, CommentaireRepository $commentaireRepository, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $commentaireRepository->find($id);
        if (!$commentaire) {
            throw $this->createNotFoundException('Commentaire introuvable.');
        }

        $entityManager->remove($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire supprimé.');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/user/{id}/roles', name: 'admin_user_roles')]
    public function editUserRoles(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $form = $this->createForm(UserRoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rôles utilisateurs mis à jour.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/user_roles.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
