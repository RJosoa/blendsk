<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class LikeController extends AbstractController
{
    #[Route('/like', name: 'app_like')]
    public function index(): Response
    {
        return $this->render('like/index.html.twig', [
            'controller_name' => 'LikeController',
        ]);
    }

    #[Route('/posts/{id}/like', name: 'app_toggle_like', methods: ['POST'])]
    public function toggleLike(Post $post, EntityManagerInterface $em, LikeRepository $likeRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'User not authenticated'], 401);
        }

        $existingLike = $likeRepository->findOneBy(['post' => $post, 'user' => $user]);

        if ($existingLike) {
            $em->remove($existingLike);
            $action = 'unliked';
        } else {
            $like = new Like();
            $like->setPost($post);
            $like->setUser($user);
            $em->persist($like);
            $action = 'liked';
        }

        $em->flush();

        return $this->json([
            'message' => "Post {$action} successfully",
            'likesCount' => $post->getLikes()->count(),
            'isLiked' => $action === 'liked'
        ]);
    }

}
