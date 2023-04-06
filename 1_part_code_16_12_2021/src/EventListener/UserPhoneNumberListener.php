<?php


namespace App\EventListener;


use libphonenumber\NumberParseException;

/**
 * Class UserPhoneNumberListener
 *
 * @package App\Listeners
 */
class UserPhoneNumberListener
{
    /**
     * @param $entity
     *
     * @throws NumberParseException
     */
    public function prePersist($entity)
    {
//        if ($entity instanceof User) {
//            $util = PhoneNumberUtil::getInstance();
//            $phoneNumber = $util->parse($entity->getPhone());
//            $entity->setPhone($util->format($phoneNumber, PhoneNumberFormat::E164));
//        }
    }
}