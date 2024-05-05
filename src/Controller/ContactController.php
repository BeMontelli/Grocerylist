<?php

namespace App\Controller;

use App\DTO\ContactDTO;
use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'page.contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $contact = new ContactDTO();
        $form = $this->createForm(ContactType::class,$contact);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            try {
                $email = (new TemplatedEmail())
                    ->from('do-not-answer@grocerylist.fr')
                    ->to('admin@grocerylist.fr')
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject('Contact Grocerylist')
                    ->htmlTemplate('emails/contact.html.twig')
                    ->context(['data'=>$form->getData()]);

                $mailer->send($email);
                $this->addFlash('success', 'Email sent !');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'Error email, please try later !');
            }

            return $this->redirectToRoute('page.contact');
        }

        return $this->render('pages/contact.html.twig', [
            'form' => $form
        ]);
    }
}
