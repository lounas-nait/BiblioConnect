<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Langue;
use App\Entity\Livre;
use App\Form\AuteurType;
use App\Form\CategorieType;
use App\Form\LangueType;
use App\Form\LivreType;
use App\Repository\AuteurRepository;
use App\Repository\CategorieRepository;
use App\Repository\LangueRepository;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_LIBRARIAN')]
class LibrarianController extends AbstractController
{
    #[Route('/librarian/dashboard', name: 'librarian_dashboard')]
    public function dashboard(
        LivreRepository $livreRepository,
        AuteurRepository $auteurRepository,
        CategorieRepository $categorieRepository,
        LangueRepository $langueRepository
    ): Response {
        return $this->render('librarian/dashboard.html.twig', [
            'livres' => $livreRepository->findAll(),
            'auteurs' => $auteurRepository->findAll(),
            'categories' => $categorieRepository->findAll(),
            'langues' => $langueRepository->findAll(),
        ]);
    }

    #[Route('/librarian/book/new', name: 'librarian_book_new')]
    public function newBook(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            $this->addFlash('success', 'Livre ajouté au catalogue.');

            return $this->redirectToRoute('librarian_dashboard');
        }

        return $this->render('librarian/book_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un ouvrage',
        ]);
    }

    #[Route('/librarian/book/{id}/edit', name: 'librarian_book_edit')]
    public function editBook(Livre $livre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Ouvrage mis à jour.');

            return $this->redirectToRoute('librarian_dashboard');
        }

        return $this->render('librarian/book_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier l’ouvrage',
        ]);
    }

    #[Route('/librarian/auteur/new', name: 'librarian_auteur_new')]
    public function newAuteur(Request $request, EntityManagerInterface $entityManager): Response
    {
        $auteur = new Auteur();
        $form = $this->createForm(AuteurType::class, $auteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($auteur);
            $entityManager->flush();

            $this->addFlash('success', 'Auteur publié.');

            return $this->redirectToRoute('librarian_dashboard');
        }

        return $this->render('librarian/reference_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un auteur',
        ]);
    }

    #[Route('/librarian/categorie/new', name: 'librarian_categorie_new')]
    public function newCategorie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie ajoutée.');

            return $this->redirectToRoute('librarian_dashboard');
        }

        return $this->render('librarian/reference_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter une catégorie',
        ]);
    }

    #[Route('/librarian/langue/new', name: 'librarian_langue_new')]
    public function newLangue(Request $request, EntityManagerInterface $entityManager): Response
    {
        $langue = new Langue();
        $form = $this->createForm(LangueType::class, $langue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($langue);
            $entityManager->flush();

            $this->addFlash('success', 'Langue ajoutée.');

            return $this->redirectToRoute('librarian_dashboard');
        }

        return $this->render('librarian/reference_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter une langue',
        ]);
    }
}
