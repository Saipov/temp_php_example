<?php

namespace App\Controller\Api\V1;

use App\Repository\TechnologiesRepository;
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
class ApiTechnologiesController extends AbstractController
{
    /**
     * @Route("/technologies", name="technologies_list")
     */
    public function index(TechnologiesRepository $technologiesRepository): Response
    {
        return $this->json($technologiesRepository->findBy(["is_active" => true]), 200, [], [
            'groups' => ['main'],
        ]);
    }

     /**
     * @Route("/technologies/{id}", name="technologies_show")
     */
    public function show(TechnologiesRepository $technologiesRepository,int $id): Response
    {
        return $this->json($technologiesRepository->find($id), 200, [], [
            'groups' => ['full'],
        ]);
    }

    
}
