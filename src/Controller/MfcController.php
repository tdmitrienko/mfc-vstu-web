<?php

namespace App\Controller;

use App\Entity\MfcRequest;
use App\Entity\User;
use App\Form\MfcStep1Type;
use App\Form\MfcStep2Type;
use App\Form\MfcStep3Type;
use App\Repository\MfcRequestRepository;
use App\Service\MfcFileStorage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[IsGranted('ROLE_USER')]
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
        $this->assertOwner($req);

        if ($req->getState() !== MfcRequest::STATE_STEP1) {
            return $this->redirectToCurrentStep($req);
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
        $this->assertOwner($req);

        $appType = $req->getApplicationType();
        if ($appType === null) {
            return $this->redirectToRoute('mfc_step1', ['id' => $req->getId()]);
        }

        $state = $req->getState();

        // step1 → step2 (если нужен документ)
        if ($state === MfcRequest::STATE_STEP1 && $appType->isDocumentRequired()) {
            return $this->applyAndRedirect($req, $em, $sm, 'to_step2', 'mfc_step2');
        }

        // step2 → проверяем что документ заполнен
        if ($state === MfcRequest::STATE_STEP2 && !$req->getDocumentNumber()) {
            return $this->redirectToRoute('mfc_step2', ['id' => $req->getId()]);
        }

        // step1/step2 → step3 (если нужны файлы)
        if (in_array($state, [MfcRequest::STATE_STEP1, MfcRequest::STATE_STEP2], true) && $appType->isFilesRequired()) {
            return $this->applyAndRedirect($req, $em, $sm, 'to_step3', 'mfc_step3');
        }

        // step1/step2 → finish (ничего больше не нужно)
        if (in_array($state, [MfcRequest::STATE_STEP1, MfcRequest::STATE_STEP2], true)) {
            $this->applyTransition($req, $em, $sm, 'finish');
            return $this->render('mfc/success.html.twig');
        }

        return $this->redirectToCurrentStep($req);
    }

    #[Route('/workflow/{id}/step-2', name: 'mfc_step2', methods: ['GET', 'POST'])]
    public function step2(
        MfcRequest $req,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $this->assertOwner($req);

        if ($req->getState() !== MfcRequest::STATE_STEP2) {
            return $this->redirectToCurrentStep($req);
        }

        $form = $this->createForm(MfcStep2Type::class, $req, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $req->touch();
            $em->flush();

            return $this->redirectToRoute('mfc_next', ['id' => $req->getId()]);
        }

        return $this->render('mfc/step2.html.twig', [
            'req' => $req,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/workflow/{id}/step-3', name: 'mfc_step3', methods: ['GET', 'POST'])]
    public function step3(
        MfcRequest $req,
        Request $request,
        EntityManagerInterface $em,
        MfcFileStorage $storage,
        #[Autowire(service: 'state_machine.mfc_request')] WorkflowInterface $sm
    ): Response {
        $this->assertOwner($req);

        if ($req->getState() !== MfcRequest::STATE_STEP3) {
            return $this->redirectToCurrentStep($req);
        }

        $form = $this->createForm(MfcStep3Type::class);
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

            $this->applyTransition($req, $em, $sm, 'finish');

            return $this->render('mfc/success.html.twig');
        }

        return $this->render('mfc/step3.html.twig', [
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
        $this->assertOwner($req);

        $state = $req->getState();
        $appType = $req->getApplicationType();

        if ($state === MfcRequest::STATE_STEP3) {
            foreach ($req->getFiles() as $file) {
                $storage->deletePhysicalFile($file);
                $req->removeFile($file);
                $em->remove($file);
            }

            if ($appType?->isDocumentRequired() && $sm->can($req, 'back_to_step2')) {
                return $this->applyAndRedirect($req, $em, $sm, 'back_to_step2', 'mfc_step2');
            }

            return $this->applyAndRedirect($req, $em, $sm, 'back_to_step1', 'mfc_step1');
        }

        if ($state === MfcRequest::STATE_STEP2) {
            $req->setDocumentNumber(null);
            return $this->applyAndRedirect($req, $em, $sm, 'back_to_step1', 'mfc_step1');
        }

        return $this->redirectToRoute('mfc_step1', ['id' => $req->getId()]);
    }

    #[Route('/requests', name: 'mfc_requests')]
    public function requests(
        MfcRequestRepository $mfcRequestRepository,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $requests = $mfcRequestRepository->findRequestsByUser($user);

        return $this->render('mfc/requests.html.twig', [
            'requests' => $requests,
        ]);
    }

    #[Route('/request/{id}/remove-template', name: 'mfc_request_remove_template')]
    public function removeTemplateRequest(
        MfcRequestRepository $mfcRequestRepository,
        MfcFileStorage $storage,
        LoggerInterface $logger,
        int $id,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $request = $mfcRequestRepository->findTemplateRequestByIdAndUser($id, $user);
        if ($request === null) {
            throw $this->createNotFoundException('Request not found');
        }

        $relativePaths = [];
        foreach ($request->getFiles() as $file) {
            $relativePaths[] = $file->getPath();
        }

        $mfcRequestRepository->removeRequest($request);

        foreach ($relativePaths as $relativePath) {
            try {
                $storage->deletePhysicalFileByRelativePath($relativePath);
            } catch (\Exception $e) {
                $logger->error('error-while-deleting-file-in-mfc-controller-remove-template-request-action', [
                    'exception' => $e,
                    'relativePath' => $relativePath,
                ]);
            }
        }

        return $this->redirectToRoute('mfc_requests');
    }

    private function assertOwner(MfcRequest $req): void
    {
        if ($req->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }
    }

    private function applyTransition(
        MfcRequest $req,
        EntityManagerInterface $em,
        WorkflowInterface $sm,
        string $transition,
    ): void {
        $sm->apply($req, $transition);
        $req->touch();
        $em->flush();
    }

    private function applyAndRedirect(
        MfcRequest $req,
        EntityManagerInterface $em,
        WorkflowInterface $sm,
        string $transition,
        string $route,
    ): Response {
        $this->applyTransition($req, $em, $sm, $transition);
        return $this->redirectToRoute($route, ['id' => $req->getId()]);
    }

    private function redirectToCurrentStep(MfcRequest $req): Response
    {
        return match ($req->getState()) {
            MfcRequest::STATE_STEP2 => $this->redirectToRoute('mfc_step2', ['id' => $req->getId()]),
            MfcRequest::STATE_STEP3 => $this->redirectToRoute('mfc_step3', ['id' => $req->getId()]),
            default => $this->redirectToRoute('mfc_step1', ['id' => $req->getId()]),
        };
    }
}
