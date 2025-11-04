<?php

namespace App\Controller;

use App\Entity\BatteryBank;
use App\Form\BatteryBankType;
use App\Repository\BatteryBankRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/battery/bank')]
final class BatteryBankController extends AbstractController
{
    #[Route(name: 'app_battery_bank_index', methods: ['GET'])]
    public function index(BatteryBankRepository $batteryBankRepository): Response
    {
        return $this->render('battery_bank/index.html.twig', [
            'battery_banks' => $batteryBankRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_battery_bank_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $batteryBank = new BatteryBank();
        $form = $this->createForm(BatteryBankType::class, $batteryBank);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($batteryBank);
            $entityManager->flush();

            return $this->redirectToRoute('app_battery_bank_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('battery_bank/new.html.twig', [
            'battery_bank' => $batteryBank,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_battery_bank_show', methods: ['GET'])]
    public function show(BatteryBank $batteryBank): Response
    {
        return $this->render('battery_bank/show.html.twig', [
            'battery_bank' => $batteryBank,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_battery_bank_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BatteryBank $batteryBank, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BatteryBankType::class, $batteryBank);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_battery_bank_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('battery_bank/edit.html.twig', [
            'battery_bank' => $batteryBank,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_battery_bank_delete', methods: ['POST'])]
    public function delete(Request $request, BatteryBank $batteryBank, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$batteryBank->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($batteryBank);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_battery_bank_index', [], Response::HTTP_SEE_OTHER);
    }
}
