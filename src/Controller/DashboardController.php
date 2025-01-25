<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('user/dashboard', name: 'dashboard')]
    public function index(PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // Check if user is logged in
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $favoritePosts = $postRepository->findByUserFavorites($user->getId());
        $likedPosts = $postRepository->findByUserLikes($user->getId());

        return $this->render('dashboard/index.html.twig', [
            'favourites' => $favoritePosts,
            'likes' => $likedPosts
        ]);
    }
}
