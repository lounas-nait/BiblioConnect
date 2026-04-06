<?php

namespace App\DataFixtures;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Favori;
use App\Entity\Langue;
use App\Entity\Livre;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $langueFr = new Langue();
        $langueFr->setNom('Français');
        $manager->persist($langueFr);

        $langueEn = new Langue();
        $langueEn->setNom('Anglais');
        $manager->persist($langueEn);

        $categorieHistoire = new Categorie();
        $categorieHistoire->setNom('Historique');
        $manager->persist($categorieHistoire);

        $categorieAventure = new Categorie();
        $categorieAventure->setNom('Aventure');
        $manager->persist($categorieAventure);

        $categoriePolicier = new Categorie();
        $categoriePolicier->setNom('Policier');
        $manager->persist($categoriePolicier);

        $auteurHugo = new Auteur();
        $auteurHugo->setNom('Victor Hugo');
        $manager->persist($auteurHugo);

        $auteurDumas = new Auteur();
        $auteurDumas->setNom('Alexandre Dumas');
        $manager->persist($auteurDumas);

        $auteurChristie = new Auteur();
        $auteurChristie->setNom('Agatha Christie');
        $manager->persist($auteurChristie);

        $admin = new User();
        $admin->setEmail('admin@biblioconnect.test');
        $admin->setPrenom('Admin');
        $admin->setNom('System');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $librarian = new User();
        $librarian->setEmail('librarian@biblioconnect.test');
        $librarian->setPrenom('Sophie');
        $librarian->setNom('Bibliothécaire');
        $librarian->setRoles(['ROLE_LIBRARIAN']);
        $librarian->setPassword($this->passwordHasher->hashPassword($librarian, 'librarian123'));
        $manager->persist($librarian);

        $user = new User();
        $user->setEmail('user@biblioconnect.test');
        $user->setPrenom('Jean');
        $user->setNom('Dupont');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        $livre1 = new Livre();
        $livre1->setTitre('Les Misérables');
        $livre1->setAuteur($auteurHugo);
        $livre1->setLangue($langueFr);
        $livre1->addCategory($categorieHistoire);
        $livre1->setDescription('Un roman épique sur la condition humaine, la rédemption et la justice sociale.');
        $livre1->setStock(4);
        $manager->persist($livre1);

        $livre2 = new Livre();
        $livre2->setTitre('Les Trois Mousquetaires');
        $livre2->setAuteur($auteurDumas);
        $livre2->setLangue($langueFr);
        $livre2->addCategory($categorieAventure);
        $livre2->setDescription('Aventures de d\'Artagnan et de ses amis mousquetaires à Paris.');
        $livre2->setStock(3);
        $manager->persist($livre2);

        $livre3 = new Livre();
        $livre3->setTitre('Le Crime de l\'Orient-Express');
        $livre3->setAuteur($auteurChristie);
        $livre3->setLangue($langueEn);
        $livre3->addCategory($categoriePolicier);
        $livre3->setDescription('Un mystère policier célèbre avec le détective Hercule Poirot.');
        $livre3->setStock(5);
        $manager->persist($livre3);

        $reservation = new Reservation();
        $reservation->setUtilisateur($user);
        $reservation->setLivre($livre1);
        $reservation->setStatus('en attente');
        $reservation->setDateReservation(new \DateTimeImmutable());
        $manager->persist($reservation);

        $favori = new Favori();
        $favori->setUtilisateur($user);
        $favori->setLivre($livre2);
        $manager->persist($favori);

        $manager->flush();
    }
}
