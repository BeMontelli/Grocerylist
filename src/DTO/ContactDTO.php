<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ContactDTO
{
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(max:100),
    ])]
    public string $name = '';

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Email(),
        new Assert\Length(min:3,max:100),
    ])]
    public string $email = '';

    public string $message = '';

}