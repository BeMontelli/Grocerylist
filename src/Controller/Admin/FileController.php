<?php

namespace App\Controller\Admin;

use App\Entity\File;
use App\Form\FileType;
use App\Entity\User;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\FileUploader;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route("/{_locale}/admin/files", name: "admin.file.", requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_ADMIN')]
class FileController extends AbstractController
{
    private $security;
    private $translator;
    private $fileUploader;

    public function __construct(Security $security, TranslatorInterface $translator, FileUploader $fileUploader)
    {
        $this->security = $security;
        $this->translator = $translator;
        $this->fileUploader = $fileUploader;
    }

    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, FileRepository $fileRepository): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $file = new File();
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $formFile = $form->get('file')->getData();
                if ($formFile) {
                    $extension = $formFile->getClientOriginalExtension();
                    $titleFile = str_replace(".".$extension, "", $formFile->getClientOriginalName());
                    $filePath = $this->fileUploader->uploadRecipeThumbnail($formFile);
                    
                    $file->setTitle($titleFile);
                    $file->setUrl($filePath);
                    $file->setExtension($extension);
                    $file->setUser($user);

                    $entityManager->persist($file);
                    $entityManager->flush();

                    $this->addFlash('success', 'File saved !');
                    return $this->redirectToRoute('admin.file.index');
                } else $this->addFlash('danger', $this->translator->trans('app.notif.fileinvalid'));
            } else $this->addFlash('danger', $this->translator->trans('app.notif.validerr'));
        }

        return $this->render('admin/file/index.html.twig', [
            'files' => $fileRepository->findAllByUser(user: $user),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
    public function show(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/file/show.html.twig', [
            'file' => $file
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$file->getId(), $request->getPayload()->get('_token'))) {
            $currentThumbnail = $file->getUrl();
            if(!empty($currentThumbnail)) {
                $fileDir = $this->getParameter('kernel.project_dir').'/public';
                $this->fileUploader->deleteThumbnail($fileDir,$currentThumbnail);
            }
    
            $entityManager->remove($file);
            $entityManager->flush();
            $this->addFlash('warning', 'File deleted !');
        } else {
            $this->addFlash('danger', 'Error occured !');
        }

        return $this->redirectToRoute('admin.file.index', [], Response::HTTP_SEE_OTHER);
    }
}
