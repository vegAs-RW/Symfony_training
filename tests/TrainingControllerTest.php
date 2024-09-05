<?php

namespace Functionnal\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Training;
use App\Entity\Module;
use App\Entity\School;

class TrainingControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        // Initialisation du client
        $this->client = static::createClient();

        // Récupération de l'EntityManager via le client
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        // Nettoyage de la base de données après chaque test
        $this->entityManager->createQuery('DELETE FROM App\Entity\Training')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Module')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\School')->execute();
        $this->entityManager->clear();
        $this->entityManager->close();

        parent::tearDown();
        $this->entityManager = null;
        $this->client = null;
    }

    public function testListTrainingsWithNoSelectedModules()
    {
        $school = $this->createAndPersistSchool('School 1');
        $this->createAndPersistTraining('Training 1', [], $school);
        $this->createAndPersistTraining('Training 2', [], $school);

        $crawler = $this->client->request('GET', '/search_training');

        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $crawler->filter('table tbody tr'), 'Expected two training rows in the table');
    }

    public function testListTrainingsWithMatchAnyModuleTrue()
    {
        $school = $this->createAndPersistSchool('School 1');
        $module1 = $this->createAndPersistModule('Module 1');
        $module2 = $this->createAndPersistModule('Module 2');
        $training = $this->createAndPersistTraining('Training Test', [$module1], $school);

        $crawler = $this->client->request('GET', '/search_training', [
            'modules' => [$module1->getId(), $module2->getId()],
            'match_any_module' => true
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count(), 'Expected some training rows in the table');
        $this->assertSelectorTextContains('td', $training->getName());
    }

    public function testListTrainingsWithMatchAnyModuleFalse()
    {
        $school = $this->createAndPersistSchool('School 1');
        $module1 = $this->createAndPersistModule('Module 1');
        $module2 = $this->createAndPersistModule('Module 2');
        $training = $this->createAndPersistTraining('Training Test', [$module1, $module2], $school);

        $crawler = $this->client->request('GET', '/search_training', [
            'modules' => [$module1->getId(), $module2->getId()],
            'match_any_module' => false
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count(), 'Expected some training rows in the table');
        $this->assertSelectorTextContains('td', $training->getName());
    }

    public function testNoTrainingsWithMatchAnyModuleFalse()
    {
        $school = $this->createAndPersistSchool('School 1');
        $module1 = $this->createAndPersistModule('Module 1');
        $module2 = $this->createAndPersistModule('Module 2');
        $this->createAndPersistTraining('Training Test', [$module1], $school); // Only module1

        $crawler = $this->client->request('GET', '/search_training', [
            'modules' => [$module1->getId(), $module2->getId()],
            'match_any_module' => false
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertEquals(0, $crawler->filter('table tbody tr')->count(), 'Expected no training rows in the table');
        $this->assertSelectorTextContains('p', 'Aucune formation ne dispense ce module');
    }

    private function createAndPersistSchool(string $name): School
    {
        $school = new School();
        $school->setName($name);
        $school->setDescription('Description of ' . $name);
        $this->entityManager->persist($school);
        $this->entityManager->flush();

        return $school;
    }

    private function createAndPersistModule(string $name): Module
    {
        $module = new Module();
        $module->setName($name);
        $module->setDescription('Description of ' . $name);
        $this->entityManager->persist($module);
        $this->entityManager->flush();

        return $module;
    }

    private function createAndPersistTraining(string $name, array $modules, School $school): Training
    {
        $training = new Training();
        $training->setName($name);
        $training->setDescription('Description of ' . $name);
        $training->setSchool($school);

        foreach ($modules as $module) {
            $training->addModule($module);
        }

        $this->entityManager->persist($training);
        $this->entityManager->flush();

        return $training;
    }
}
