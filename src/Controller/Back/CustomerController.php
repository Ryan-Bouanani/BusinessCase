<?php

namespace App\Controller\Back;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Form\Filter\CustomerFilterType;
use App\Form\RegistrationFormType;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdater;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/customer')]
#[IsGranted('ROLE_ADMIN')]
class CustomerController extends AbstractController
{
    #[Route('/', name: 'app_customer_index', methods: ['GET'])]
    public function index(
        CustomerRepository $customerRepository,
        PaginatorInterface $paginator,
        Request $request,
        FilterBuilderUpdater $builderUpdater,
        ): Response
    {
        // on récupère tout les clients
        $qb = $customerRepository->getQbAll();

        // on crée nos filtres de recherche
        $filterForm = $this->createForm(CustomerFilterType::class, null, [
            'method' => 'GET',
        ]);
         // on vérifie si la query a un paramètre du formFilter en cours.Si oui, on l’ajoute dans le queryBuilder
         if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        // Pagination
        $customers = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/customer/index.html.twig', [
            'customers' => $customers,
            'filters' => $filterForm->createView(),
        ]);

    }

    #[Route('/new', name: 'app_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CustomerRepository $customerRepository): Response
    {

        $customer = new Customer();

        // Creation du formulaire de client
        $form = $this->createForm(CustomerType::class, $customer);
        
        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {

             // On met le client à jour en bdd
            $customerRepository->add($customer, true);

            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    // #[Route('/{id}', name: 'app_customer_show', methods: ['GET'])]
    // public function show(Customer $customer): Response
    // {

    //     return $this->render('back/customer/show.html.twig', [
    //         'customer' => $customer,
    //     ]);
    // }

    
    // #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, Customer $customer, CustomerRepository $customerRepository, UserPasswordHasherInterface $hasher): Response
    // {

    //     // Creation du formulaire de client
    //     $form = $this->createForm(CustomerType::class, $customer);
        
    //     // On inspecte les requettes du formulaire
    //     $form->handleRequest($request);

    //     // Si le form est envoyé et valide
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         // Et 
    //         if ($hasher->isPasswordValid($customer, $form->getData()->getPassword())) {
    //             $customer = $form->getData();
    //             $customerRepository->add($customer, true);

    //             $this->addFlash(
    //                 'success',
    //                 'Les informations de votre compte ont bien été modifiées.'
    //             );

    //             return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
    //         } else {
    //             $this->addFlash(
    //                 'warning',
    //                 'Le mot de passe renseigné est incorrect.'
    //             );
    //         } 
    //     }

    //     return $this->renderForm('back/customer/edit.html.twig', [
    //         'customer' => $customer,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/{id}', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, CustomerRepository $customerRepository): Response
    {
        
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->request->get('_token'))) {
            $customerRepository->remove($customer, true);
        }

        return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
    }
}
