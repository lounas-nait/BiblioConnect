<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeAndCatalogueTest extends WebTestCase
{
    public function testHomePageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Votre espace de lecture moderne');
        $this->assertStringContainsString('Gérez vos livres, suivez vos prêts et accédez rapidement à votre compte depuis un seul endroit.', $client->getResponse()->getContent());
    }

    public function testCataloguePageIsSuccessful(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Le driver SQLite n\'est pas disponible dans l\'environnement de test.');
        }

        $client = static::createClient();
        $client->request('GET', '/catalogue');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Catalogue des ouvrages');
        $this->assertStringContainsString('Recherchez par titre, auteur ou catégorie', $client->getResponse()->getContent());
    }
}
