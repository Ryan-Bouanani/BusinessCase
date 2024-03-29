<?php

namespace App\Controller\Front;

use App\Entity\Customer;
use App\Form\RegistrationFormType;
use App\Security\CustomerAuthenticator;
use App\Service\ShoppingCart\ShoppingCartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * Ce controller va servir à inscrire l'utilisateur
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param UserAuthenticatorInterface $userAuthenticator
     * @param CustomerAuthenticator $authenticator
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, CustomerAuthenticator $authenticator, 
    EntityManagerInterface $entityManager,
    ShoppingCartService $shoppingCartService,
    ): Response
    {
        $user = new Customer();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            // dd($user);

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        // On ajoute un user à la commande
        $shoppingCartService->addUserToBasket();

        return $this->render('front/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/checkout/inscription', name: 'app_checkout_register')]
    public function checkoutRegister(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        UserAuthenticatorInterface $userAuthenticator, 
        CustomerAuthenticator $authenticator, 
        EntityManagerInterface $entityManager,
        ShoppingCartService $shoppingCartService,
        SessionInterface $session,
    ): Response
    {
        $user = new Customer();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            // dd($user);

            $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );

            // On ajoute un user à la commande
            $shoppingCartService->addUserToBasket();
            
            // On vérifie que l'utilisateur possède bien un panier
            if ($session->has('shoppingCart')) {
                // Sinon on redrige vers formulaire d'ajout d'adresse
                return $this->redirectToRoute('checkout_address');
            }
        }


        return $this->render('front/shoppingCart/register.html.twig', [
            'registrationForm' => $form->createView(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal(),
        ]);
    }
}
