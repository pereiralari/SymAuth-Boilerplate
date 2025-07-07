<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/users')]
#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    #[Route('', name: 'api_users_list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['user:read']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'The page number',
        schema: new OA\Schema(type: 'integer')
    )]
    #[Security(name: 'Bearer')]
    public function list(UserRepository $userRepository, Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        $users = $userRepository->findBy(
            ['deletedAt' => null],
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );
        
        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a user',
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:read']))
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found'
    )]
    #[Security(name: 'Bearer')]
    public function show(User $user): JsonResponse
    {
        if ($user->isDeleted()) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }
}