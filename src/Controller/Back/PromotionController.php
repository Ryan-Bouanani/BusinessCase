<?php

namespace App\Controller\Back;

use App\Entity\Promotion;
use App\Form\Filter\PromotionFilterType;
use App\Form\PromotionType;
use App\Repository\PromotionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/promotion')]
#[IsGranted('ROLE_ADMIN')]
class PromotionController extends AbstractController
{
    /**
     * Ce controller va servir à afficher la liste des promotions
     *
     * @param PromotionRepository $promotionRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param FilterBuilderUpdaterInterface $builderUpdater
     * @return Response
     */
    #[Route('/', name: 'app_promotion_index', methods: ['GET'])]
    public function index(
        PromotionRepository $promotionRepository,
        PaginatorInterface $paginator,
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater
        ): Response
    {
        // on récupère tout les produits
        $qb = $promotionRepository->getQbAll();

        // on crée nos filtres de recherche de promotion
        $filterForm = $this->createForm(PromotionFilterType::class, null, [
            'method' => 'GET',
        ]);

        // On vérifie si la query a un paramètre du formFilter en cours, si oui, on l’ajoute dans le queryBuilder
        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        // Pagination
        $promotions = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('back/promotion/index.html.twig', [
            'promotions' => $promotions,
            'filters' => $filterForm->createView(),
        ]);
    }

    /**
     * Ce controller va servir à l'ajout d'un nouvelle promotion
     *
     * @param Request $request
     * @param PromotionRepository $promotionRepository
     * @return Response
     */
    #[Route('/new', name: 'app_promotion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PromotionRepository $promotionRepository): Response
    {
        $promotion = new Promotion();

        // Creation du formulaire de promotion
        $form = $this->createForm(PromotionType::class, $promotion);
        
        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On met la promotion en bdd
            $promotionRepository->add($promotion, true);
            $this->addFlash(
                'success',
                'Votre promotion a été ajoutée avec succès !'
            );           
            return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash(
                'error',
                $form->getErrors()
            );
        }

        return $this->renderForm('back/promotion/new.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    /**
     * Ce controller va servir à la modification d'une promotion
     *
     * @param Request $request
     * @param Promotion $promotion
     * @param PromotionRepository $promotionRepository
     * @return Response
     */
    #[Route('/{slug}/edit', name: 'app_promotion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promotion $promotion, PromotionRepository $promotionRepository): Response
    {
        // Creation du formulaire de promotion
        $form = $this->createForm(PromotionType::class, $promotion);

        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
             // On met la promotion à jour en bdd
            $promotionRepository->add($promotion, true);

            $this->addFlash(
                'success',
                'Votre promotion a été modifiée avec succès !'
            );
            return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash(
                'error',
                $form->getErrors()
            );
        }

        return $this->renderForm('back/promotion/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_promotion_delete', methods: ['POST'])]
    /**
     *  Ce controller va servir à la suppression d'une promotion
     *
     * @param Request $request
     * @param Promotion $promotion
     * @param PromotionRepository $promotionRepository
     * @return Response
     */
    public function delete(Request $request, Promotion $promotion, PromotionRepository $promotionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->request->get('_token'))) {
            $promotionRepository->remove($promotion, true);
        }

        return $this->redirectToRoute('app_promotion_index', [], Response::HTTP_SEE_OTHER);
    }
}
