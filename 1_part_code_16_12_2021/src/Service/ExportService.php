<?php

namespace App\Service;


use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Entity\CommunicationQuality;
use App\Entity\Contact;
use App\Entity\ContactPhone;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Сервис для формирования файлов в формате xlsx (csv), пока заточен под контакты
 *
 * @TODO    Сделать универсальное решение
 *
 * Class ExportService
 *
 * @package App\Service
 */
class ExportService
{

    // Стили для таблиц
    protected array $style = [
        // Стили для шапки таблицы
        'header' => [
            // Выравнивание
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            // Заполнение цветом
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => '00CFCFCF'],
            ]
        ],
    ];

    // Ширина колонки 1px == 0.1428
    protected array $colWidth = [
        '100' => 14.28,
        '150' => 21.42,
        '200' => 28.56,
        '260' => 37.128,
        '300' => 42.84,
        '350' => 49.98
    ];

    protected Spreadsheet $spreadsheet;

    protected array $data = [];

    /**
     * Тип отчета для выгрузки
     *
     * @var string
     */
    protected string $typeReport = "";

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();

        $cacheRedis = new RedisAdapter(
        // Настройка коннекта для теста. Нужно брать из конфига
            RedisAdapter::createConnection('redis://localhost:6379'),
            $namespace = '',
            $defaultLifetime = 600 // 10 мин
        );
        $cache = new Psr16Cache($cacheRedis);
        Settings::setCache($cache);
    }

    /**
     * @param ArrayCollection $data
     * @param string          $typeReport
     *
     * @return $this|ArrayCollection|ExportService
     */
    public function setData(ArrayCollection $data, $typeReport = "export_contact")
    {
        $data = $this->exportCallQuality($data);
        $this->typeReport = $typeReport;

        return $data;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generateXlsx()
    {
        $file = $this->callQualityXlsx();

        return $file ?? null;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generateCsv()
    {
        $file = $this->callQualityCsv();

        return $file ?? null;
    }

    /**
     * Качество связи
     *
     * @param ArrayCollection $data
     *
     * @return ExportService
     */
    private function exportCallQuality(ArrayCollection $data): ExportService
    {

        $criteria[] = [
            "Дата/Время",
            "Сотрудник",
            "Контакт",
            "Телефон, на который звонили",
            "Рейтинг",
            "Страна",
            "Комментарий"
        ];

        /** @var CommunicationQuality $val */
        foreach ($data as $val) {
            $criteria[] = [
                $val->getCreatedAt()->format("d.m.Y H:i:s"),
                $val->getOwner()->getLastName() . ' ' . $val->getOwner()->getFirstName() . ' ' . $val->getOwner()->getMiddleName(),
                $val->getContact()->getLastName() . ' ' . $val->getContact()->getFirstName() . ' ' . $val->getContact()->getMiddleName(),
                $val->getContact()->getPhones()->map(function (ContactPhone $contact) {
                    return $contact->getRaw();
                })[0],
                $val->getRating(),
                !is_null($val->getCountry()) ? $val->getCountry()->getName() : '-',
                $val->getComment() ?? ""
            ];
        }

        $this->data = $criteria;

        return $this;
    }

    /**
     * Качество связи
     *
     * @return false|string
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function callQualityXlsx()
    {
        $this->spreadsheet->createSheet();
        $this->spreadsheet->setActiveSheetIndex(0)->setTitle("Качество связи")
            ->fromArray($this->data)
            ->freezePane('A2');

        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setSelectedCell("A1");

        // Устанавливаем стили листа
        $sheet->getColumnDimension("A")->setWidth($this->colWidth['150']);
        $sheet->getColumnDimension("B")->setWidth($this->colWidth['150']);
        $sheet->getColumnDimension("C")->setWidth($this->colWidth['200']);
        $sheet->getColumnDimension("D")->setWidth($this->colWidth['150']);
        $sheet->getColumnDimension("E")->setWidth($this->colWidth['100']);
        $sheet->getColumnDimension("F")->setWidth($this->colWidth['150']);
        $sheet->getColumnDimension("G")->setWidth($this->colWidth['150']);

        $hColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $hColumn . '1')->applyFromArray($this->style['header']);

        // Перенос по словам
        $hRow = $sheet->getHighestRow();

        // Comments
        $sheet->getStyle('G2:G' . $hRow)->getAlignment()->setWrapText(true);

        $writer = new Xlsx($this->spreadsheet);
        $writer->setPreCalculateFormulas(false);

        $tempFile = tempnam(sys_get_temp_dir(), "contacts_");
        $writer->save($tempFile);

        // Чистим память
        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        return $tempFile;
    }


    /**
     * @return false|string
     * @throws Exception
     */
    private function callQualityCsv()
    {
        $this->spreadsheet->createSheet();
        $this->spreadsheet->setActiveSheetIndex(0)->fromArray($this->data);

        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setSelectedCell("A1");

        $writer = new Csv($this->spreadsheet);
        $writer->setPreCalculateFormulas(false);

        $tempFile = tempnam(sys_get_temp_dir(), "call_quality_");
        $writer->save($tempFile);

        // Чистим память
        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        return $tempFile;
    }


}
