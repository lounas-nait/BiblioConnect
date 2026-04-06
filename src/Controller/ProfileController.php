<?php

namespace App\Controller;

use App\Entity\Favori;
use App\Entity\Reservation;
use App\Repository\FavoriRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile_index')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'reservations' => $user->getReservations(),
            'favoris' => $user->getFavoris(),
            'commentaires' => $user->getCommentaires(),
        ]);
    }

    #[Route('/profile/reservation/cancel/{id}', name: 'profile_reservation_cancel', methods: ['POST'])]
    public function cancelReservation(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($reservation->getUtilisateur() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation annulée avec succès.');

        return $this->redirectToRoute('profile_index');
    }

    #[Route('/profile/favorite/remove/{id}', name: 'profile_favorite_remove', methods: ['POST'])]
    public function removeFavorite(Favori $favori, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($favori->getUtilisateur() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($favori);
        $entityManager->flush();

        $this->addFlash('success', 'Le livre a été retiré de vos favoris.');

        return $this->redirectToRoute('profile_index');
    }
}
