<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/billboard/manage/categories", name="manage_billboard_category_legacy_redirect")
 */
final class RedirectController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->redirectToRoute('manage_billboard_category_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
