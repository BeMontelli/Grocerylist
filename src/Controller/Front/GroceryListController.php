<?php

namespace App\Controller\Front;

use App\Entity\GroceryList;
use App\Entity\Ingredient;
use App\Entity\GroceryListIngredient;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\GroceryListType;
use App\Form\GroceryListCommentsType;
use App\Form\IngredientType;
use App\Form\RecipeType;
use App\Repository\GroceryListRepository;
use App\Service\FileUploader;
use App\Service\GroceryListIngredientService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\Persistence\Proxy;

#[Route("/{_locale}/lists", name: "page.list.", requirements: ['_locale' => 'fr|en'])]
class GroceryListController extends AbstractController
{
    private $translator;
    private $groceryListIngredientService;

    public function __construct(TranslatorInterface $translator, GroceryListIngredientService $groceryListIngredientService)
    {
        $this->translator = $translator;
        $this->groceryListIngredientService = $groceryListIngredientService;
    }

    #[Route('/{publicSlug}', name: 'show', requirements: ['publicSlug' => Requirement::ASCII_SLUG])]
    public function show(string $publicSlug, EntityManagerInterface $entityManager,Request $request): Response
    {
        /** @var GroceryList $groceryList  */
        $groceryList = $entityManager->getRepository(GroceryList::class)->findOneBy(['publicSlug' => $publicSlug]);
        
        if (empty($groceryList)) {
            $preferredLanguage = $request->getPreferredLanguage(['en', 'fr']);
            return $this->redirectToRoute('page.home', ["_locale" => $preferredLanguage]);
        }
        
        // fully load object
        $entityManager->refresh($groceryList);

        $form = $this->createForm(GroceryListCommentsType::class, $groceryList);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $entityManager->flush();
                return $this->redirectToRoute('page.list.show', ['publicSlug'=> $groceryList->getPublicSlug()], Response::HTTP_SEE_OTHER);
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }
        
        $groceryListIngredients = $groceryList->getGroceryListIngredients();
        // PROXY collection objects fully initialize if not
        foreach ($groceryListIngredients as $groceryListIngredient) {
            $ingredient = $groceryListIngredient->getIngredient();
            if ($ingredient instanceof Proxy) {
                $entityManager->initializeObject($ingredient);
            }
        }

        $recipes = $groceryList->getRecipes();
        // PROXY collection objects fully initialize if not
        foreach ($recipes as $recipe) {
            $recipeGroceryListIngredients = $recipe->getGroceryListIngredients();
            foreach ($recipeGroceryListIngredients as $groceryListIngredient) {
                if ($recipeGroceryListIngredients instanceof Proxy) {
                    $entityManager->initializeObject($recipeGroceryListIngredients);
                }
            }
        }

        return $this->render('front/grocery_list/show.html.twig', [
            'grocery_list' => $groceryList,
            'elements' => $this->groceryListIngredientService->getIngredientsStructured($groceryListIngredients->toArray()),
            'recipes' => $recipes,
            'form' => $form,
        ]);
    }

    #[Route('/toggle-grocerylistingredient-list/{grocerylistId}/{grocerylistingredientId}/{inlist}', name: 'toggleGroceryListIngredientList', requirements: ['grocerylistId' => Requirement::DIGITS,'grocerylistingredientId' => Requirement::DIGITS,'inlist' => 'remove|put'], methods: ['GET'])]
    public function toggleGroceryListIngredientFromList(int $grocerylistId,int $grocerylistingredientId,string $inlist,Request $request,EntityManagerInterface $em): Response
    {

        /** @var GroceryList $groceryList  */
        $groceryList = $em->find(GroceryList::class,$grocerylistId);
        /** @var GroceryListIngredient $grocerylistingredientId  */
        $groceryListIngredient = $em->find(GroceryListIngredient::class,$grocerylistingredientId);

        if(!$groceryList || !$groceryListIngredient) {
            $preferredLanguage = $request->getPreferredLanguage(['en', 'fr']);
            return $this->redirectToRoute('page.home', ["_locale" => $preferredLanguage]);
        }

        $inListTarget = !($inlist === 'remove');

        $groceryListIngredient->setInList($inListTarget);
        $groceryListIngredient->setActivation(false);
        $em->persist($groceryListIngredient);

        $groceryList->setUpdatedAt(new \DateTimeImmutable());
        $em->persist($groceryList);

        $em->flush();

        $url = $this->generateUrl('page.list.show', [
            'publicSlug' => $groceryList->getPublicSlug()
        ]) . '?group=grocerylist&gid=recipes';

        return $this->redirect($url, Response::HTTP_SEE_OTHER);
    }
}
