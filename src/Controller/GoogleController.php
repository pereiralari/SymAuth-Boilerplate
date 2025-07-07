<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile']);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry, EntityManagerInterface $em, JWTTokenManagerInterface $jwtManager)
    {
        $client = $clientRegistry->getClient('google');
        $googleUser = $client->fetchUserFromToken($client->getAccessToken());
        
        // Check if user exists
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $googleUser->getEmail()]);
        
        if (!$existingUser) {
            // Create new user
            $user = new User();
            $user->setEmail($googleUser->getEmail());
            $user->setRoles(['ROLE_USER']);
            $user->setUuid(Uuid::v4()->toRfc4122());
            // Set a random password or leave it blank depending on your needs
            $user->setPassword(bin2hex(random_bytes(16)));
            
            $em->persist($user);
            $em->flush();
        } else {
            $user = $existingUser;
        }
        
        // Generate JWT token
        $token = $jwtManager->create($user);
        
        // Redirect to frontend with token
        return new JsonResponse([
            'token' => $token
        ]);
    }
}