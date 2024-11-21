<?php

namespace App\DTO;

use App\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SearchRecipesDTO
{
    #[Assert\Sequentially([
        new Assert\Length(max: 30)
    ])]
    public ?string $title = null;

    #[Assert\All([
        new Assert\Type(Category::class),
    ])]
    public array $categories = [];

    #[Assert\Callback]
    public function validateAtLeastOneField(ExecutionContextInterface $context): void
    {
        if (empty($this->title) && empty($this->categories)) {
            $context->buildViolation('Please fill in at least one field (title or categories).')
                ->atPath('title')
                ->addViolation();
            /*$context->buildViolation('Please fill in at least one field (title or categories).')
                 ->atPath('categories')
                 ->addViolation();*/
        }
    }

}