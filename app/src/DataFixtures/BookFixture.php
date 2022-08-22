<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BookFixture extends Fixture
{
    private $faker;

    public function __construct() {
        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager) {

        for ($i = 0; $i < 5000; $i++) {
            $manager->persist($this->getBook());
        }
        $manager->flush();
    }

    private function getBook(): Book {

        return new Book(
            implode(' ', $this->faker->words(random_int(2, 5))),
            $this->faker->firstName() . ' ' . $this->faker->lastName()
        );
    }
}
