<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CategoryRepository;
use App\Repository\FavouriteRepository;
use App\Repository\LikeRepository;
use App\Services\CloudinaryService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{

    #[Route('/explorer', name: 'explorer')]
    public function index(Request $request, PostRepository $postRepository, CategoryRepository $categoryRepository, FavouriteRepository $favouriteRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $selectedCategoryId = $request->query->get('category');
        $user = $this->getUser();

        if ($selectedCategoryId === '') {
            $selectedCategoryId = null;
        }

        $posts = $postRepository->findByCategory($selectedCategoryId);

        $favouritedPosts = [];
        if ($user) {
            $favourites = $favouriteRepository->findBy(['user' => $user]);
            foreach ($favourites as $favourite) {
                $favouritedPosts[] = $favourite->getPost()->getId();
            }
        }

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategoryId,
            'favouritedPosts' => $favouritedPosts
        ]);
    }

    #[Route('/apiexplorer', name: 'apiexplorer')]
    public function JsonPosts(PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        $posts = $postRepository->findAll();
        $jsonPosts = $serializer->serialize($posts, 'json', ['groups' => 'post:read']);

        dump($jsonPosts); // Add this line to debug

        return $this->render('post/_api_explorer.html.twig', [
            'jsonPosts' => $jsonPosts
        ]);
    }


    #[Route('/posts', name: 'post_list')]
    public function list(Request $request, PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $selectedCategoryId = $request->query->get('category');

        $posts = $postRepository->findByCategory($selectedCategoryId);

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategoryId,
        ]);
    }


    public function show(Post $post, LikeRepository $likeRepository, FavouriteRepository $favouriteRepository): Response
    {
        $isLiked = false;
        $isFavourited = false;
        if ($this->getUser()) {
            $like = $likeRepository->findOneBy([
                'post' => $post,
                'user' => $this->getUser()
            ]);
            $isLiked = $like !== null;

            $favourite = $favouriteRepository->findOneBy([
                'post' => $post,
                'user' => $this->getUser()
            ]);
            $isFavourited = $favourite !== null;
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'isLiked' => $isLiked,
            'isFavourited' => $isFavourited
        ]);
    }


    public function create(
        Request $request,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        CloudinaryService $cloudinaryService
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour créer un post.');
                return $this->redirectToRoute('login');
            }

            $post->setAuthor($user);

            // Gestion de l'image
            $file = $form->get('featureImageFile')->getData();
            $link = $form->get('featureImage')->getData();

            if (($file && $link) || (!$file && !$link)) {
                $this->addFlash('error', 'Veuillez uploader un fichier OU saisir un lien, mais pas les deux.');
                return $this->render('post/new.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            if ($file) {
                $cloudResult = $cloudinaryService->uploadFeatureImage($file->getPathname());
                $post->setFeatureImage($cloudResult['secure_url']);
            } else {
                $cloudResult = $cloudinaryService->uploadFeatureImage($link);
                $post->setFeatureImage($cloudResult['secure_url']);
            }

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post created successfully');
            return $this->redirectToRoute('explorer');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView()
        ]);
    }


    public function edit(
        int $id,
        Request $request,
        PostRepository $postRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = $postRepository->find($id);

        if ($request->isMethod('POST')) {
            $featureImage = $request->request->get('featureImage');
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $content = $request->request->get('content');

            $post->setFeatureImage($featureImage);
            $post->setTitle($title);
            $post->setDescription($description);
            $post->setContent($content);

            $em->flush();

            $this->addFlash('success', 'Post mis à jour avec succès.');
            return $this->redirectToRoute('explorer');
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
        ]);
    }

    public function delete(int $id, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $post = $postRepository->find($id);

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('explorer');
    }

    #[Route('/post/{id}/report', name: 'post_report', methods: ['POST'])]
    public function report(Post $post, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post->setReport(true);
        $em->flush();

        $this->addFlash('success', 'Post reported successfully');
        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }
}
