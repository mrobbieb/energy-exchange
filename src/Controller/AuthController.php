<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'auth', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This method is never really called on success:
        // json_login + success_handler return the token.
        // But Symfony requires a controller for the route.
        return $this->json([
            'message' => 'Bad credentials or JSON login not triggered',
        ], 401);
    }
}
