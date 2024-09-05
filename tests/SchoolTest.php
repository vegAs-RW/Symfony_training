<?php

namespace Functionnal\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\School;

class SchoolTest extends WebTestCase
{

    private $school;
    private $entityManager;
    private $client;
    protected function setUp(): void {

        $client = static::createClient();
        $entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();

        $school = new School();
        $school->setName('ecole test');
        $school->setDescription('description ecole test');

        $entityManager->persist($school);
        $entityManager->flush();

        $this->school = $school;
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    protected function tearDown(): void {
        $this->entityManager->remove($this->school);
        $this->entityManager->flush();
    }
    public function testSchool()
    {
        $school = new School();
        $school->setName("3wa");
        $school->setDescription("devenir dev");

        $this->assertEquals($school->getName(), "3wa");
    }

    public function testSchoolWithDatabase() {
        $client = static::createClient();
        $entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();

        $repository = $entityManager->getRepository(School::class);
        $school = $repository->find(153);

        $this->assertEquals($school->getName(), '3wa');

    }
}
