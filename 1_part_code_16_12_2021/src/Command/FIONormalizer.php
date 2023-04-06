<?php


namespace App\Command;

use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Classes\FIOAnalyzerHelper;
use App\Entity\Contact;
use App\Exception\Http\ConflictException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class FIONormalizer extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * FIONormalizer constructor.
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
            ->setName("fio:normalizer")
            ->setDescription("Приведение в порядок ФИО контактов")
            ->setHelp("Эта команда позволит вам привести контакты в правильный порядок имен. Фамилия Имя Отчество");
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
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'ВНИМАНИЕ: СДЕЛАЙТЕ БЭКАП БД! Вы уверены, что хотите запустить процесс исправления ФИО и у вас сделан бэкап базы данных? [y/n]',
            false,
            '/^(y|да)/i'
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }
        // Чистим консоль
        $output->write(sprintf("\033\143"));

        $usersIterable = $this->entityManager
            ->createQueryBuilder()
            ->select("c")
            ->from(Contact::class, "c")
            ->getQuery()
            ->toIterable();

        $contactCount = $this->entityManager
            ->createQueryBuilder()
            ->select("count(c.id)")
            ->from(Contact::class, "c")
            ->getQuery()
            ->getSingleScalarResult();

        $progressBar = new ProgressBar($output, $contactCount);
        $progressBar->setBarCharacter('<comment>▉</comment>');
        $progressBar->setEmptyBarCharacter('░');
        $progressBar->setProgressCharacter('▉');
        $progressBar->setBarWidth($contactCount);
        $progressBar->start();

        // Старт транзакции
        $this->entityManager->getConnection()->beginTransaction();
        try {

            $fioInstance = new FIOAnalyzerHelper();
            foreach ($usersIterable as $k => $row) {

                $fio = "{$row->getLastName()} {$row->getFirstName()} {$row->getMiddleName()}";

                $fioNormal = $fioInstance->fioNormalizer($fio);

                $row->setFirstName($fioNormal["first_name"]);
                $row->setLastName($fioNormal["last_name"]);
                $row->setMiddleName($fioNormal["middle_name"]);

                $progressBar->advance();
            }
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

        } catch (ConnectionException $e) {
            $this->entityManager->getConnection()->rollBack();
            throw new ConflictException("Contact update error", "update_contacts_error");
        }

        $progressBar->finish();

        return 0;
    }
}
