<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class ConferenceController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferences): Response
    {
        return $this->render('conference/index.html.twig', [
            'conferences' => $conferences->findAll(),
        ]);
    }

    #[Route('/conference/{id}', name: 'conference')]
    public function show(
        Conference $conference,
        CommentRepository $comments,
        #[MapQueryParameter()]
        int $offset = 0,
    ): Response
    {
        $paginator = $comments->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::COMMENTS_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::COMMENTS_PER_PAGE),
        ]);
    }
}
