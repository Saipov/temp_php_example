<?php


namespace App\Service;


use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

class LibPhoneNumber
{
    private PhoneNumberUtil $phone_util;

    /**
     * LibPhoneNumber constructor.
     */
    public function __construct()
    {
        $this->phone_util = PhoneNumberUtil::getInstance();
    }

    /**
     * @param        $phone_number
     * @param string $region
     *
     * @return bool
     */
    public function validate($phone_number, $region = "RU")
    {
        try {
            return $this->phone_util->isValidNumber($this->phone_util->parse($phone_number, $region));
        } catch (NumberParseException $e) {
            return false;
        }
    }


    /**
     * @param        $phone_number
     * @param string $region
     *
     * @return PhoneNumber|null
     */
    public function parsePhoneNumber($phone_number, $region = "RU")
    {
        try {
            return $this->phone_util->parse($phone_number, $region);
        } catch (NumberParseException $e) {
            return null;
        }
    }

    /**
     * @return PhoneNumberUtil
     */
    public function getPhoneUtil(): PhoneNumberUtil
    {
        return $this->phone_util;
    }
}
