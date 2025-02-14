<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route("/{_locale}/admin/categories", name: "admin.category.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class CategoryController extends AbstractController
{
    private $security;
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }

    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user  */
        $user = $this->security->getUser();

        $categories = $categoryRepository->findAllWithRecipesTotal($user);

        // form new
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
            
        if($form->isSubmitted()) {
            if($form->isValid()) {
                $category->setUser($user);
                $entityManager->persist($category);
                $entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.notif.saved', [
                    '%entity%' => $this->translator->trans('app.admin.categories.entity',['%entity%' => '1']),
                    '%gender%' => 'female'
                ]));
                return $this->redirectToRoute('admin.category.index');
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'form' => $form
        ]);
    }

    #[Route('/{slug}-{id}', name: 'show', requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG])]
    public function show(Category $category): RedirectResponse
    {
        return $this->redirectToRoute('admin.category.edit', ["id" => $category->getId()]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user  */
        $user = $this->security->getUser();

        // Fix Proxy/Proxies instances of Class w/ $em->detach()
        //$entityManager->detach($user);
        //$user = $entityManager->find(User::class, $user->getId());
        //dump($user); // App\Entity\User
        //dd($category->getUser()); // Proxies\__CG__\App\Entity\User

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if($form->isValid()) {
                $category->setUser($user);
                $entityManager->persist($category);
                $entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.notif.edited',[
                    '%entity%' => $this->translator->trans('app.admin.categories.entity',['%entity%' => '1']),
                    '%gender%' => 'female'
                ]));
                return $this->redirectToRoute('admin.category.index');
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager) {

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('warning', $category->getTitle().': '.$this->translator->trans('app.notif.deleted', [
                '%entity%' => $this->translator->trans('app.admin.categories.entity',['%entity%' => '1']),
                '%gender%' => 'female'
            ]));
        } else {
            $this->addFlash('danger', $this->translator->trans('app.notif.erroccur'));
        }

        return $this->redirectToRoute('admin.category.index');
    }
}
