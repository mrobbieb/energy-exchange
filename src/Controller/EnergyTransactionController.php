<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Battery;
use App\Entity\EnergyTransaction;
use App\Form\BatteryType;
use App\Repository\BatteryRepository;
use App\Repository\BatteryBankRepository;
use App\Repository\EnergyTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\EnergyTransactionDTO;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/transaction')]
final class EnergyTransactionController extends AbstractController
{
    #[Route(name: 'app_energy_transaction')]
    public function index(): Response
    {
        return $this->render('energy_transaction/index.html.twig', [
            'controller_name' => 'EnergyTransactionController',
        ]);
    }

    #[Route('', name: 'app_energy_transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
    EnergyTransactionRepository $energyTransactionRepository,
    BatteryRepository $batteryRepository,
    BatteryBankRepository $batteryBankRepository,
    UserRepository $userRepository,
    SerializerInterface $serializer,
    EntityManagerInterface $entityManager,
    ValidatorInterface $validator): JsonResponse
    {
        $data = $request->getContent();
        
        try {
            $transactionSerializer = $serializer->deserialize($data, EnergyTransaction::class, 'json');
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
        $transactionDto = new EnergyTransactionDTO();
        $transactionDto->battery = $transactionSerializer->getBattery();
        $transactionDto->batteryBank = $transactionSerializer->getBatteryBank();
        $transactionDto->user = $transactionSerializer->getUser();
        $transactionDto->watts = $transactionSerializer->getWatts();

        $violations = $validator->validate($transactionDto);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $v) {
                $errors[$v->getPropertyPath()][] = $v->getMessage();
            }
            return $this->json(['errors' => $errors], 422);
        }

        $transaction = new EnergyTransaction();
        $transaction->setBattery($transactionDto->battery);
        $transaction->setBatteryBank($transactionDto->batteryBank);
        $transaction->setUser($transactionDto->user);
        $transaction->setWatts($transactionDto->watts);
        $transaction->setCreatedAt(new DateTimeImmutable());

        $entityManager->persist($transaction);
        $entityManager->flush();

        /**
         * @Todo 
         * There is a way to use groups and context to clean this up. This works for now but temporary.
         */
        $data = [
            'battery' => $transaction->getBattery()->getId(),
            'batteryBank' => $transaction->getBatteryBank()->getId(),
            'user' => $transaction->getUser()->getId(),
            'watts' => $transaction->getWatts(),
            'transactionId' => $transaction->getId(),
        ];

        $newTransaction = $serializer->serialize($data, 'json');

        
        return new JsonResponse(
            $newTransaction,
            JsonResponse::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'app_energy_transaction_show', methods: ['GET'])]
    public function show(int $id, Request $request, EnergyTransactionRepository $energyTransactionRepository, SerializerInterface $serializer): JsonResponse
    {
        $energyTransaction = $energyTransactionRepository->find($id);

        // $data = [
        //     'transactionId' => $energyTransaction->getId(),
        //     'batteryId' => $energyTransaction->getBattery()->getId(),
        //     'batteryBankId' => $energyTransaction->getBatteryBank()->getId(),
        //     'userId' => $energyTransaction->getUser()->getId(),
        //     'watts' => $energyTransaction->getWatts(),
        //     'createdAt' => $energyTransaction->getCreatedAt()
        // ];

        // $serializedTransaction = $serializer->serialize($data,'json');
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('energyTransaction:read')
            ->toArray();

        $serializedTransaction = $serializer->serialize($energyTransaction,'json', $context);

        return new JsonResponse($serializedTransaction, JsonResponse::HTTP_OK, [], true);
    }
}
