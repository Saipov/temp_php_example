<?php


namespace App\Classes;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RequestProcessor
 *
 * @package App\Classes
 */
class RequestProcessor
{
    /**
     * @var RequestStack
     */
    protected RequestStack $request;

    /**
     * RequestProcessor constructor.
     *
     * @param RequestStack $request
     */
    public function __construct(RequestStack $request)
    {
        $this->request = $request;
    }

    /**
     * @param array $record
     * @return array
     */
    public function processRecord(array $record): array
    {
        $req = $this->request->getCurrentRequest();
        $record["extra"]["query_string"] = $req->getQueryString();
        $record["extra"]["user-agent"] = $req->headers->get("User-Agent");
        $record["extra"]["request"] = $req->request->all();

        return $record;
    }
}
