<?php

namespace App\Controller;

use App\Entity\Works;
use App\Form\WorksType;
use App\Repository\WorksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/works")
 */
class WorksController extends AbstractController
{
    /**
     * @Route("/", name="works_index", methods={"GET"})
     */
    public function index(WorksRepository $worksRepository): Response
    {
        return $this->render('works/index.html.twig', [
            'works' => $worksRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="works_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $work = new Works();
        $form = $this->createForm(WorksType::class, $work);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($work);
            $entityManager->flush();

            return $this->redirectToRoute('works_index');
        }

        return $this->render('works/new.html.twig', [
            'work' => $work,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="works_show", methods={"GET"})
     */
    public function show(Works $work): Response
    {
        return $this->render('works/show.html.twig', [
            'work' => $work,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="works_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Works $work): Response
    {
        $form = $this->createForm(WorksType::class, $work);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('works_index');
        }

        return $this->render('works/edit.html.twig', [
            'work' => $work,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="works_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Works $work): Response
    {
        if ($this->isCsrfTokenValid('delete'.$work->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($work);
            $entityManager->flush();
        }

        return $this->redirectToRoute('works_index');
    }
}
