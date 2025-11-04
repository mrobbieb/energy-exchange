<?php

namespace App\Controller;

use App\Entity\Exchange;
use App\Form\ExchangeType;
use App\Repository\ExchangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/exchange')]
final class ExchangeController extends AbstractController
{
    #[Route(name: 'app_exchange_index', methods: ['GET'])]
    public function index(ExchangeRepository $exchangeRepository): Response
    {
        return $this->render('exchange/index.html.twig', [
            'exchanges' => $exchangeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_exchange_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exchange = new Exchange();
        $form = $this->createForm(ExchangeType::class, $exchange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exchange);
            $entityManager->flush();

            return $this->redirectToRoute('app_exchange_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exchange/new.html.twig', [
            'exchange' => $exchange,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_exchange_show', methods: ['GET'])]
    public function show(Exchange $exchange): Response
    {
        return $this->render('exchange/show.html.twig', [
            'exchange' => $exchange,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_exchange_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Exchange $exchange, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExchangeType::class, $exchange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_exchange_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exchange/edit.html.twig', [
            'exchange' => $exchange,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_exchange_delete', methods: ['POST'])]
    public function delete(Request $request, Exchange $exchange, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exchange->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($exchange);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_exchange_index', [], Response::HTTP_SEE_OTHER);
    }
}
