<?php

namespace App\Tests\Unit;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Langue;
use App\Entity\Livre;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthAndBookTest extends KernelTestCase
{
    public function testPasswordHasherValidatesLoginCredentials(): void
    {
        self::bootKernel();
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('test.login@example.com');
        $user->setPrenom('Test');
        $user->setNom('Login');

        $encoded = $hasher->hashPassword($user, 'Secret123!');
        $user->setPassword($encoded);

        $this->assertTrue($hasher->isPasswordValid($user, 'Secret123!'));
        $this->assertFalse($hasher->isPasswordValid($user, 'WrongPassword'));
    }

    public function testLivreEntityCanBeCreatedAndRead(): void
    {
        $auteur = new Auteur();
        $auteur->setNom('Victor Hugo');

        $langue = new Langue();
        $langue->setNom('Français');

        $categorie = new Categorie();
        $categorie->setNom('Historique');

        $livre = new Livre();
        $livre->setTitre('Les Misérables');
        $livre->setAuteur($auteur);
        $livre->setLangue($langue);
        $livre->addCategory($categorie);
        $livre->setDescription('Un roman épique sur la France du XIXe siècle.');
        $livre->setStock(12);

        $this->assertSame('Les Misérables', $livre->getTitre());
        $this->assertSame($auteur, $livre->getAuteur());
        $this->assertSame($langue, $livre->getLangue());
        $this->assertCount(1, $livre->getCategories());
        $this->assertStringContainsString('France', $livre->getDescription());
        $this->assertSame(12, $livre->getStock());
    }
}
