<?php

namespace App\DataFixtures;

use App\Entity\Training;
use App\Entity\Module;
use App\Entity\School;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class SchoolFixtures extends Fixture
{
    private $createdSchools = [];
    private $createdTrainings = [];
    private $createdModules = [];
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        $schools = [];
        for ($i = 0; $i < 3; $i++) {
            $school = new School();
            $school->setName($faker->company());
            $school->setDescription($faker->sentence());
            $manager->persist($school);
            $schools[] = $school;
            $this->createdSchools[] = $school;
        }

        $trainings = [];
        for ($i =0; $i < 6; $i++) {
            $training = new Training();
            $training->setName($faker->jobTitle());
            $training->setDescription($faker->paragraph());
            
            $randomSchool = $schools[array_rand($schools)];
            $training->setSchool($randomSchool);
            $randomSchool->addTraining($training);

            $manager->persist($training);
            $trainings[] = $training;
            $this->createdTrainings[] = $training;
        }

        $modules = [];
        for ($i=0; $i < 6; $i++) {
            $module = new Module();
            $module->setName($faker->word());
            $module->setDescription($faker->paragraph());

            $manager->persist($module);
            $modules[]= $module;
            $this->createdModules[] = $module;
        }

        foreach($trainings as $training) {
            $randomModules = $faker->randomElements($modules, 3);
            foreach($randomModules as $module) {
                $training->addModule($module);
            }
        }

        $manager->flush();
    }

    public function getCreatedSchools(): array
    {
        return $this->createdSchools;
    }

    public function getCreatedTrainings(): array
    {
        return $this->createdTrainings;
    }

    public function getCreatedModules(): array
    {
        return $this->createdModules;
    }
}