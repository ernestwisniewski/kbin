<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\User;
use BadMethodCallException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use function is_string;

/**
 * @method User|null getUser()
 */
abstract class AbstractController extends BaseAbstractController
{
    protected function getUserOrThrow(): User
    {
        $user = $this->getUser();

        if (!$user) {
            throw new BadMethodCallException('User is not logged in');
        }

        return $user;
    }

    protected function validateCsrf(string $id, $token): void
    {
        if (!is_string($token) || !$this->isCsrfTokenValid($id, $token)) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }
    }

    protected function redirectToRefererOrHome(Request $request): Response
    {
        if (!$request->headers->has('Referer')) {
            return $this->redirectToRoute('front');
        }

        return $this->redirect($request->headers->get('Referer'));
    }

    protected function getJsonSuccessResponse(): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => true,
            ]
        );
    }

    protected function getJsonFormResponse(FormInterface $form, string $template, ?array $variables = null): JsonResponse
    {
        return new JsonResponse(
            [
                'form' => $this->renderView(
                    $template,
                    [
                        'form' => $form->createView(),
                    ] + ($variables ?? [])
                ),
            ]
        );
    }

    protected function getPageNb(Request $request): int
    {
        return (int) $request->get('p', 1);
    }

    protected function redirectToEntry(Entry $entry): Response
    {
        return $this->redirectToRoute(
            'entry_single',
            [
                'magazine_name' => $entry->magazine->name,
                'entry_id'      => $entry->getId(),
                'slug'          => $entry->slug,
            ]
        );
    }

    protected function redirectToPost(Post $post): Response
    {
        return $this->redirectToRoute(
            'post_single',
            [
                'magazine_name' => $post->magazine->name,
                'post_id'       => $post->getId(),
                'slug'          => $post->slug,
            ]
        );
    }

    protected function redirectToMagazine(Magazine $magazine): Response
    {
        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->name,
            ]
        );
    }
}
