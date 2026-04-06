<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    #[Route('/reserve/{id}', name: 'reserve_book')]
    public function reserve(Livre $livre, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Bloquer admins et librarians
        if ($this->isGranted('ROLE_LIBRARIAN') || $this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Les bibliothécaires et administrateurs ne peuvent pas réserver de livres.');
        }

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setUtilisateur($this->getUser());
            $reservation->setLivre($livre);
            $reservation->setStatus('en attente');
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réservation a bien été enregistrée.');

            return $this->redirectToRoute('catalogue_show', ['id' => $livre->getId()]);
        }

        return $this->render('reservation/new.html.twig', [
            'livre' => $livre,
            'reservationForm' => $form->createView(),
        ]);
    }
}
