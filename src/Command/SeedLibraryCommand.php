<?php

namespace App\Command;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Entity\Favori;
use App\Entity\Langue;
use App\Entity\Livre;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-library',
    description: 'Crée des comptes de démonstration et remplit le catalogue de la bibliothèque.'
)]
class SeedLibraryCommand extends Command
{
    protected static string $defaultName = 'app:seed-library';
    protected static string $defaultDescription = 'Crée des comptes de démonstration et remplit le catalogue de la bibliothèque.';

    public function __construct(private EntityManagerInterface $entityManager, private UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed de la bibliothèque');

        $userRepository = $this->entityManager->getRepository(User::class);
        $auteurRepository = $this->entityManager->getRepository(Auteur::class);
        $categorieRepository = $this->entityManager->getRepository(Categorie::class);
        $langueRepository = $this->entityManager->getRepository(Langue::class);
        $livreRepository = $this->entityManager->getRepository(Livre::class);

        $admin = $this->createUserIfMissing(
            'admin@biblioconnect.test',
            ['ROLE_ADMIN'],
            'Admin',
            'Super',
            'Admin123!'
        );

        $librarian = $this->createUserIfMissing(
            'librarian@biblioconnect.test',
            ['ROLE_LIBRARIAN'],
            'Librarie',
            'Claire',
            'Librarian123!'
        );

        $user = $this->createUserIfMissing(
            'user@biblioconnect.test',
            ['ROLE_USER'],
            'Demo',
            'Utilisateur',
            'User123!'
        );

        $auteurs = [
            $this->createReferenceIfMissing($auteurRepository, Auteur::class, ['nom' => 'Hugo', 'prenom' => 'Victor']),
            $this->createReferenceIfMissing($auteurRepository, Auteur::class, ['nom' => 'Rowling', 'prenom' => 'J.K.']),
            $this->createReferenceIfMissing($auteurRepository, Auteur::class, ['nom' => 'Camus', 'prenom' => 'Albert']),
        ];

        $categories = [
            $this->createReferenceIfMissing($categorieRepository, Categorie::class, ['nom' => 'Roman']),
            $this->createReferenceIfMissing($categorieRepository, Categorie::class, ['nom' => 'Science-fiction']),
            $this->createReferenceIfMissing($categorieRepository, Categorie::class, ['nom' => 'Histoire']),
        ];

        $langues = [
            $this->createReferenceIfMissing($langueRepository, Langue::class, ['nom' => 'Français']),
            $this->createReferenceIfMissing($langueRepository, Langue::class, ['nom' => 'Anglais']),
        ];

        $livres = [
            [
                'titre' => 'Le Rouge et le Noir',
                'auteur' => $auteurs[0],
                'categories' => [$categories[0], $categories[2]],
                'langue' => $langues[0],
                'image' => 'https://via.placeholder.com/220x320?text=Le+Rouge+et+le+Noir',
                'description' => 'Une immersion dans le destin d’un jeune homme ambitieux à l’ère romantique.',
                'stock' => 12,
            ],
            [
                'titre' => 'Harry Potter à l’école des sorciers',
                'auteur' => $auteurs[1],
                'categories' => [$categories[1]],
                'langue' => $langues[1],
                'image' => 'https://via.placeholder.com/220x320?text=Harry+Potter',
                'description' => 'Le début d’une saga magique et emblématique pour toute la famille.',
                'stock' => 8,
            ],
            [
                'titre' => 'L’Étranger',
                'auteur' => $auteurs[2],
                'categories' => [$categories[0]],
                'langue' => $langues[0],
                'image' => 'https://via.placeholder.com/220x320?text=L%27Etranger',
                'description' => 'Un roman philosophique sur l’absurde et la liberté de l’individu.',
                'stock' => 5,
            ],
        ];

        foreach ($livres as $livreData) {
            $this->createLivreIfMissing($livreRepository, $livreData);
        }

        $this->entityManager->flush();

        $io->success('Données de démonstration créées avec succès.');
        $io->text('Comptes créés :');
        $io->listing([
            'Admin : admin@biblioconnect.test / Admin123!',
            'Bibliothécaire : librarian@biblioconnect.test / Librarian123!',
            'Utilisateur : user@biblioconnect.test / User123!',
        ]);

        return Command::SUCCESS;
    }

    private function createUserIfMissing(string $email, array $roles, string $nom, string $prenom, string $plainPassword): User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
        }

        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        if (!$user->getId()) {
            $this->entityManager->persist($user);
        }

        return $user;
    }

    private function createReferenceIfMissing($repository, string $class, array $criteria)
    {
        $entity = $repository->findOneBy($criteria);
        if (!$entity) {
            $entity = new $class();
            foreach ($criteria as $property => $value) {
                $setter = 'set' . ucfirst($property);
                if (method_exists($entity, $setter)) {
                    $entity->$setter($value);
                }
            }
            $this->entityManager->persist($entity);
        }

        return $entity;
    }

    private function createLivreIfMissing($repository, array $data): Livre
    {
        $livre = $repository->findOneBy(['titre' => $data['titre']]);
        if (!$livre) {
            $livre = new Livre();
            $livre->setTitre($data['titre']);
            $livre->setAuteur($data['auteur']);
            $livre->setLangue($data['langue']);
            $livre->setImage($data['image']);
            $livre->setDescription($data['description']);
            $livre->setStock($data['stock']);
            foreach ($data['categories'] as $categorie) {
                $livre->addCategory($categorie);
            }
            $this->entityManager->persist($livre);
        }

        return $livre;
    }
}
