<?php


namespace App\Command;

use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Entity\PhoneNumbersPlan;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @class DownloadPhoneNumbersPlan
 */
class DownloadPhoneNumbersPlan extends Command
{

    /**
     * Ссылка на файлы с данными
     */
    const CSV_LINKS =
        "";

    const REGION_TIMEZONE_MAP = [
        "Абзелиловский район * Белорецкий район|Абзелиловский район * Белорецкий район" => "Asia/Yekaterinburg",
        "Алтайский край" => "Asia/Krasnoyarsk",
        "Амурская обл." => "Asia/Yakutsk",
        "Архангельская обл." => "Europe/Moscow",
        "Архангельская область * Ненецкий автономный округ" => "Europe/Moscow",
        "Архангельская область и Ненецкий автономный округ" => "Europe/Moscow",
        "Астраханская обл." => "Europe/Samara",
        "Белгородская обл." => "Europe/Moscow",
        "Брянская обл." => "Europe/Moscow",
        "Владимирская обл." => "Europe/Moscow",
        "Волгоградская обл." => "Europe/Volgograd",
        "Вологодская обл." => "Europe/Moscow",
        "Воронежская обл." => "Europe/Moscow",
        "г. Москва * Московская область" => "Europe/Moscow",
        "г. Москва и Московская область" => "Europe/Moscow",
        "г. Норильск|Красноярский край" => "Asia/Krasnoyarsk",
        "г. Псков|Псковская обл." => "Europe/Moscow",
        "г. Санкт-Петербург * Ленинградская область" => "Europe/Moscow",
        "г. Санкт-Петербург и Ленинградская область" => "Europe/Moscow",
        "г. Севастополь" => "Europe/Simferopol",
        "г. Сочи|Краснодарский край" => "Europe/Moscow",
        "Еврейская автономная обл." => "Asia/Vladivostok",
        "Забайкальский край" => "Asia/Yakutsk",
        "Ивановская обл." => "Europe/Moscow",
        "Иркутская обл." => "Asia/Irkutsk",
        "Кабардино-Балкарская Республика" => "Europe/Moscow",
        "Калининградская обл." => "Europe/Kaliningrad",
        "Калужская обл." => "Europe/Moscow",
        "Камчатский край" => "Asia/Kamchatka",
        "Карачаево-Черкесская Республика" => "Europe/Moscow",
        "Кемеровская обл." => "Asia/Novokuznetsk",
        "Кировская обл." => "Europe/Moscow",
        "Корякский округ|Камчатский край" => "Asia/Kamchatka",
        "Костромская обл." => "Europe/Moscow",
        "Краснодарский край" => "Europe/Moscow",
        "Красноярский край" => "Asia/Krasnoyarsk",
        "Курганская обл." => "Asia/Yekaterinburg",
        "Курская обл." => "Europe/Moscow",
        "Липецкая обл." => "Europe/Moscow",
        "Магаданская обл." => "Asia/Magadan",
        "Московская область * Москва|Московская область * Москва" => "Europe/Moscow",
        "Мурманская обл." => "Europe/Moscow",
        "Ненецкий АО" => "Europe/Moscow",
        "Нижегородская обл." => "Europe/Moscow",
        "Новгородская обл." => "Europe/Moscow",
        "Новосибирская обл." => "Asia/Krasnoyarsk",
        "Омская обл." => "Asia/Omsk",
        "Оренбургская обл." => "Asia/Yekaterinburg",
        "Орловская обл." => "Europe/Moscow",
        "Пензенская обл." => "Europe/Moscow",
        "Пермский край" => "Asia/Yekaterinburg",
        "Приморский край" => "Asia/Vladivostok",
        "Псковская обл." => "Europe/Moscow",
        "Республика Адыгея" => "Europe/Moscow",
        "Республика Алтай" => "Asia/Novokuznetsk",
        "Республика Башкортостан" => "Asia/Yekaterinburg",
        "Республика Бурятия" => "Asia/Irkutsk",
        "Республика Дагестан" => "Europe/Moscow",
        "Республика Ингушетия" => "Europe/Moscow",
        "Республика Кабардино-Балкарская" => "Europe/Moscow",
        "Республика Калмыкия" => "Europe/Moscow",
        "Республика Карачаево-Черкесская" => "Europe/Moscow",
        "Республика Карелия" => "Europe/Moscow",
        "Республика Коми" => "Europe/Moscow",
        "Республика Крым" => "Europe/Simferopol",
        "Республика Крым * г. Севастополь" => "Europe/Simferopol",
        "Республика Крым и г. Севастополь" => "Europe/Simferopol",
        "Республика Марий Эл" => "Europe/Moscow",
        "Республика Мордовия" => "Europe/Moscow",
        "Республика Саха /Якутия/" => "Asia/Yakutsk",
        "Республика Северная Осетия - Алания" => "Europe/Moscow",
        "Республика Татарстан" => "Europe/Moscow",
        "Республика Тыва" => "Asia/Krasnoyarsk",
        "Республика Удмуртская" => "Europe/Samara",
        "Республика Хакасия" => "Asia/Krasnoyarsk",
        "Республика Чеченская" => "Europe/Moscow",
        "Российская Федерация" => "Europe/Moscow",
        "Ростовская обл." => "Europe/Moscow",
        "Рязанская обл." => "Europe/Moscow",
        "Самарская обл." => "Europe/Samara",
        "Саратовская обл." => "Europe/Samara",
        "Свердловская обл." => "Asia/Yekaterinburg",
        "Сахалинская обл." => "Asia/Srednekolymsk",
        "Смоленская обл." => "Europe/Moscow",
        "Ставропольский край" => "Europe/Moscow",
        "Сургутский район * г. Сургут" => "Asia/Yekaterinburg",
        "Тамбовская обл." => "Europe/Moscow",
        "Тверская обл." => "Europe/Moscow",
        "Томская обл." => "Asia/Krasnoyarsk",
        "Тульская обл." => "Europe/Moscow",
        "Тюменская обл." => "Asia/Yekaterinburg",
        "Тюменская область" => "Asia/Yekaterinburg",
        "Удмуртская Республика" => "Europe/Samara",
        "Ульяновская обл." => "Europe/Samara",
        "Хабаровский край" => "Asia/Vladivostok",
        "Ханты - Мансийский - Югра АО" => "Asia/Yekaterinburg",
        "Ханты-Мансийский АО - Югра" => "Asia/Yekaterinburg",
        "Челябинская обл." => "Asia/Yekaterinburg",
        "Чеченская Республика" => "Europe/Moscow",
        "Чувашская Республика" => "Europe/Moscow",
        "Чувашская Республика - Чувашия" => "Europe/Moscow",
        "Чукотский АО" => "Asia/Anadyr",
        "Ямало-Ненецкий АО" => "Asia/Yekaterinburg",
        "Ярославская обл." => "Europe/Moscow",
    ];

