<?php

namespace App\Controller\Api\V1;

use App\Repository\ServicesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/v1", name="api_")
 */
class ApiServicesController extends AbstractController
{
    /**
     * @Route("/services", name="services_list")
     */
    public function index(ServicesRepository $servicesRepository): Response
    {
        return $this->json($servicesRepository->findBy(["is_active" => true]), 200, [], [
            'groups' => ['main'],
        ]);
    }

     /**
     * @Route("/services/{id}", name="services_show")
     */
    public function show(ServicesRepository $servicesRepository,int $id): Response
    {
        return $this->json($servicesRepository->find($id), 200, [], [
            'groups' => ['full'],
        ]);
    }

    
}
