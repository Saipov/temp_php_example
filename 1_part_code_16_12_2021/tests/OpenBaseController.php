<?php


namespace App\Tests;


use App\OpenKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OpenBaseController extends WebTestCase
{
    public static KernelBrowser $client;
    static string $access_token = "";

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

		/**
		 * @return string
		 */
		protected static function getKernelClass(): string
		{
				return OpenKernel::class;
		}

		protected function tearDown(): void
		{
				parent::tearDown();
				static::$class = null;
		}
}