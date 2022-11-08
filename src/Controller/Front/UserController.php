<?php

namespace App\Controller\Front;

use App\Entity\Address;
use App\Form\AddressType;
use App\Form\CustomerType;
use App\Repository\AddressRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    #[Route('/user', name: 'app_user')]
    public function index(Request $request,
    AddressRepository $addressRepository, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response
    {
        // On récupère l'utilisateur
        $customer = $this->getUser();

        if (is_null($customer->getAddress())){
            $address = new Address();
        } else {
            $address = $customer->getAddress();
        }
          $formAddress = $this->createForm(AddressType::class, $address);
            $formAddress->handleRequest($request);

            if ($formAddress->isSubmitted() && $formAddress->isValid()) {
                $addressRepository->add($address, true);
                $customer->setAddress($address);
                $entityManager->persist($customer);
                $entityManager->flush();
                return $this->redirectToRoute('app_user', [], Response::HTTP_SEE_OTHER);
            }

        $formUser = $this->createForm(CustomerType::class, $customer);
        $formUser->handleRequest($request);

        if ($formUser->isSubmitted() && $formUser->isValid()) {
            $customerRepository->add($customer, true);
            return $this->redirectToRoute('app_user', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('front/user/index.html.twig', [
            'addressForm' => $address,
            'formAddress' => $formAddress->createView(),
            // 'formMail' => $formMail->createView(),
            // 'formPassword' => $formPassword->createView(),
            'formUser' => $formUser->createView(),
            // 'address' => $customer->getAddress(),
            // 'user' => $customer
        ]);
    }
}
