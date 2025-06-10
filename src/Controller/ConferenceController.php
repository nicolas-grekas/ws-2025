<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentTypeForm;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

final class ConferenceController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferences): Response
    {
        return $this->render('conference/index.html.twig', [
            'conferences' => $conferences->findAll(),
        ]);
    }

    #[Route('/conference/{slug:conference}', name: 'conference')]
    public function show(
        Request $request,
        Conference $conference,
        EntityManagerInterface $entityManager,
        #[Autowire(param: 'photo_dir')]
        string $photoDir,

        #[AutowireMethodOf(CommentRepository::class)]
        \Closure $getCommentPaginator,

        #[MapQueryParameter(options: ['min_range' => 0])]
        int $offset = 0,

    ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentTypeForm::class, $comment);
        $emptyForm = clone $form;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);

            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                $photo->move($photoDir, $filename);
                $comment->setPhotoFilename($filename);
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->renderBlock('conference/show.html.twig', 'form_stream', [
                    'comment' => $comment,
                    'comment_form' => $emptyForm,
                ]);
            }

            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        $paginator = $getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::COMMENTS_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::COMMENTS_PER_PAGE),
            'comment_form' => $form,
        ]);
    }
}
