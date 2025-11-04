<?php

namespace App\Controller;

use App\Entity\Battery;
use App\Form\BatteryType;
use App\Repository\BatteryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/battery')]
final class BatteryController extends AbstractController
{
    #[Route(name: 'app_battery_index', methods: ['GET'])]
    public function index(BatteryRepository $batteryRepository): Response
    {
        return $this->render('battery/index.html.twig', [
            'batteries' => $batteryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_battery_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $battery = new Battery();
        $form = $this->createForm(BatteryType::class, $battery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($battery);
            $entityManager->flush();

            return $this->redirectToRoute('app_battery_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('battery/new.html.twig', [
            'battery' => $battery,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_battery_show', methods: ['GET'])]
    public function show(Battery $battery): Response
    {
        return $this->render('battery/show.html.twig', [
            'battery' => $battery,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_battery_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Battery $battery, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BatteryType::class, $battery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_battery_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('battery/edit.html.twig', [
            'battery' => $battery,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_battery_delete', methods: ['POST'])]
    public function delete(Request $request, Battery $battery, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$battery->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($battery);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_battery_index', [], Response::HTTP_SEE_OTHER);
    }
}
