<?php

namespace Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Training;
use App\Entity\Module;

class TrainingTest extends WebTestCase
{
    public function testBasicSearch()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/search_training');

        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $trainings = $em->getRepository(Training::class)->findAll();
        $modules = $em->getRepository(Module::class)->findAll();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Recherche de formation par module');
        $this->assertCount( count($trainings) , $crawler->filter('tbody > tr'));

        $this->assertCount( count($modules) + 1  , $crawler->filter('label'));
    }

    public function testSearchWithModule()
    {
        $moduleId = 3;
        $client = static::createClient();
        $crawler = $client->request('GET', '/search_training?modules[]='.$moduleId );

        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $trainings = $em->getRepository(Training::class)->findByModules([$moduleId]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Recherche de formation par module');
        $this->assertCount( count($trainings) , $crawler->filter('tbody > tr'));
    }

    public function testSearchAnyModule()
    {
        $moduleId = 187;
        $client = static::createClient();
        $crawler = $client->request('GET', '/search_training?modules[]='.$moduleId."&match_any_module=1" );

        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $trainings = $em->getRepository(Training::class)->findByAnyModule([$moduleId]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Recherche de formation par module');
        $this->assertCount( count($trainings) , $crawler->filter('tbody > tr'));
    }
}