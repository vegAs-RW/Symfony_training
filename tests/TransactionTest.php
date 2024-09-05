<?php

namespace Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use App\DataFixtures\SchoolFixtures;
use App\Entity\Training;
use App\Entity\Module;
use App\Entity\School;

class TransactionTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $fixture;

    protected function setUp(): void
    {
        // Initialisation du client
        $this->client = static::createClient();

        // Récupération de l'EntityManager via le client
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->entityManager->getConnection()->beginTransaction();
        // Charger les fixtures
        $this->loadFixtures();
    }

    protected function tearDown(): void
    {
        // Nettoyage de la base de données après chaque test
        $this->entityManager->rollBack();
        $this->entityManager->clear();
        $this->entityManager->close();

        parent::tearDown();
        $this->entityManager = null;
        $this->client = null;
    }

    private function loadFixtures()
    {
       $this->fixture = new SchoolFixtures();
       $this->fixture->load($this->entityManager);
    }

    public function testListTrainingsWithNoSelectedModules()
    {
        $crawler = $this->client->request('GET', '/search_training');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(6, $crawler->filter('table tbody tr')->count(), 'Expected at least six training rows in the table');
    }

    public function testListTrainingsWithMatchAnyModuleTrue()
    {
        $modules = $this->entityManager->getRepository(Module::class)->findAll();
        $training = $this->entityManager->getRepository(Training::class)->findOneBy([]);
    
        $crawler = $this->client->request('GET', '/search_training', [
            'modules' => array_map(fn($module) => $module->getId(), $modules),
            'match_any_module' => true
        ]);
    
        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count(), 'Expected some training rows in the table');
    
        // Debug output
        echo $crawler->html();
    
        $this->assertSelectorTextContains('td', $training->getName());
    }

    public function testListTrainingsWithMatchAnyModuleFalse()
{
    $modules = $this->entityManager->getRepository(Module::class)->findAll();
    $training = $this->entityManager->getRepository(Training::class)->findOneBy([]);

    $crawler = $this->client->request('GET', '/search_training', [
        'modules' => array_map(fn($module) => $module->getId(), $modules),
        'match_any_module' => false
    ]);

    $this->assertResponseIsSuccessful();
    $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count(), 'Expected some training rows in the table');

    $this->assertSelectorTextContains('td', $training->getName());
}
    public function testNoTrainingsWithMatchAnyModuleFalse()
    {
        // Directly query the database to fetch module and training data
        $modules = $this->entityManager->getRepository(Module::class)->findAll();

        $crawler = $this->client->request('GET', '/search_training', [
            'modules' => array_map(fn($module) => $module->getId(), $modules),
            'match_any_module' => false
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertEquals(0, $crawler->filter('table tbody tr')->count(), 'Expected no training rows in the table');
        $this->assertSelectorTextContains('p', 'Aucune formation ne dispense ce module');
    }
}
