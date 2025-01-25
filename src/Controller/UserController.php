<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    public function show(int $id, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {
            $user = new User();
            $name = $request->request->get('name');
            $username = $request->request->get('username');
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $termsAccept = $request->request->get('termsAccept', false);

            if (!$name || !$username || !$email || !$plainPassword) {
                $this->addFlash('error', 'Tous les champs sont requis.');
                return $this->render('user/new.html.twig');
            }

            $user->setName($name);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setTermsAccepted($termsAccept);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('login');
        }

        return $this->render('user/new.html.twig');
    }

    public function edit(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $role = $request->request->get('role', 'ROLE_USER');

            if (!$name || !$email) {
                $this->addFlash('error', 'Tous les champs sont requis.');
                return $this->render('user/edit.html.twig', ['user' => $user]);
            }

            $user->setName($name);
            $user->setEmail($email);
            $user->setRoles([$role]);

            $em->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', ['user' => $user]);
    }

    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted'], JsonResponse::HTTP_OK);
    }
}
