<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Livre;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CommentaireController extends AbstractController
{
    #[Route('/commentaire/add/{id}', name: 'commentaire_add')]
    public function add(Livre $livre, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Bloquer admins et librarians
        if ($this->isGranted('ROLE_LIBRARIAN') || $this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Les bibliothécaires et administrateurs ne peuvent pas ajouter d\'avis.');
        }

        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setUtilisateur($this->getUser());
            $commentaire->setLivre($livre);
            $commentaire->setDate(new \DateTimeImmutable());
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis, il a bien été publié.');

            return $this->redirectToRoute('catalogue_show', ['id' => $livre->getId()]);
        }

        return $this->render('commentaire/new.html.twig', [
            'livre' => $livre,
            'commentForm' => $form->createView(),
        ]);
    }
}
