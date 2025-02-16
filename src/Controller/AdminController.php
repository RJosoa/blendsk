<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class AdminController extends AbstractController
{

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function userlist(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $usersList = $userRepository->findAll();

        $jsonComments = $serializer->serialize($usersList, 'json', ['groups' => 'user:read']);
        return new JsonResponse($jsonComments, 200, [], true);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function userById(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($id);
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($jsonUser, 200, [], true);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateRoles(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found']);
        }

        $data = json_decode($request->getContent(), true);

        $roles = $data['roles'];

        $user->setRoles($roles);
        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'User role updated',
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function commentsList(CommentRepository $commentRepository, SerializerInterface $serializer): JsonResponse
    {
        // $commentsList = $commentRepository->findAll();
        $commentsList = $commentRepository->findBy(['report' => true]);

        if (!$commentsList) {
            return new JsonResponse(['message' => 'CommentsList not found']);
        }

        $jsonComments = $serializer->serialize($commentsList, 'json', ['groups' => 'comment:read']);

        return new JsonResponse($jsonComments, 200, [], true);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function commentDelete(int $id, CommentRepository $commentRepository, EntityManagerInterface $em): JsonResponse
    {
        $comment = $commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['message' => 'Comment not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $em->remove($comment);
        $em->flush();
        return new JsonResponse(['message' => 'Comment deleted'], JsonResponse::HTTP_OK);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function postList(PostRepository $postRepository, SerializerInterface $serializer)
    {
        $postsList = $postRepository->findBy(['report' => true]);
        if (!$postsList) {
            return new JsonResponse(['message' => 'PostsList not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $jsonPosts = $serializer->serialize($postsList, 'json', ['groups' => 'post:read']);
        return new JsonResponse($jsonPosts, 200, [], true);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function postDelete(int $id, PostRepository $postRepository, EntityManagerInterface $em): JsonResponse
    {
        $post = $postRepository->find($id);
        if (!$post) {
            return new JsonResponse(['message' => 'Post not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $em->remove($post);
        $em->flush();
        return new JsonResponse(['message' => 'Post deleted'], JsonResponse::HTTP_OK);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function categoryCreate(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $em)
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'];

        $category = $categoryRepository->findOneBy(['name' => $name]);
        if ($category) {
            return $this->json(['message' => 'Category already exists!'], Response::HTTP_CONFLICT);
        }

        $category = new Category();
        $category->setName($name);
        $em->persist($category);
        $em->flush();

        return $this->json(['message' => 'Category created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function categoryEdit(Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $category = $categoryRepository->find($id);

        if (!$category) {
            return $this->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['message' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }

        $existingCategory = $categoryRepository->findOneBy(['name' => $data['name']]);
        if ($existingCategory && $existingCategory->getId() !== $category->getId()) {
            return $this->json(['message' => 'A category with this name already exists'], Response::HTTP_CONFLICT);
        }

        $category->setName($data['name']);
        $em->flush();

        return $this->json([
            'message' => 'Category updated successfully',
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName()
            ]
        ], Response::HTTP_OK);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function categoryList(CategoryRepository $categoryRepository, SerializerInterface $serializer): JsonResponse
    {
        $categories = $categoryRepository->findAll();

        $jsonCategory = $serializer->serialize($categories, 'json', ['groups' => 'category:read']);

        return new JsonResponse($jsonCategory, 200, [], true);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function categoryById(int $id, CategoryRepository $categoryRepository, SerializerInterface $serializer)
    {
        $category = $categoryRepository->find($id);

        $jsonCategory = $serializer->serialize($category, 'json', ['groups' => 'category:read']);

        return new JsonResponse($jsonCategory, 200, [], true);
    }
}
