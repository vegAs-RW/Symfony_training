<?php

namespace Functionnal\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Training;
use App\Entity\School;
use App\Entity\Module;

class TrainingsTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $createdEntities = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();

        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $fixture = new \App\DataFixtures\SchoolFixtures();
        $fixture->load($this->entityManager);

        // Enregistrer toutes les entités créées par les fixtures
        $this->createdEntities = array_merge(
            $fixture->getCreatedSchools(),
            $fixture->getCreatedModules(),
            $fixture->getCreatedTrainings()
        );
    }

    protected function tearDown(): void
    {
        // Supprimer uniquement les entités créées par les fixtures
        foreach ($this->createdEntities as $entity) {
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function testTrainingHasThreeModules()
    {
        $repository = $this->entityManager->getRepository(Training::class);
        $training = $repository->findOneBy([]);
        $crawler = $this->client->request('GET', '/training' . $training->getId());

        $this->assertNotNull($training, 'Training entity found');
        $this->assertCount(3, $training->getModules(), "the training should have exactly 3 modules");
        // $this->assertCount(3, $crawler->filter('ul li'));
    }   
}
