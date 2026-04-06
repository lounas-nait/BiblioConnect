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
    name: 'app:create-admin',
    description: 'Crée un compte administrateur depuis la CLI Symfony.',
)]
class CreateAdminCommand extends Command
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
            ->addArgument('email', InputArgument::OPTIONAL, 'Adresse email de l\'admin')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe de l\'admin')
            ->addArgument('prenom', InputArgument::OPTIONAL, 'Prénom de l\'admin')
            ->addArgument('nom', InputArgument::OPTIONAL, 'Nom de l\'admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email') ?: $io->ask('Adresse email de l\'administrateur', 'admin@biblioconnect.test');
        $password = $input->getArgument('password') ?: $io->askHidden('Mot de passe de l\'administrateur (écran masqué)');
        $prenom = $input->getArgument('prenom') ?: $io->ask('Prénom de l\'administrateur', 'Admin');
        $nom = $input->getArgument('nom') ?: $io->ask('Nom de l\'administrateur', 'Super');

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
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        if (!$existing) {
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Compte administrateur créé : %s', $email));
        $io->text([
            'Rôle : ROLE_ADMIN',
            'Connexion possible via /login',
        ]);

        return Command::SUCCESS;
    }
}
