<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-librarian',
    description: 'Crée un compte bibliothécaire depuis la CLI Symfony.',
)]
class CreateLibrarianCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Adresse email du bibliothécaire')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe du bibliothécaire')
            ->addArgument('prenom', InputArgument::OPTIONAL, 'Prénom du bibliothécaire')
            ->addArgument('nom', InputArgument::OPTIONAL, 'Nom du bibliothécaire')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email') ?: $io->ask('Adresse email du bibliothécaire', 'librarian@biblioconnect.test');
        $password = $input->getArgument('password') ?: $io->askHidden('Mot de passe du bibliothécaire (écran masqué)');
        $prenom = $input->getArgument('prenom') ?: $io->ask('Prénom du bibliothécaire', 'Librarie');
        $nom = $input->getArgument('nom') ?: $io->ask('Nom du bibliothécaire', 'Claire');

        if (!$password) {
            $io->error('Le mot de passe ne peut pas être vide.');
            return Command::FAILURE;
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        $existing = $userRepository->findOneBy(['email' => $email]);

        if ($existing) {
            $io->warning(sprintf('Un utilisateur existe déjà avec l\'email %s. Ses rôles seront mis à jour.', $email));
            $user = $existing;
        } else {
            $user = new User();
            $user->setEmail($email);
        }

        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setRoles(['ROLE_LIBRARIAN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        if (!$existing) {
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Compte bibliothécaire créé : %s', $email));
        $io->text([
            'Rôle : ROLE_LIBRARIAN',
            'Connexion possible via /login',
        ]);

        return Command::SUCCESS;
    }
}
