<?php

namespace App\Controller\BackOffice\Funding;

use App\Entity\MonthlyCostCoverage;
use App\Form\Dto\MonthlyCostCoverage as MonthlyCostCoverageDto;
use App\Form\MonthlyCostCoverageForm;
use App\Repository\MonthlyCostCoverageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MonthlyCostCoverageCreateController extends AbstractController
{
    private MonthlyCostCoverageRepository $monthlyCostCoverageRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(MonthlyCostCoverageRepository $monthlyCostCoverageRepository, EntityManagerInterface $entityManager)
    {
        $this->monthlyCostCoverageRepository = $monthlyCostCoverageRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/monthly-cost-coverage/create", name="bo_monthly_cost_coverage_create", methods={"GET","POST"})
     */
    public function __invoke(Request $request): Response
    {
        $monthlyCostCoverage = new MonthlyCostCoverageDto(new \DateTimeImmutable('first day of'), 0);
        dump($monthlyCostCoverage);
        $form = $this->createForm(MonthlyCostCoverageForm::class, $monthlyCostCoverage, [
            'mode' => MonthlyCostCoverageForm::MODE_CREATE,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($this->monthlyCostCoverageRepository->findOneBy(['month' => $monthlyCostCoverage->month]) !== null) {
                $form->get('month')->addError(new FormError('This month is already created.'));
            }

            if ($form->isValid()) {
                $costCoverage = new MonthlyCostCoverage(Uuid::uuid4());
                $costCoverage->setMonth(clone $monthlyCostCoverage->month);
                $costCoverage->setTarget($monthlyCostCoverage->target);
                $costCoverage->setPostpone($monthlyCostCoverage->postpone);
                $this->entityManager->persist($costCoverage);
                $this->entityManager->flush();

                return $this->redirectToRoute('bo_monthly_cost_coverage_list');
            }
        }

        return $this->render('back_office/monthly_cost_coverage_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
