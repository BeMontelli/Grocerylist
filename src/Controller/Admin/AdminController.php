<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\User;
use App\Entity\GroceryList;

#[Route("/{_locale}/admin", name: "admin.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class AdminController extends AbstractController
{
    private $security;
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }
    
    #[Route('/', name: 'dashboard')]
    public function dashboard(Request $request): Response 
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var GroceryList $currentGrocerylist */
        $currentGrocerylist = ($user->getCurrentGroceryList()) ? $user->getCurrentGroceryList(): null;

        return $this->render('admin/pages/dashboard.html.twig', [
            'currentGrocerylist' => $currentGrocerylist,
        ]);
    }
}
