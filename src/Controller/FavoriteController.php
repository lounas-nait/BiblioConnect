<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Favori;
use App\Repository\FavoriRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FavoriteController extends AbstractController
{
    #[Route('/favorite/toggle/{id}', name: 'favorite_toggle')]
    public function toggle(Livre $livre, FavoriRepository $favoriRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $favorite = $favoriRepository->findOneBy([
            'utilisateur' => $user,
            'livre' => $livre,
        ]);

        if ($favorite) {
            $entityManager->remove($favorite);
            $this->addFlash('success', 'Livre retiré de vos favoris.');
        } else {
            $favorite = new Favori();
            $favorite->setUtilisateur($user);
            $favorite->setLivre($livre);
            $entityManager->persist($favorite);
            $this->addFlash('success', 'Livre ajouté à vos favoris.');
        }

        $entityManager->flush();

        return $this->redirectToRoute('catalogue_show', ['id' => $livre->getId()]);
    }
}
