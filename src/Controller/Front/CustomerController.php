<?php

namespace App\Controller\Front;

use App\Entity\Address;
use App\Entity\Customer;
use App\Form\AddressType;
use App\Form\CustomerType;
use App\Form\UserEmailType;
use App\Form\UserPasswordType;
use App\Repository\AddressRepository;
use App\Repository\BasketRepository;
use App\Repository\CustomerRepository;
use App\Service\ShoppingCart\ShoppingCartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customer')]
class CustomerController extends AbstractController
{
    /**
     * Ce controller va servir à afficher le menu de la page mon compte
     *
     * @return Response
     */
    #[Route('/', name: 'app_customer')]
    public function index(): Response
    {
        // On récupère l'utilisateur
        $customer = $this->getUser();

        // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
        if (!$customer) {
            return $this->redirectToRoute('app_login');
        }   

        return $this->render('front/customer/index.html.twig', [
            'customer' => $customer
        ]);
    }

    #[Route('/order', name: 'app_customer_order')]
    /**
     * Ce controller va servir à afficher les anciennes commande de l'utilisateur
     *
     * @param BasketRepository $basketRepository
     * @param ShoppingCartService $shoppingCartService
     * @return Response
     */
    public function order(
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
        ): Response
    {
        // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
        /** @var Customer $customer*/
        $customer = $this->getUser();
        if (!$customer) {
            return $this->redirectToRoute('app_login');
        }

        // On récupere les commandes de l'utilisateur
        $orders = $basketRepository->findLastBasketWithCustomer($customer->getId());
        // On récupere le total de chaque commande
        $total = [];
        foreach ($orders as $key => $order) {
            $totalOrder = $shoppingCartService->getTotal($order);
            // On additione le total de chaque commande
            $total[$key] = $totalOrder;
        }
        return $this->render('front/customer/order.html.twig', [
            'orders' => $orders,
            'total' => $total
        ]);
    }

    /**
     * Ce controller va servir à afficher et modifier les donées personnelles de l'utilisateur
     *
     * @param Request $request
     * @param AddressRepository $addressRepository
     * @param CustomerRepository $customerRepository
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route('/personalData', name: 'app_customer_personalData')]
    public function personalData(
        Request $request,
        AddressRepository $addressRepository, 
        CustomerRepository $customerRepository,
        UserPasswordHasherInterface $hasher
        ): Response
    {
         // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
         $customer = $this->getUser();
         if (!$customer) {
             return $this->redirectToRoute('app_login');
         }
 
        // * FORM INFOS PERSONNELLES
             // Creation du formulaire d'informations personnelles
             $formUser = $this->createForm(CustomerType::class, $customer);
             
             // On inspecte les requettes du formulaire
             $formUser->handleRequest($request);
 
             // Si le formulaire est envoyé et valide
             if ($formUser->isSubmitted() && $formUser->isValid()) {
                 // On met l'utilisateur à jour en bdd
                 $customerRepository->add($customer, true);
 
                 $this->addFlash(
                     'success',
                     'Vos coordonnées ont bien été modifiées.'
                 );
             }
        // 
         // * FORM ADRESSE
             // Si l'utilisateur n'a pas d'adresse onen crée une
             /** @var Customer $customer*/
             if (is_null($customer->getAddress())){
                 $address = new Address();
             } else {
                 $address = $customer->getAddress();
             }
             // Creation du formulaire d'adresse
             $formAddress = $this->createForm(AddressType::class, $address);
 
                 // On inspecte les requettes du formulaire
                 $formAddress->handleRequest($request);
 
                 // Si le formulaire est envoyé et valide
                 if ($formAddress->isSubmitted() && $formAddress->isValid()) {
                     // On ajoute l'addresse en bdd
                     $addressRepository->add($address, true);
                     $customer->setAddress($address);
                     $customerRepository->add($customer, true);
 
                     $this->addFlash(
                         'success',
                         'Votre adresse a bien été modifié.'
                     );
                 }
         // 
        //  * FORM CHANGE EMAIL
               // Creation du formulaire de changement de mail
               $formEmail = $this->createForm(UserEmailType::class);
 
               // On inspecte les requettes du formulaire
               $formEmail->handleRequest($request);
               
               // Si le formulaire est envoyé et valide
               if ($formEmail->isSubmitted() && $formEmail->isValid()) {

                    // Si le password entré correspond au password actuel 
                   if ($hasher->isPasswordValid($customer, $formEmail->getData()['password'])) {
   
                    // On met à jour le mail de l'utilisateur
                    $customer->setEmail($formEmail->get('email')->getData());
   
                       $this->addFlash(
                           'success',
                           'Votre email a bien été modifié.'
                       );
   
                       // On met l'utilisateur à jour en bdd
                       $customerRepository->add($customer, true);
                   } else {
                       $this->addFlash(
                           'error',
                           'Le mot de passe renseigné est incorrect.'
                       );
                   }
               }
        // 
         // * FORM CHANGE PASSWORD
             // Creation du formulaire de changement de mot de passe 
             $passForm = $this->createForm(UserPasswordType::class);
 
             // On inspecte les requettes du formulaire
             $passForm->handleRequest($request);
             
             // Si le formulaire est envoyé et valide
             if ($passForm->isSubmitted() && $passForm->isValid()) {
                 if ($hasher->isPasswordValid($customer, $passForm->getData()['password'])) {
 
                     // Et on set le nouveau mot de passe en le hachant
                     $customer->setPassword(
                         $hasher->hashPassword(
                             $customer,
                         $passForm->getData()['newPassword']
                         )
                     );
 
                     $this->addFlash(
                         'success',
                         'Le mot de passe a bien été modifié.'
                     );
 
                     // On met l'utilisateur ç jour en bdd
                     $customerRepository->add($customer, true);
                 } else {
                     $this->addFlash(
                         'error',
                         'Le mot de passe renseigné est incorrect.'
                     );
                 }
             }
         // 
 
         return $this->render('front/customer/personalData.html.twig', [
             'addressForm' => $address,
             'formAddress' => $formAddress->createView(),
             'formEmail' => $formEmail->createView(),
             'formPassword' => $passForm->createView(),
             'formUser' => $formUser->createView(),
             'address' => $customer->getAddress(),
             'customer' => $customer
         ]);
    }
}
