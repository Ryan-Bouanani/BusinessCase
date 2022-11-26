<?php

namespace App\Controller\Front;

use App\Entity\Address;
use App\Entity\Customer;
use App\Form\AddressType;
use App\Form\CustomerType;
use App\Form\UserPasswordType;
use App\Repository\AddressRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customer')]
class CustomerController extends AbstractController
{

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param AddressRepository $addressRepository
     * @param CustomerRepository $customerRepository
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route('/', name: 'app_customer')]
    public function index(
        Request $request,
        AddressRepository $addressRepository, 
        CustomerRepository $customerRepository,
        UserPasswordHasherInterface $hasher
        ): Response
    {
        // On récupère l'utilisateur
        $customer = $this->getUser();

        // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

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


                    return $this->redirectToRoute('app_customer', [], Response::HTTP_SEE_OTHER);
                }
        // 
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
                    'Vos informations ont bien été modifiées.'
                );
                return $this->redirectToRoute('app_customer', [], Response::HTTP_SEE_OTHER);
            }
        // 
        // * FORM CHANGE PASSWORD
            // Creation du formulaire de mot de passe 
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

                    return $this->redirectToRoute('app_customer');
                } else {
                    $this->addFlash(
                        'error',
                        'Le mot de passe renseigné est incorrect.'
                    );
                }
            }
        // 

        return $this->render('front/customer/index.html.twig', [
            'addressForm' => $address,
            'formAddress' => $formAddress->createView(),
            // 'formMail' => $formMail->createView(),
            'formPassword' => $passForm->createView(),
            'formUser' => $formUser->createView(),
            // 'address' => $customer->getAddress(),
            // 'user' => $customer
        ]);
    }
}
