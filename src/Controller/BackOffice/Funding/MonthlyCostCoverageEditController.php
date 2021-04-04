<?php

namespace App\Controller\BackOffice\Funding;

use App\Entity\MonthlyCostCoverage;
use App\Form\Dto\MonthlyCostCoverage as MonthlyCostCoverageDto;
use App\Form\MonthlyCostCoverageForm;
use App\Repository\MonthlyCostCoverageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MonthlyCostCoverageEditController extends AbstractController
{
    public function __construct(
        private MonthlyCostCoverageRepository $monthlyCostCoverageRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route("/bo/monthly-cost-coverage/edit/{id}", name: "bo_monthly_cost_coverage_edit", methods: ["GET", "POST"])]
    public function __invoke(
        Request $request, string $id
    ): Response {
        /** @var MonthlyCostCoverage $costCoverage */
        $costCoverage = $this->monthlyCostCoverageRepository->find($id);
        if ($costCoverage === null) {
            throw new NotFoundHttpException('Monthly cost coverage not found.');
        }

        if (!$costCoverage->isDefault() && $costCoverage->isPast(new \DateTimeImmutable('now'))) {
            throw new NotFoundHttpException('Monthly cost coverage is past.');
        }

        $monthlyCostCoverage = new MonthlyCostCoverageDto(
            clone $costCoverage->getMonth(),
            $costCoverage->getTarget(),
            $costCoverage->isPostpone(),
        );
        $form = $this->createForm(MonthlyCostCoverageForm::class, $monthlyCostCoverage, [
            'default_coverage' => $costCoverage->isDefault(),
            'mode' => MonthlyCostCoverageForm::MODE_EDIT,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $costCoverage->setMonth(clone $monthlyCostCoverage->month);
            $costCoverage->setTarget($monthlyCostCoverage->target);
            $costCoverage->setPostpone($monthlyCostCoverage->postpone);
            $this->entityManager->flush();

            return $this->redirectToRoute('bo_monthly_cost_coverage_list');
        }

        return $this->render('back_office/funding/monthly_cost_coverage_edit.html.twig', [
            'form' => $form->createView(),
            'monthly_cost_coverage' => $costCoverage,
        ]);
    }
}
