<?php

namespace App\Controller\Api\V1;

use App\Entity\AddressBook;
use App\Form\AddressBookType;
use App\Form\PhoneType;
use App\Repository\AddressBookRepository;
use App\Repository\AddressTypeRepository;
use App\Repository\PhoneTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Контроллер "Адресная книга"
 *
 * @Route("/api/v1/")
 */
class AddressBookController extends AbstractController
{
    /**
     * @Route("addressbook", name="api_v1_get_address_book", methods={"GET"})
     */
    public function index(
        AddressBookRepository $addressBookRepository,
        PhoneTypeRepository $phoneTypeRepository,
        AddressTypeRepository $addressTypeRepository
    ): Response {
        return $this->json(
            [
                "data" => $addressBookRepository->findAll(),
                "phoneType" => $phoneTypeRepository->findAll(),
                "addressType" => $addressTypeRepository->findAll(),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Создаем новую запись в БД Адресная книга
     *
     * @Route("addressbook", name="api_v1_add_address_book", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $addressBook = new AddressBook();
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(AddressBookType::class, $addressBook);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $addressBook = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($addressBook);
            $em->flush();

            return $this->json($addressBook, Response::HTTP_CREATED);
        }

        return $this->json(['status' => 'error', 'errors' => $form], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Редактируем запись в БД
     *
     * @Route("addressbook/{id}", name="api_v1_edit_address_book", methods={"PUT"})
     *
     * @param Request $request
     * @param AddressBookRepository $addressBookRepository
     * @param string $id
     * @return Response
     */
    public function edit(Request $request, AddressBookRepository $addressBookRepository, string $id): Response
    {
        $data = json_decode($request->getContent(), true);
        $addressBook = $addressBookRepository->findOneBy(['id' => $id]);

        if (!$addressBook) {
            throw new ResourceNotFoundException("Не найдет контакт с индетификатором $id");
        }

        $form = $this->createForm(AddressBookType::class, $addressBook);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($addressBook);
            $em->flush();

            return $this->json($addressBook, Response::HTTP_OK);
        }

        return $this->json(['status' => 'error', 'errors' => $form], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Удаление (Softdelete) записи в БД
     *
     * @Route("addressbook/{id}", name="api_v1_delete_address_book", methods={"DELETE"})
     *
     * @param Request $request
     * @param AddressBookRepository $addressBookRepository
     * @param string $id
     * @return Response
     */
    public function delete(Request $request, AddressBookRepository $addressBookRepository, string $id): Response
    {
        $addressBook = $addressBookRepository->findOneBy(['id' => $id]);

        if (!$addressBook) {
            throw new ResourceNotFoundException("Не найдет контакт с индетификатором $id");
        }

        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->enable('softdeleteable');
        $em->remove($addressBook);
        $em->flush();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }


}
