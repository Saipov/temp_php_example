<?php

namespace App\MessageHandler;

use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use PhpOffice as PhpOffice;
use Psr\Log\LoggerInterface;
use App\Classes\FIOAnalyzerHelper;
use App\Entity as Entity;
use App\Exception\Http\MimeTypeException;
use App\Message\ImportContact;
use App\Service\SSEPublisherService;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;


/**
 * Передача контактов другому пользователю.
 */
final class ImportContactsHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;
    private FIOAnalyzerHelper $fioAnalyzer;
    private PhoneNumberUtil $phoneNumberUtil;
    private array $tags = [];
    private array $columnMap = [
        "fio" => "A",
        "tags" => "C",
        "phones" => "B",
        "city" => "F",
        "region" => "G",
        "address" => "H",
        "notes" => "I",
    ];
    private SSEPublisherService $SSEPublisher;
    private int $usleep = 0;
    private $lastWriteTime = 0;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface    $messageBus,
        SSEPublisherService    $SSEPublisher,
        LoggerInterface        $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->SSEPublisher = $SSEPublisher;
        $this->logger = $logger;
        $this->fioAnalyzer = new FIOAnalyzerHelper();
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * @param ImportContact $message
     *
     * @throws MimeTypeException
     */
    public function __invoke(ImportContact $message)
    {
        try {
            $this->importProcess($message);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage(), [
                "code" => $exception->getCode(),
                "file" => $exception->getFile(),
                "line" => $exception->getLine()
            ]);

            $message_error = "Процесс импорта завершился неудачей!";

            $this->SSEPublisher->emit("contacts-import-process", [
                "message" => $message_error,
                "status" => "failure"
            ], $message->getUserId());

            // UnrecoverableMessageHandlingException позволит избежать повторных попыток
            throw new UnrecoverableMessageHandlingException($message_error);
        }
    }

    private function importProcess(ImportContact $message)
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $owner = $this->entityManager->getRepository(Entity\User::class)->find($message->getUserId());

        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($message->getPathFileName());

        // Данные превращаются в массив
        $rowIterator = $spreadsheet->getActiveSheet()->getRowIterator(2);

        $forCleaning = []; // Массив объектов для уничтожения в процессе операции.
        $batchSize = 20;
        $i = 1;
        $total = $spreadsheet->getActiveSheet()->getHighestDataRow(); // Общее количество строк в активной книге.
        $position = 0;

        switch ($total) {
            case ($total <= 100):
            {
                $this->usleep = 800;
                break;
            }
            case ($total > 100 && $total < 500):
            {
                $this->usleep = 400;
                break;
            }
        }

        //<editor-fold desc="Перебор строк активной книги.">
        while ($rowIterator->valid()) {
            $this->progress($position, $total, $message->getUserId());

            $row = $rowIterator->current();
            $forCleaning[] = $row;

            $cellIterator = $row->getCellIterator();
            $forCleaning[] = $cellIterator;

            $contact = new Entity\Contact($owner->getOrganization(), $owner);
            $forCleaning[] = $contact;

            $contact->setOrganization($owner->getOrganization());
            $contact->setOwner($owner);

            while ($cellIterator->valid()) {
                $cell = $cellIterator->current();
                $forCleaning[] = $cell;

                switch ($cell->getColumn()) {
                    case $this->columnMap["fio"]:
                    {
                        if (!empty($cell->getValue())) {
                            $nameArray = $this->fioAnalyzer->fioNormalizer($cell->getValue());
                            $contact->setFirstName($nameArray["first_name"]);
                            $contact->setLastName($nameArray["last_name"]);
                            $contact->setMiddleName($nameArray["middle_name"]);
                        }
                        break;
                    }
                    case $this->columnMap["phones"]:
                    {
                        $phoneNumbers = explode(",", $cell->getValue());
                        $phoneNumbers = array_map("trim", $phoneNumbers);

                        foreach ($phoneNumbers as $number) {
                            $pn = $this->phoneNumberUtil->parse($number, "RU"); // TODO: Пока только Россия.

                            $phoneNumber = new Entity\ContactPhone($contact);
                            $phoneNumber->setLabel("Персональный");
                            $phoneNumber->setRaw($this->phoneNumberUtil->format($pn, PhoneNumberFormat::E164));
                            $phoneNumber->setInternational($this->phoneNumberUtil->format($pn, PhoneNumberFormat::INTERNATIONAL));
                            $phoneNumber->setCountryCode("RU"); // TODO: Пока только Россия.
                            $phoneNumber->setCountryCallingCode($pn->getCountryCode()); // TODO: Пока только Россия.

                            $forCleaning[] = $phoneNumber;

                            $contact->getPhones()->add($phoneNumber);

                            unset($pn);
                        }
                        break;
                    }
                    case $this->columnMap["region"]:
                    {
                        $contact->setRegion($cell->getValue());
                        break;
                    }
                    case $this->columnMap["city"]:
                    {
                        $contact->setCity($cell->getValue());
                        break;
                    }
                    case $this->columnMap["address"]:
                    {
                        $contact->setAddress($cell->getValue());
                        break;
                    }
                    case $this->columnMap["notes"]:
                    {
                        $contact->setNotes($cell->getValue());
                        break;
                    }
                    case $this->columnMap["tags"]:
                    {
                        if (is_string($cell->getValue()) and !empty($cell->getValue())) {
                            $tagNames = explode(",", $cell->getValue());
                            $tagNames = array_map(function ($e) {
                                return trim($e);
                            }, $tagNames);

                            foreach ($tagNames as $tagName) {
                                $contactTag = $this->getTag($tagName);
                                $contactTag->setOrganization($contact->getOrganization());
                                $contact->getTags()->add($contactTag);
                            }

                            unset($tagNames);
                        }
                        break;
                    }
                }

                $cellIterator->next();
            }

            unset($cellIterator);

            $contact->setDefaultPhone($contact->getPhones()->first());
            $this->setTimeZone($contact);

            $this->entityManager->persist($contact);

            if (($i % $batchSize) === 0) {
                try {
                    $this->entityManager->flush();
                } catch (Exception $exception) {
                    $this->SSEPublisher->emit("contacts-import-process", [
                        "message" => $exception->getMessage(),
                        "status" => "failure",
                        "total" => $total,
                        "position" => $position,
                        "percent" => 100,
                    ], $message->getUserId());
                }

                // Уничтожить не используемые объекты.
                foreach ($forCleaning as $key => $item) {
                    unset($forCleaning[$key]);
                }

                gc_collect_cycles();
            }

            $i++;
            $position++;

            $rowIterator->next();

            if ($this->usleep > 0) {
                usleep($this->usleep);
            }
        }
        //</editor-fold>

        // Сохранять объекты, которые не составляли целую партию
        try {
            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->SSEPublisher->emit("contacts-import-process", [
                "message" => "$position / $total",
                "status" => "success",
                "total" => $total,
                "position" => $position,
                "percent" => 100,
            ], $message->getUserId());
        } catch (Exception $exception) {
            $this->SSEPublisher->emit("contacts-import-process", [
                "message" => $exception->getMessage(),
                "status" => "failure",
                "total" => $total,
                "position" => $position,
                "percent" => 100,
            ], $message->getUserId());
        }


        // Очистка книги из памяти
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $rowIterator, $owner);

        gc_collect_cycles();
    }

    /**
     * @param string $name
     *
     * @return \App\Entity\ContactTag
     */
    private function getTag(string $name): Entity\ContactTag
    {
        if (key_exists($name, $this->tags)) {
            return $this->tags[$name];
        }

        $contactTag = $this->entityManager
            ->getRepository(Entity\ContactTag::class)
            ->findOneBy(["name" => $name]);

        if (!$contactTag instanceof Entity\ContactTag) {
            $contactTag = new Entity\ContactTag();
            $contactTag->setName($name);
            $contactTag->setColor("#" . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, "0", STR_PAD_LEFT));
        }

        return $this->tags[$name] = $contactTag;
    }

    /**
     * @param int $position
     * @param int $total
     * @param int $user_id
     */
    private function progress(int $position, int $total, int $user_id)
    {
        if ($position == $total) {
            $this->sendProgress($position, $total, 100, $user_id);
            return;
        }

        $percent = round(($position * 100) / $total);
        $timeInterval = round((microtime(true) - $this->lastWriteTime));
        if ($timeInterval > 0.5) {
            $this->lastWriteTime = microtime(true);
            $this->sendProgress($position, $total, $percent, $user_id);
        }
    }

    /**
     * @param $position
     * @param $total
     * @param $percent
     * @param $user_id
     */
    private function sendProgress($position, $total, $percent, $user_id)
    {
        $this->SSEPublisher->emit("contacts-import-process", [
            "message" => "$position / $total",
            "status" => "progress",
            "total" => $total,
            "position" => $position,
            "percent" => $percent,
        ], $user_id);
    }

    /**
     * @param \App\Entity\Contact $contact
     *
     * @return void
     */
    private function setTimeZone(Entity\Contact $contact)
    {
        /** @var \App\Entity\ContactPhone $phoneNumber */
        $phoneNumber = $contact->getPhones()->first();

        if ($phoneNumber instanceof Entity\ContactPhone) {
            $ccc = $phoneNumber->getCountryCallingCode();
            $raw = $phoneNumber->getRaw();

            $number = str_replace("+$ccc", "" ,$raw);

            $phoneNumberPlan = $this->entityManager
                ->getRepository(Entity\PhoneNumbersPlan::class)
                ->getTimeZoneByPhones((int)$number);

            if ($phoneNumberPlan instanceof Entity\PhoneNumbersPlan) {
                $contact->setTimezone($phoneNumberPlan->getTimezone() ?? new DateTimeZone("UTC"));
            }
        }
    }
}
