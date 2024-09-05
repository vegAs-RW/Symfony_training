<?php

namespace Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Training;

class TrainingInterfaceTest extends WebTestCase
{
    public function testAccessManageTrainingPage()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $training = $em->getRepository(Training::class)->findOneBy([]);

        $crawler = $client->request('GET', '/manage_training/' . $training->getId());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('h1', 'Manage Training: ' . $training->getName());
        $this->assertSelectorTextContains('body > p', $training->getDescription());
    }

    public function testInitialModulesCount()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $training = $em->getRepository(Training::class)->findOneBy([]);

        $crawler = $client->request('GET', '/manage_training/' . $training->getId());

        $initialModulesCount = $training->getModules()->count();
        $this->assertCount($initialModulesCount, $crawler->filter('body > ul li'), 'There should be the correct number of modules listed.');
    }

    public function testDeleteModule()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $training = $em->getRepository(Training::class)->findOneBy([]);

        $initialModulesCount = $training->getModules()->count();
        $modulesArray = $training->getModules();
        $module = $modulesArray->first();
        
        //$crawler = $client->request('GET', '/manage_training/' . $training->getId() . '/delete_module/' . $module->getId());
        //$this->assertEquals(302, $client->getResponse()->getStatusCode());
        
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $currentModulesCount = $crawler->filter('body > ul li');
        $this->assertCount($initialModulesCount - 1, $currentModulesCount, 'The module should be removed after deletion.');

        $updatedTraining = $em->getRepository(Training::class)->find($training->getId());
        $moduleDeleted = !$updatedTraining->getModules()->contains($module);
        $this->assertTrue($moduleDeleted, 'The module should be removed from the training.');
    }

}
