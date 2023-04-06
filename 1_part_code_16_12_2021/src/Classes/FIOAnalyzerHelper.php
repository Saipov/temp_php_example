<?php


namespace App\Classes;

use nomelodic\NCL\lib\NCL;
use nomelodic\NCL\NCLNameCaseRu;

/**
 * Класс помощник, позволяет определить фамилию, имя, отчества из набора слов
 * и вывести в нужном формате
 * Документация: http://namecaselib.com/ru/book/
 *
 * Class FIOAnalyzer
 *
 * @package App\Classes
 */
class FIOAnalyzerHelper
{

    // приставки к тюркским отчествам
    private array $prefixMiddle = ["ОГЛЫ", "ОГЛУ", "УЛЫ", "УУЛУ", "КЫЗЫ", "ГЫЗЫ"];

    /**
     * @var NCLNameCaseRu
     */
    private NCLNameCaseRu $NCLNameCaseRu;

    /**
     * FIOAnalyzer constructor.
     */
    public function __construct()
    {
        $this->NCLNameCaseRu = new NCLNameCaseRu();
    }

    /**
     * @param string|null $fullName
     * @param bool        $uppercase
     *
     * @return array|string[]
     */
    public function fioNormalizer(string $fullName, bool $uppercase = true): array
    {
        $fioNormal = [
            "last_name" => "",
            "first_name" => "",
            "middle_name" => ""
        ];

        if ($fullName) {
            // Чистим строку от всех символов кроме букв и пробела
            $fullName = preg_replace("/[^А-яёЁA-z\s+]/su", "", $fullName);

            if ($uppercase) {
                $fullName = mb_strtoupper(trim($fullName));
            }

            $fullFIO = $this->clearPrefixMiddle($fullName);

            // Склоняем слово любыми методами
            $this->NCLNameCaseRu->fullReset()->q($fullFIO["fio"]);

            $fullName = $this->NCLNameCaseRu->getWordsArray();

            $fioNormal = [];

            // S – фамилия F – отчество N – имя
            foreach ($fullName as $item) {
                if ($item->getNamePart() === "S" && $item->getNameCase(NCL::$IMENITLN)) {
                    $fioNormal["last_name"] = $item->getNameCase(NCL::$IMENITLN);
                }

                if ($item->getNamePart() === "N" && $item->getNameCase(NCL::$IMENITLN)) {
                    $fioNormal["first_name"] = $item->getNameCase(NCL::$IMENITLN);
                }

                if ($item->getNamePart() === "F" && $item->getNameCase(NCL::$IMENITLN)) {
                    if ($fullFIO["index"] !== false) {
                        $fioNormal["middle_name"] = $item->getNameCase(NCL::$IMENITLN) . " " . $this->prefixMiddle[$fullFIO["index"]];
                    } else {
                        $fioNormal["middle_name"] = $item->getNameCase(NCL::$IMENITLN);
                    }
                }
            }

            // Если не смогли исправить ФИО – то возвращаем пустую строку
            $fioNormal = [
                "last_name" => $fioNormal["last_name"] ?? "",
                "first_name" => $fioNormal["first_name"] ?? "",
                "middle_name" => $fioNormal["middle_name"] ?? ""
            ];
        }

        return $fioNormal;
    }

    /**
     * @param string $fullName
     *
     * @return array
     */
    private function clearPrefixMiddle(string $fullName): array
    {
        $index = null;
        foreach ($this->prefixMiddle as $key => $value) {
            // Может быть 0 позиция, поэтому проверяем так
            if (mb_stripos($fullName, $value) !== false) {
                $index = $key;
            }
        }

        // Может быть 0, поэтому проверяем строго
        if ($index !== false && !is_null($index)) {
            $clearFIO = str_ireplace($this->prefixMiddle[$index],"", $fullName);
            // Удаляем все лишние пробелы
            $clearFIO = preg_replace("/^ +| +$|( ) +/m", "$1", $clearFIO);
            return ["fio" => $clearFIO, "index" => $index];
        }

        return ["fio" => $fullName, "index" => false];
    }
}
