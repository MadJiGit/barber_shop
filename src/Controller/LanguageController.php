<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    #[Route('/locale/{_locale}', name: 'app_locale', requirements: ['_locale' => 'bg|en'])]
    //    #[Route('/locale/{_locale}', name: 'app_locale', requirements: ['_locale' => 'bg|en'])]
    public function switchLocale(Request $request, string $_locale): RedirectResponse
    {
        $request->getSession()->set('_locale', $_locale);

        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('main');
    }
}
