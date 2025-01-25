<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends AbstractController
{
    #[Route('/comments', name: 'app_comment')]
    public function list(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findAll();


        return $this->render('comment/list.html.twig', [
            'comments' => $comments
        ]);
    }

    #[Route('/post/{id}/comment', name: 'post_comment_create', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        PostRepository $postRepository,
        EntityManagerInterface $em
    ): Response {
        $post = $postRepository->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé.');
        }

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour commenter.');
        }

        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');
            $report = $request->request->get('report') ? true : false;

            if (!$content) {
                $this->addFlash('error', 'Le contenu du commentaire est obligatoire.');
                return $this->redirectToRoute('post_show', ['id' => $id]);
            }

            $comment = new Comment();
            $comment->setContent($content);
            $comment->setReport($report);
            $comment->setPost($post);
            $comment->setUser($user);

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            return $this->redirectToRoute('post_show', ['id' => $id]);
        }

        return $this->redirectToRoute('post_show', ['id' => $id]);
    }

    public function show(): Response
    {
        return $this->render('comment/show.html.twig');
    }

    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Vérifier que seul l'auteur ou un administrateur peut modifier
        if ($comment->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de modifier ce commentaire.');
            return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
        }

        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');
            $comment->setContent($content);

            $em->flush();

            $this->addFlash('success', 'Commentaire modifié avec succès.');
            return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    public function delete(int $id, CommentRepository $commentRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $comment = $commentRepository->find($id);

        if (!$comment) {
            $this->addFlash('error', 'Comment not found.');
            return $this->redirectToRoute('post_index');
        }

        if ($comment->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'You are not allowed to delete this comment.');
            return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
        }

        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', 'Comment successfully deleted.');

        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }

    #[Route('/comment/{id}/report', name: 'comment_report', methods: ['POST'])]
    public function report(Comment $comment, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $comment->setReport(true);
        $em->flush();

        $this->addFlash('success', 'Comment reported successfully');
        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }
}
