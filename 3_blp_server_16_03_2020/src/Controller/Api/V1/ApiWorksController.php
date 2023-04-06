<?php

namespace App\Controller\Api\V1;

use App\Repository\WorksRepository;
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
class ApiWorksController extends AbstractController
{
    /**
     * @Route("/works", name="works_list")
     */
    public function index(WorksRepository $worksRepository): Response
    {
        return $this->json($worksRepository->findBy(["is_active" => true]), 200, [], [
            'groups' => ['main'],
        ]);
    }

     /**
     * @Route("/works/{id}", name="works_show")
     */
    public function show(WorksRepository $worksRepository,int $id): Response
    {
        return $this->json($worksRepository->find($id), 200, [], [
            'groups' => ['full'],
        ]);
    }

    
}
