<?php


namespace App\Tests;


use App\AppKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AppBaseController
 *
 * @package App\Tests
 */
class AppBase extends KernelTestCase
{
    public static KernelBrowser $client;
    static string $access_token = "";

    /**
     * @return string
     */
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }
}