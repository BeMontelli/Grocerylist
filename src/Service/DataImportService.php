<?php
namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\GroceryList;
use App\Entity\Recipe;
use App\Entity\GroceryListIngredient;
use App\Entity\Section;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class DataImportService
{
    private EntityManagerInterface $em;

    private $sections;
    private $builtSections;
    private $categories;
    private $ingredients;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->sections = [
            "Conserves",
            "Hygiene",
            "Ménage/Vaisselle",
            "Cuisine",
            "Matin/Biscuits",
            "Boissons",
            "Desserts",
            "Apéritifs",
            "Surgelés",
            "Condiments/Sauces",
            "Pâtes/Riz",
            "Plats cuisinés",
            "Boulangerie",
            "Épicerie",
            "Fruits",
            "Légumes",
            "Laitages",
            "Fromages",
            "Boucherie/Viandes",
            "Poissonnerie/Poissons",
            "Autres"
        ];
        $this->builtSections = [];

        $this->categories = [
            "Entrée",
            "Plat principal",
            "Dessert",
            "Apéritif",
            "Boisson",
            "Autre"
        ];

        $this->ingredients = [
            //Épicerie
            [
                "title" => 'Sel',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Poivre',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Jus de citron',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Epices à frites',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Basilic',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Herbes de provence',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Persillade',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Ail',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Œufs/Oeufs',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Sucre Cassonade',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Sucre blanc',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Bouillon Cube',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Cornichons',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Olives verte',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Olives noire',
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => "Huile d'olive",
                'section' => 'Épicerie',
                'recipeOK' => true
            ],
            [
                "title" => "Menthe",
                'section' => 'Épicerie',
                'recipeOK' => true
            ],

            //Légumes
            [
                "title" => 'Salade',
                'section' => 'Légumes',
                'recipeOK' => true
            ],
            [
                "title" => 'Tomates',
                'section' => 'Légumes',
                'recipeOK' => true
            ],
            [
                "title" => 'Oignons',
                'section' => 'Légumes',
                'recipeOK' => true
            ],
            [
                "title" => 'Poivrons',
                'section' => 'Légumes',
                'recipeOK' => true
            ],
            [
                "title" => 'Pommes de terre',
                'section' => 'Légumes',
                'recipeOK' => true
            ],

            //Conserves
            [
                "title" => 'Haricots',
                'section' => 'Conserves',
                'recipeOK' => true
            ],
            [
                "title" => 'Flageolets',
                'section' => 'Conserves',
                'recipeOK' => true
            ],
            [
                "title" => 'Champignons de Paris',
                'section' => 'Conserves',
                'recipeOK' => true
            ],
            [
                "title" => 'Raviolis',
                'section' => 'Conserves',
                'recipeOK' => true
            ],

            //Pâtes/Riz
            [
                "title" => 'Riz Basmati',
                'section' => 'Pâtes/Riz',
                'recipeOK' => true
            ],
            [
                "title" => 'Riz rond',
                'section' => 'Pâtes/Riz',
                'recipeOK' => true
            ],
            [
                "title" => 'Pâtes Penne',
                'section' => 'Pâtes/Riz',
                'recipeOK' => true
            ],
            [
                "title" => 'Spaghettis',
                'section' => 'Pâtes/Riz',
                'recipeOK' => true
            ],
            [
                "title" => 'Noodles',
                'section' => 'Pâtes/Riz',
                'recipeOK' => true
            ],

            //Plats cuisinés
            [
                "title" => 'Pastabox',
                'section' => 'Plats cuisinés',
                'recipeOK' => false
            ],
            [
                "title" => 'Manchons de poulet',
                'section' => 'Plats cuisinés',
                'recipeOK' => false
            ],

            //Boulangerie
            [
                "title" => 'Baguette de pain',
                'section' => 'Boulangerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Pain de mie',
                'section' => 'Boulangerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Pains burger',
                'section' => 'Boulangerie',
                'recipeOK' => true
            ],
            [
                "title" => 'Galette Wrap',
                'section' => 'Boulangerie',
                'recipeOK' => true
            ],

            //Fruits
            [
                "title" => 'Pommes',
                'section' => 'Fruits',
                'recipeOK' => true
            ],
            [
                "title" => 'Bananes',
                'section' => 'Fruits',
                'recipeOK' => true
            ],
            [
                "title" => 'Nectarines',
                'section' => 'Fruits',
                'recipeOK' => true
            ],
            [
                "title" => 'Oranges',
                'section' => 'Fruits',
                'recipeOK' => true
            ],
            [
                "title" => 'Mangues',
                'section' => 'Fruits',
                'recipeOK' => true
            ],
            [
                "title" => 'Poires',
                'section' => 'Fruits',
                'recipeOK' => true
            ],
            [
                "title" => 'Kiwi',
                'section' => 'Fruits',
                'recipeOK' => true
            ],
            [
                "title" => 'Amandes',
                'section' => 'Fruits',
                'recipeOK' => true
            ],

            //Poissonnerie/Poissons
            [
                "title" => 'Thon naturel',
                'section' => 'Poissonnerie/Poissons',
                'recipeOK' => true
            ],
            [
                "title" => 'Maquereaux',
                'section' => 'Poissonnerie/Poissons',
                'recipeOK' => false
            ],
            [
                "title" => 'Rillettes de saumon',
                'section' => 'Poissonnerie/Poissons',
                'recipeOK' => true
            ],
            [
                "title" => 'Sardines',
                'section' => 'Poissonnerie/Poissons',
                'recipeOK' => false
            ],
            [
                "title" => 'Saumon',
                'section' => 'Poissonnerie/Poissons',
                'recipeOK' => true
            ],
            [
                "title" => 'Surimi',
                'section' => 'Poissonnerie/Poissons',
                'recipeOK' => true
            ],

            //Boucherie/Viandes
            [
                "title" => 'Dinde',
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => 'Dinde en tranches',
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => 'Roti en tranches',
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => 'Canard',
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => 'Jambon cru',
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => 'Jambon blanc',
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => "Saucisson à l'ail",
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => "Chorizo",
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => "Lardons",
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],
            [
                "title" => "Bacon",
                'section' => 'Boucherie/Viandes',
                'recipeOK' => true
            ],

            //Surgelés
            [
                "title" => 'Cordons bleu',
                'section' => 'Surgelés',
                'recipeOK' => true
            ],
            [
                "title" => 'Steacks hachés',
                'section' => 'Surgelés',
                'recipeOK' => true
            ],
            [
                "title" => 'Pizza',
                'section' => 'Surgelés',
                'recipeOK' => true
            ],
            [
                "title" => 'Poisson pané',
                'section' => 'Surgelés',
                'recipeOK' => true
            ],
            [
                "title" => 'Pommes de terre grenailles',
                'section' => 'Surgelés',
                'recipeOK' => true
            ],
            [
                "title" => 'Potatoes',
                'section' => 'Surgelés',
                'recipeOK' => true
            ],

            //Condiments/Sauces
            [
                "title" => 'Sauce tomate',
                'section' => 'Condiments/Sauces',
                'recipeOK' => true
            ],
            [
                "title" => 'Sauce soja',
                'section' => 'Condiments/Sauces',
                'recipeOK' => true
            ],
            [
                "title" => 'Sauce salade',
                'section' => 'Condiments/Sauces',
                'recipeOK' => true
            ],
            [
                "title" => 'Sauce frites',
                'section' => 'Condiments/Sauces',
                'recipeOK' => true
            ],

            //Laitages
            [
                "title" => 'Beurre',
                'section' => 'Laitages',
                'recipeOK' => true
            ],
            [
                "title" => 'Yaourts',
                'section' => 'Laitages',
                'recipeOK' => true
            ],
            [
                "title" => 'Petits suisse',
                'section' => 'Laitages',
                'recipeOK' => true
            ],
            [
                "title" => 'Riz au lait',
                'section' => 'Laitages',
                'recipeOK' => true
            ],
            [
                "title" => 'Lait',
                'section' => 'Laitages',
                'recipeOK' => true
            ],
            [
                "title" => 'Fromage blanc',
                'section' => 'Laitages',
                'recipeOK' => true
            ],
            [
                "title" => 'Crème fraiche',
                'section' => 'Laitages',
                'recipeOK' => true
            ],
            [
                "title" => 'Bechamel',
                'section' => 'Laitages',
                'recipeOK' => true
            ],

            //Fromages
            [
                "title" => 'Buche de chèvre',
                'section' => 'Fromages',
                'recipeOK' => true
            ],
            [
                "title" => 'Fromage à tartiner',
                'section' => 'Fromages',
                'recipeOK' => true
            ],
            [
                "title" => 'Gruyère/Emmental',
                'section' => 'Fromages',
                'recipeOK' => true
            ],
            [
                "title" => 'Mozzarella',
                'section' => 'Fromages',
                'recipeOK' => true
            ],
            [
                "title" => 'Parmesan',
                'section' => 'Fromages',
                'recipeOK' => true
            ],
            [
                "title" => 'Fromage bleu',
                'section' => 'Fromages',
                'recipeOK' => true
            ],
            [
                "title" => 'Camembert',
                'section' => 'Fromages',
                'recipeOK' => true
            ],

            //Boissons
            [
                "title" => 'Eau gazeuse',
                'section' => 'Boissons',
                'recipeOK' => true
            ],
            [
                "title" => 'Jus de fruit',
                'section' => 'Boissons',
                'recipeOK' => true
            ],
            [
                "title" => 'Coca',
                'section' => 'Boissons',
                'recipeOK' => false
            ],
            [
                "title" => 'Coca Cherry',
                'section' => 'Boissons',
                'recipeOK' => false
            ],
            [
                "title" => 'Sirop',
                'section' => 'Boissons',
                'recipeOK' => false
            ],

            //Matin/Biscuits
            [
                "title" => 'Cacao',
                'section' => 'Matin/Biscuits',
                'recipeOK' => true
            ],
            [
                "title" => 'Brioche',
                'section' => 'Matin/Biscuits',
                'recipeOK' => true
            ],
            [
                "title" => 'Cookies',
                'section' => 'Matin/Biscuits',
                'recipeOK' => false
            ],
            [
                "title" => 'Petits Lu',
                'section' => 'Matin/Biscuits',
                'recipeOK' => false
            ],

            //Desserts
            [
                "title" => 'Mousse au chocolat',
                'section' => 'Desserts',
                'recipeOK' => false
            ],
            [
                "title" => 'Profiteroles',
                'section' => 'Desserts',
                'recipeOK' => false
            ],
            [
                "title" => 'Tiramisu',
                'section' => 'Desserts',
                'recipeOK' => false
            ],
            [
                "title" => 'Brownies',
                'section' => 'Desserts',
                'recipeOK' => false
            ],
            [
                "title" => 'Tablettes de chocolat au lait',
                'section' => 'Desserts',
                'recipeOK' => true
            ],

            //Apéritifs
            [
                "title" => 'Chips',
                'section' => 'Apéritifs',
                'recipeOK' => false
            ],
            [
                "title" => 'Chips crevettes',
                'section' => 'Apéritifs',
                'recipeOK' => false
            ],
            [
                "title" => 'Pringles',
                'section' => 'Apéritifs',
                'recipeOK' => false
            ],
            [
                "title" => 'Guacamole',
                'section' => 'Apéritifs',
                'recipeOK' => false
            ],
            [
                "title" => 'Houmous',
                'section' => 'Apéritifs',
                'recipeOK' => false
            ],
            [
                "title" => 'Tzatziki',
                'section' => 'Apéritifs',
                'recipeOK' => false
            ],
            [
                "title" => 'Pois Wazabi',
                'section' => 'Apéritifs',
                'recipeOK' => false
            ],

            //Hygiene
            [
                "title" => 'Déodorant',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Gel douche',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Rince bouche',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Mouchoirs',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Sopalin',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Papier toilette',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Lingettes nettoyante',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Cotons tige',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Dentifrice',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Brosse à dents',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Crème pour les mains',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Beaume à lèvre',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Rasoirs',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],
            [
                "title" => 'Parfum',
                'section' => 'Hygiene',
                'recipeOK' => false
            ],

            //Ménage/Vaisselle
            [
                "title" => 'Sac poubelle',
                'section' => 'Ménage/Vaisselle',
                'recipeOK' => false
            ],
            [
                "title" => 'Désodorisant',
                'section' => 'Ménage/Vaisselle',
                'recipeOK' => false
            ],
            [
                "title" => 'Adoucissant',
                'section' => 'Ménage/Vaisselle',
                'recipeOK' => false
            ],
            [
                "title" => 'Lessive',
                'section' => 'Ménage/Vaisselle',
                'recipeOK' => false
            ],
            [
                "title" => 'Produit sol',
                'section' => 'Ménage/Vaisselle',
                'recipeOK' => false
            ],
            // TO continue

            //Cuisine
            [
                "title" => 'Sac congélation',
                'section' => 'Cuisine',
                'recipeOK' => false
            ],
            [
                "title" => 'Eponges',
                'section' => 'Cuisine',
                'recipeOK' => false
            ],
            [
                "title" => 'Papier aluminum',
                'section' => 'Cuisine',
                'recipeOK' => false
            ],
            [
                "title" => 'Papier cuisson',
                'section' => 'Cuisine',
                'recipeOK' => false
            ],
            [
                "title" => 'Produit vaisselle',
                'section' => 'Cuisine',
                'recipeOK' => false
            ],
            [
                "title" => 'Grattoir',
                'section' => 'Cuisine',
                'recipeOK' => false
            ],

            //Autres
            [
                "title" => 'Chew gum',
                'section' => 'Autres',
                'recipeOK' => false
            ],
            [
                "title" => 'Boules Quies',
                'section' => 'Autres',
                'recipeOK' => false
            ],
            [
                "title" => 'Piles AAA',
                'section' => 'Autres',
                'recipeOK' => false
            ],
            [
                "title" => 'Piles CR 2032',
                'section' => 'Autres',
                'recipeOK' => false
            ],
            // TO continue
        ];
    }

    public function importData($user) : void {
        $this->createSections($user);
        $this->createCategories($user);
        $this->createIngredients($user);
    }

    public function createSections($user) : void {
        foreach ($this->sections as $section) {
            $existing = $this->em->getRepository(Section::class)
                ->findOneBy(['title' => $section,'user' => $user]);
    
            if (!$existing) $this->createSection($section,$user);
        }
    }

    public function createSection(string $section,$user): void {
        $slugger = new AsciiSlugger();

        $newSection = new Section();
        $newSection->setTitle($section);

        $slug = strtolower($slugger->slug($section));
        $newSection->setSlug($slug);

        $newSection->setUser($user);

        $newSection->setCreatedAt(new \DateTimeImmutable());
        $newSection->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($newSection);
        $this->em->flush();
        $this->builtSections[] = $newSection;
    }

    public function createCategories($user) : void {
        foreach ($this->categories as $category) {
            $existing = $this->em->getRepository(Category::class)
                ->findOneBy(['title' => $category,'user' => $user]);
    
            if (!$existing) $this->createCategory($category,$user);
        }
    }

    public function createCategory(string $category,$user): void {
        $slugger = new AsciiSlugger();

        $newCategory = new Category();
        $newCategory->setTitle($category);

        $slug = strtolower($slugger->slug($category));
        $newCategory->setSlug($slug);

        $newCategory->setUser($user);

        $newCategory->setCreatedAt(new \DateTimeImmutable());
        $newCategory->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($newCategory);
        $this->em->flush();
    }

    public function createIngredients($user, $sections = null) : void {
        foreach ($this->ingredients as $ingredient) {
            $existing = $this->em->getRepository(Section::class)
                ->findOneBy(['title' => $ingredient['title'],'user' => $user]);
    
            if (!$existing) $this->createIngredient($ingredient,$user,$sections);
        }
    }

    public function createIngredient(array $ingredient, $user, $sections = null): void {

        if(!$sections) $sections = $this->builtSections;

        $slugger = new AsciiSlugger();

        $newIngredient = new Ingredient();
        $newIngredient->setTitle($ingredient['title']);

        $slug = strtolower($slugger->slug($ingredient['title']));
        $newIngredient->setSlug($slug);

        $newIngredient->setAvailableRecipe(availableRecipe: $ingredient['recipeOK']);

        $titleToFind = $ingredient['section'];
        $matchingSections = array_filter($sections, function($section) use ($titleToFind) {
            return $section->getTitle() === $titleToFind;
        });
        $matchingSections = array_values($matchingSections);
        if($matchingSections && count($matchingSections) > 0) {
            $newIngredient->setSection($matchingSections[0]);
        }
        $newIngredient->setUser($user);

        $newIngredient->setCreatedAt(new \DateTimeImmutable());
        $newIngredient->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($newIngredient);
        $this->em->flush();
    }
}