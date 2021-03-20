<?php

namespace App\Controller\BackOffice\Funding;

use App\Domain\MonthlyCostCoverageId;
use App\Entity\MonthlyCostCoverage;
use App\Form\Dto\MonthlyCostCoverage as MonthlyCostCoverageDto;
use App\Form\MonthlyCostCoverageForm;
use App\Repository\MonthlyCostCoverageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

class MonthlyCostCoverageCreateController extends AbstractController
{
    public function __construct(
        private MonthlyCostCoverageRepository $monthlyCostCoverageRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route("/bo/monthly-cost-coverage/create", name: "bo_monthly_cost_coverage_create", methods: ["GET", "POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $monthlyCostCoverage = new MonthlyCostCoverageDto(new \DateTimeImmutable('first day of'), 0);
        $form = $this->createForm(MonthlyCostCoverageForm::class, $monthlyCostCoverage, [
            'mode' => MonthlyCostCoverageForm::MODE_CREATE,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($this->monthlyCostCoverageRepository->findOneBy(['month' => $monthlyCostCoverage->month]) !== null) {
                $form->get('month')->addError(new FormError('This month is already created.'));
            }

            if ($form->isValid()) {
                $costCoverage = new MonthlyCostCoverage(new MonthlyCostCoverageId(new Ulid()), $monthlyCostCoverage->month);
                $costCoverage->setTarget($monthlyCostCoverage->target);
                $costCoverage->setPostpone($monthlyCostCoverage->postpone);
                $this->entityManager->persist($costCoverage);
                $this->entityManager->flush();

                return $this->redirectToRoute('bo_monthly_cost_coverage_list');
            }
        }

        return $this->render('back_office/funding/monthly_cost_coverage_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
