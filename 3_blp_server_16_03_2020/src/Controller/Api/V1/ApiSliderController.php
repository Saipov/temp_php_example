<?php

namespace App\Controller\Api\V1;

use App\Repository\SliderRepository;
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
class ApiSliderController extends AbstractController
{
    /**
     * @Route("/slider", name="slider_list")
     */
    public function index(SliderRepository $sliderRepository): Response
    {
        return $this->json($sliderRepository->findBy(["is_active" => true]), 200, [], [
            'groups' => ['main'],
        ]);
    }


    
}
