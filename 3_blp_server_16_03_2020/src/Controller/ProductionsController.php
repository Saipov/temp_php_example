<?php

namespace App\Controller;

use App\Entity\Productions;
use App\Form\ProductionsType;
use App\Repository\ProductionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/settings/productions")
 */
class ProductionsController extends AbstractController
{
    /**
     * @Route("/", name="productions_index", methods={"GET"})
     */
    public function index(ProductionsRepository $productionsRepository): Response
    {
        return $this->render('productions/index.html.twig', [
            'productions' => $productionsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="productions_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $production = new Productions();
        $form = $this->createForm(ProductionsType::class, $production);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($production);
            $entityManager->flush();

            return $this->redirectToRoute('productions_index');
        }

        return $this->render('productions/new.html.twig', [
            'production' => $production,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="productions_show", methods={"GET"})
     */
    public function show(Productions $production): Response
    {
        return $this->render('productions/show.html.twig', [
            'category' => $production,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="productions_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Productions $production): Response
    {
        $form = $this->createForm(ProductionsType::class, $production);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('productions_index');
        }

        return $this->render('productions/edit.html.twig', [
            'category' => $production,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="productions_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Productions $production): Response
    {
        if ($this->isCsrfTokenValid('delete'.$production->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($production);
            $entityManager->flush();
        }

        return $this->redirectToRoute('productions_index');
    }
}
