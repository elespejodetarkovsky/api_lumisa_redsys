<?php

namespace App\DataFixtures;

use App\Factory\ApiTokenFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
//        UserFactory::createOne([
//            'email' => 'bernie@dragonmail.com',
//            'password' => 'roar',
//        ]);

        UserFactory::createMany(10);

        ApiTokenFactory::createMany(30, function () {
            return [
                'ownedBy' => UserFactory::random(),
            ];
        });
    }
}