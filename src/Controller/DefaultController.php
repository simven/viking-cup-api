<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(AuthorizationCheckerInterface $authChecker): RedirectResponse
    {
        if ($authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin_sponsor_index');
        }

        return $this->redirectToRoute('login');
    }
}