    private EntityManagerInterface $entityManager;

    /**
     * DownloadPhoneNumbersPlan constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName("app:phone-number-plan:setup")
            ->setDescription("Загрузка/Обновление базы номерных планов")
            ->setHelp("Эта команда позволит вам создать загрузить или обновить базы номерных планов");

    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parsed = $this->parse($this->downloadRegionsDatabase());

        // Чистим базу
        if ($parsed) {
            $this->truncatePhoneNumbersPlan();
        }

        $forCleaning = [];
        $batchSize = 40;
        $rowInsert = 0;
        try {
            foreach ($parsed as $key => $item) {

                $numbersPlan = new PhoneNumbersPlan();
                $numbersPlan->setZoneNumber($item["zoneNumber"]);
                $numbersPlan->setPhoneNumberStart($item["phoneNumberStart"]);
                $numbersPlan->setPhoneNumberEnd($item["phoneNumberEnd"]);
                $numbersPlan->setOperatorName($item["operatorName"]);
                $numbersPlan->setRegion($item["region"]);
                $numbersPlan->setTimezone(new DateTimeZone(self::REGION_TIMEZONE_MAP[$item["region"]] ?? date_default_timezone_get()));

                $forCleaning[] = $numbersPlan;

                $this->entityManager->persist($numbersPlan);

                if (($key % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    foreach ($forCleaning as $k => $value) {
                        unset($forCleaning[$k]);
                    }
                }
                $rowInsert++;
            }
            $this->entityManager->flush();
            $this->entityManager->clear();

            $output->writeln([
                "",
                "<info>База номерных планов успешно создана</info>",
                "<info>====================</info>",
                sprintf("  <info>Всего добавлено объектов: %s</info>", $rowInsert),
                "",
            ]);

        } catch (Exception $exception) {
            $output->writeln([
                sprintf("<error>%s</error>", $exception->getMessage()),
            ]);
            return 1;
        }
        return 0;
    }

    /**
     * Парсим данные
     *
     * @param        $csvString
     * @param string $delimiter
     *
     * @return array
     */
    private function parse($csvString, string $delimiter = ";"): array
    {
        $result = [];
        $lines = explode("\n", $csvString);

        // Убираем названия колонок
        $lines = array_slice($lines, 1);

        foreach ($lines as $line) {
            $lineData = explode($delimiter, $line);
            $lineData = array_map("trim", $lineData);

            // Если нет региона пропускаем итерацию
            if (!isset($lineData[5])) {
                continue;
            }

            $result[] = [
                "zoneNumber" => $lineData[0],
                "phoneNumberStart" => $lineData[0] . $lineData[1],
                "phoneNumberEnd" => $lineData[0] . $lineData[2],
                "operatorName" => $lineData[4],
                "region" => $lineData[5]
            ];
        }
        return $result;
    }

    /**
     * Скачиваем данные
     *
     * @TODO Переделать на symfony http client
     * @return false|string
     * @throws Exception
     */
    private function downloadRegionsDatabase()
    {
        $arrContextOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];

        set_time_limit(600);
        $response = file_get_contents(self::CSV_LINKS, false, stream_context_create($arrContextOptions));

        $status_line = $http_response_header[0];

        preg_match("{HTTP\/\S*\s(\d{3})}", $status_line, $match);

        $status = $match[1];

        if ($status !== "200") {
            throw new Exception("unexpected response status: {$status_line}\n" . $response);
        }

        return $response;
    }

    /**
     * Чистим базу
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function truncatePhoneNumbersPlan()
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeStatement($platform->getTruncateTableSQL("phone_numbers_plan"));
    }
}