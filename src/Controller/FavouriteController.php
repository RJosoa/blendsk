<?php

namespace App\Controller;

use App\Entity\Favourite;
use App\Entity\Post;
use App\Repository\FavouriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FavouriteController extends AbstractController
{
    #[Route('/favourite', name: 'app_favourite')]
    public function index(): Response
    {
        return $this->render('favourite/index.html.twig', [
            'controller_name' => 'FavouriteController',
        ]);
    }

    #[Route('/posts/{id}/favourite', name: 'app_toggle_favourite', methods: ['POST'])]
    public function toggleFavourite(Post $post, EntityManagerInterface $em, FavouriteRepository $favouriteRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'User not authenticated'], 401);
        }

        $existingFavourite = $favouriteRepository->findOneBy(['post' => $post, 'user' => $user]);

        if ($existingFavourite) {
            $em->remove($existingFavourite);
            $action = 'unfavourited';
        } else {
            $favourite = new Favourite();
            $favourite->setPost($post);
            $favourite->setUser($user);
            $em->persist($favourite);
            $action = 'favourited';
        }

        $em->flush();

        return $this->json([
            'message' => "Post {$action} successfully",
            'isFavourited' => $action === 'favourited'
        ]);
    }
}
