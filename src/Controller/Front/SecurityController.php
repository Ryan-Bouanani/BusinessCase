<?php

namespace App\Controller\Front;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\BasketRepository;
use App\Repository\CustomerRepository;
use App\Service\SendMailService;
use App\Service\ShoppingCart\ShoppingCartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('front/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route(path: '/checkout/connexion', name: 'app_checkout_login')]
    public function loginCheckout (
        AuthenticationUtils $authenticationUtils, 
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
        SessionInterface $session,
        ): Response
    {
        // /** @var Customer $user*/
        $user = $this->getUser();
        if ($user) {          
            // On recupere le panier de la session 
            if ($session->has('shoppingCart')) {

                 /** @var Basket $shoppingCart*/
               $shoppingCart = $session->get('shoppingCart', []);
               $shoppingCart = $basketRepository->find($shoppingCart->getId());
               $shoppingCart->setCustomer($user);
               $basketRepository->add($shoppingCart, true);
           } else {
                return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
           }
           
           if ($shoppingCart) {
                // Si la commande possède une adresse et/ou un moyen de paiement alors on redirige 
               if ($shoppingCart->getAddress() !== null) {           
                   if ($shoppingCart->getMeanOfPayment() !== null) {           
                       return $this->redirectToRoute('app_checkout_resume', [], Response::HTTP_SEE_OTHER);
                   } 
                   return $this->redirectToRoute('app_checkout_payment', [], Response::HTTP_SEE_OTHER);
               } 
               // Sinon on redrige vers formulaire d'ajout d'adresse
               return $this->redirectToRoute('app_checkout_address');
           }
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('front/shoppingCart/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error,
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal(),
        ]);
    }

    #[Route(path: '/oubli-pass', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request, 
        CustomerRepository $customerRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail,
    ): Response
    {
        // On crée le formulaire de demande du mail du compte de l'utilisateur
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On va chercher l'utilisateur par son email
            $user = $customerRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if ($user) {
                // On génère un token  de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);

                // On envoie en bdd le token  de réinitialisation de l'utilisateur
                $entityManager->flush();

                // On génere un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // On crée les données du mail
                $context = compact('url', 'user');

                // Envoi du mail
                $mail->send(
                    'LaNimesAlerie@gmail.com',
                    $user->getEmail(),
                    'Réinitialisation de mot de passe',
                    'password_reset',
                    $context
                );

                // On renvoie un message de succès
                $this->addFlash('success', 'Un email vous a été envoyé, sur votre boite mail');
                return $this->redirectToRoute('app_login');
            }
            // Si user est null, on renvoie un message d'erreur
            $this->addFlash('error', 'Un problème est survenue');
            return $this->redirectToRoute('app_login');
        }
        // On envoie le form pour qu'il soit affiché
        return $this->render('front/security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/oubli-pass/{token}', name: 'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ): Response
    {
        // On vérifier si on a ce token dans la bdd
        $user = $customerRepository->findOneByResetToken($token);

        // Si le token appartient à un utilisateur
        if ($user) {
            // On crée le formulaire du nouveau mot de passe
            $form = $this->createForm(ResetPasswordFormType::class);

            // On inspecte les requettes du formulaire
            $form->handleRequest($request);

            // Si le formulaire est envoyé et valide
            if ($form->isSubmitted() && $form->isValid()) {

                // On éfface le token
                $user->setResetToken('');

                // Et on set le nouveau mot de passe en le hachant
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                // Puis on met à jour le reset du token et le nouveau mot de passe en bdd
                $entityManager->flush();

                // Si mot de passe changé alors on renvoie un message de succes
                $this->addFlash('succes', 'Mot de passe mis à jour avec succès');
                return $this->redirectToRoute('app_login');
            }

            // On envoie le form pour qu'il soit affiché
            return $this->render('front/security/reset_password.html.twig', [
                'passForm' => $form->createView(),
            ]);
        }
        // Si le jeton n'est pas valide, on renvoie une erreur
        $this->addFlash('error', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }
    
}
