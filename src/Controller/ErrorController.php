<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ErrorController extends AbstractController
{
    public function show(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();

        return match ($statusCode) {
            Response::HTTP_NOT_FOUND => $this->render("bundles/TwigBundle/Exception/error404.html.twig", [
                'status_code' => $statusCode,
                'message' => $exception->getMessage(),
                'trace' => explode(PHP_EOL, $exception->getTraceAsString()),
            ]),
            Response::HTTP_INTERNAL_SERVER_ERROR => $this->render("bundles/TwigBundle/Exception/error500.html.twig", [
                'status_code' => $statusCode,
                'message' => $exception->getMessage(),
                'trace' => explode(PHP_EOL, $exception->getTraceAsString()),
            ]),
            default => $this->render("bundles/TwigBundle/Exception/error.html.twig", [
                'status_code' => $statusCode,
                'message' => $exception->getMessage(),
                'trace' => explode(PHP_EOL, $exception->getTraceAsString()),
            ]),
        };
    }
}
