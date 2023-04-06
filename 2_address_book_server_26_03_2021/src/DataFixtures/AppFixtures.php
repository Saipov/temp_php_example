<?php

namespace App\DataFixtures;

use App\Entity\AddressType;
use App\Entity\PhoneType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Тип телефонных номеров
        $phoneTypeData = ["Домашний", "Рабочий"];

        foreach ($phoneTypeData as $value) {
            $phoneType = new PhoneType();
            $phoneType->setPhoneType($value);
            $manager->persist($phoneType);
        }

        //Заполняем типы адресов: юридический, фактический и почтовый адреса
        $addressTypeData = ["Юридический", "Фактический", "Почтовый адрес"];

        foreach ($addressTypeData as $value) {
            $addressType = new AddressType();
            $addressType->setAddressType($value);
            $manager->persist($addressType);
        }

        $manager->flush();
    }
}
