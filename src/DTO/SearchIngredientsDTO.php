<?php

namespace App\DTO;

use App\Entity\Section;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SearchIngredientsDTO
{
    #[Assert\Sequentially([
        new Assert\Length(max: 30)
    ])]
    public ?string $title = null;

    #[Assert\All([
        new Assert\Type(Section::class),
    ])]
    public array $sections = [];

    #[Assert\Callback]
    public function validateAtLeastOneField(ExecutionContextInterface $context): void
    {
        if (empty($this->title) && empty($this->sections)) {
            $context->buildViolation('Please fill in at least one field (title or sections).')
                ->atPath('title')
                ->addViolation();
            /*$context->buildViolation('Please fill in at least one field (title or sections).')
                 ->atPath('sections')
                 ->addViolation();*/
        }
    }

}