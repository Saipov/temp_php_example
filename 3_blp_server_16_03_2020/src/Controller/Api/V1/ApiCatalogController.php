<?php

namespace App\Controller\Api\V1;

use App\Repository\CatalogRepository;
use App\Repository\CategoriesRepository;
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
class ApiCatalogController extends AbstractController
{
    /**
     * @Route("/catalog/{id}", name="catalog_list")
     */
    public function index(CatalogRepository $catalogRepository,CategoriesRepository $category,int $id): Response
    {

        $category = $category->find($id);
        $products = $category->getCatalogs();

        return $this->json($products, 200, [],[
            'groups' => ['main'],
        ]);
    }

    /**
     * @Route("/catalog/show/{id}", name="catalog_show")
     */
    public function show(CatalogRepository $catalogRepository,int $id): Response
    {
        return $this->json($catalogRepository->find($id), 200, [], [
            'groups' => ['full'],
        ]);
    }

}
