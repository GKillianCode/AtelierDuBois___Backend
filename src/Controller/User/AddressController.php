<?php

namespace App\Controller\User;

use App\Dto\User\AddressDto;
use App\Service\User\AddressService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AddressController extends AbstractController
{
    private AddressService $addressService;
    private SerializerInterface $serializer;

    public function __construct(AddressService $addressService, SerializerInterface $serializer)
    {
        $this->addressService = $addressService;
        $this->serializer = $serializer;
    }

    #[Route('/api/v1/user/address/add', name: 'address_add', methods: ['POST'])]
    public function addAddress(Request $request): Response
    {
        $addressDto = $this->serializer->deserialize(
            $request->getContent(),
            AddressDto::class,
            'json'
        );

        $this->addressService->addAddress($addressDto);
        return new Response("");
    }

    /*
    #[Route('/api/v1/user/address/{id}', name: 'address_get', methods: ['GET'])]
    public function getAddress(int $id): Response
    {
        return new Response("");
    }

    #[Route('/api/v1/user/address/', name: 'address_get_all', methods: ['GET'])]
    public function getAllAddress(): Response
    {
        return new Response("");
    }

    #[Route('/api/v1/user/{id}/update', name: 'address_update', methods: ['PUT'])]
    public function updateAddress(int $id): Response
    {

        return new Response("");
    }

    #[Route('/api/v1/user/{id}/remove', name: 'address_remove', methods: ['DELETE'])]
    public function removeAddress(int $id): Response
    {
        return new Response("");
    }
    */
}
