<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractController
{
    public function list(CategoryRepository $categoryRepository,): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $categories = $categoryRepository->findAll();
        return $this->render('category/list.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    public function create(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($request->isMethod('POST')) {
            $category = new Category();
            $name = $request->request->get('name');

            if (!$name) {
                $this->addFlash('error', 'Tous les champs sont requis.');
                return $this->render('post/new.html.twig');
            }

            $category->setName($name);


            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'category créé avec succès.');
            return $this->redirectToRoute('explorer');
        }

        return $this->render('category/new.html.twig');
    }

    public function edit(
        int $id,
        Request $request,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = $categoryRepository->find($id);

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');

            $category->setName($name);


            $em->flush();

            $this->addFlash('success', 'Post mis à jour avec succès.');
            return $this->redirectToRoute('explorer');
        }

        return $this->render('post/edit.html.twig', [
            'category' => $category,
        ]);
    }

    public function delete(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $post = $categoryRepository->find($id);

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('explorer');
    }
}
