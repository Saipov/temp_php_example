<?php


namespace App\Validator\RequestResolver;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

interface RequestResolverInterface
{
    public function support(Request $request): bool;

    public function resolve(Request $request, Security $security): void;
}