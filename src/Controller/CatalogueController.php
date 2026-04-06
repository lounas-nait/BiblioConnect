<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Form\CommentaireType;
use App\Form\ReservationType;
use App\Repository\CategorieRepository;
use App\Repository\FavoriRepository;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogueController extends AbstractController
{
    #[Route('/catalogue', name: 'catalogue_index')]
    public function index(Request $request, LivreRepository $livreRepository, CategorieRepository $categorieRepository): Response
    {
        $term = trim((string) $request->query->get('q', ''));
        $categoryId = $request->query->get('category');
        $category = $categoryId ? $categorieRepository->find($categoryId) : null;

        $livres = $livreRepository->search($term, $category);
        $categories = $categorieRepository->findAll();

        return $this->render('catalogue/index.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'selectedCategory' => $category,
            'term' => $term,
        ]);
    }

    #[Route('/catalogue/{id}', name: 'catalogue_show')]
    public function show(Livre $livre, Request $request, FavoriRepository $favoriRepository): Response
    {
        $commentForm = $this->createForm(CommentaireType::class);
        $reservationForm = $this->createForm(ReservationType::class);

        $isFavorite = false;
        $user = $this->getUser();

        if ($user) {
            $favorite = $favoriRepository->findOneBy([
                'utilisateur' => $user,
                'livre' => $livre,
            ]);
            $isFavorite = (bool) $favorite;
        }

        return $this->render('catalogue/show.html.twig', [
            'livre' => $livre,
            'commentForm' => $commentForm->createView(),
            'reservationForm' => $reservationForm->createView(),
            'isFavorite' => $isFavorite,
        ]);
    }
}
