<?php

namespace App\Controller;

use App\Entity\MfcRequest;
use App\Form\MfcStep1Type;
use App\Form\MfcStep2Type;
use App\Service\MfcFileStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[IsGranted("ROLE_USER")]
#[Route('/mfc')]
class MfcController extends AbstractController
{
    #[Route('/', name: 'mfc')]
    public function start(EntityManagerInterface $em): Response
    {
        $req = new MfcRequest();
        $req->setOwner($this->getUser());

        $em->persist($req);
        $em->flush();

        return $this->redirectToRoute('mfc_step1', ['id' => $req->getId()]);
    }

    #[Route('/workflow/{id}/step-1', name: 'mfc_step1', methods: ['GET', 'POST'])]
    public function step1(
        MfcRequest $req,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        if ($req->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        if ($req->getState() !== MfcRequest::STATE_STEP1) {
            return $this->redirectToRoute('mfc_step2', ['id' => $req->getId()]);
        }

        $form = $this->createForm(MfcStep1Type::class, $req, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $req->touch();
            $em->flush();

            return $this->redirectToRoute('mfc_next', ['id' => $req->getId()]);
        }

        return $this->render('mfc/step1.html.twig', [
            'req' => $req,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/workflow/{id}/next', name: 'mfc_next', methods: ['POST', 'GET'])]
    public function next(
        MfcRequest $req,
        EntityManagerInterface $em,
        #[Autowire(service: 'state_machine.mfc_request')] WorkflowInterface $sm
    ): Response {
        if ($req->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        // требуем, чтобы тип справки уже был выбран
        if (!$req->getApplicationType() || !$req->getDocumentNumber()) {
            return $this->redirectToRoute('mfc_step1', ['id' => $req->getId()]);
        }

        if ($sm->can($req, 'to_step2')) {
            $sm->apply($req, 'to_step2');
            $req->touch();
            $em->flush();
        }

        return $this->redirectToRoute('mfc_step2', ['id' => $req->getId()]);
    }

    #[Route('/workflow/{id}/step-2', name: 'mfc_step2', methods: ['GET', 'POST'])]
    public function step2(
        MfcRequest $req,
        Request $request,
        EntityManagerInterface $em,
        MfcFileStorage $storage,
        #[Autowire(service: 'state_machine.mfc_request')] WorkflowInterface $sm
    ): Response {
        if ($req->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        if ($req->getState() !== MfcRequest::STATE_STEP2) {
            return $this->redirectToRoute('mfc_step1', ['id' => $req->getId()]);
        }

        $form = $this->createForm(MfcStep2Type::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<int, UploadedFile> $files */
            $files = $form->get('files')->getData();

            foreach ($files as $file) {
                $em->beginTransaction();
                try {
                    $storage->storeUploadedFile($req, $file);
                    $em->commit();
                } catch (\Exception $e) {
                    $em->rollback();

                    throw $e;
                }
            }

            if ($sm->can($req, 'finish')) {
                $sm->apply($req, 'finish');
            }

            $req->touch();
            $em->flush();

            return $this->redirectToRoute('mfc_success');
        }

        return $this->render('mfc/step2.html.twig', [
            'req' => $req,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/workflow/{id}/back', name: 'mfc_back', methods: ['POST'])]
    public function back(
        MfcRequest $req,
        EntityManagerInterface $em,
        MfcFileStorage $storage,
        #[Autowire(service: 'state_machine.mfc_request')] WorkflowInterface $sm
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($req->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        // очищаем файлы только если позволено
        if ($sm->can($req, 'back_to_step1')) {
            // удалить физически + в бд
            foreach ($req->getFiles() as $file) {
                $storage->deletePhysicalFile($file);
                $req->removeFile($file);
                $em->remove($file);
            }

            $sm->apply($req, 'back_to_step1');
            $req->touch();
            $em->flush();
        }

        return $this->redirectToRoute('mfc_step1', ['id' => $req->getId()]);
    }

    #[Route('/success', name: 'mfc_success')]
    public function mfcSuccess(): Response
    {
        return $this->render('mfc/success.html.twig');
    }

    #[Route('/status', name: 'mfc_status')]
    public function mfcStatus(): Response
    {
        return $this->render('mfc/status.html.twig');
    }
}
