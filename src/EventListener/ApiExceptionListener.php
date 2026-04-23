<?php

namespace App\EventListener;

use App\Exception\AppException;
use App\Response\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as SecurityAccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class ApiExceptionListener
{
    public function __construct(
        private readonly bool            $debug,
        private readonly LoggerInterface $logger,
        private readonly Security        $security,
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();
        $request   = $event->getRequest();

        $response = match (true) {
            $exception instanceof AppException                      => $this->handleApp($exception, $request),
            $exception instanceof ValidationFailedException          => $this->handleValidation($exception, $request),
            $exception instanceof AuthenticationException            => ApiResponse::unauthorized('Unauthorized'),
            $exception instanceof SecurityAccessDeniedException      => ApiResponse::unauthorized('Unauthorized'),
            $exception instanceof HttpExceptionInterface             => $this->handleHttp($exception, $request),
            default                                                  => $this->handleUnknown($exception, $request),
        };

        $event->setResponse($response);
    }

    /** @return array<string, mixed> */
    private function buildContext(\Throwable $e, Request $request): array
    {
        return [
            'http' => [
                'method'     => $request->getMethod(),
                'url'        => $request->getUri(),
                'user_agent' => $request->headers->get('User-Agent'),
                'referer'    => $request->headers->get('Referer'),
                'request_id' => $request->headers->get('X-Request-Id'), // si vous en générez un
            ],

            'auth' => [
                'user_id' => $this->security->getUser()?->getUserIdentifier(),
            ],

            'exception' => [
                'class'   => $e::class,
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ],
        ];
    }

    private function handleApp(AppException $e, Request $request): ApiResponse
    {
        $context = $this->buildContext($e, $request);

        $context['exception']['error_code'] = $e->getErrorCode();
        $context['exception']['http_status'] = $e->getHttpStatusCode();
        $context['exception']['domain_context'] = $e->getContext(); // ex: ['user_id' => 42]

        match (true) {
            $e->getHttpStatusCode() >= 500 => $this->logger->error($e->getMessage(), $context),
            $e->getHttpStatusCode() >= 400 => $this->logger->notice($e->getMessage(), $context),
            default                        => $this->logger->info($e->getMessage(), $context),
        };

        $errors = ['code' => $e->getErrorCode()];
        if ($this->debug) {
            $errors['context']   = $e->getContext();
            $errors['exception'] = $e::class;
        }

        return ApiResponse::error($e->getMessage(), $errors, $e->getHttpStatusCode());
    }

    private function handleValidation(ValidationFailedException $e, Request $request): ApiResponse
    {
        $violations = [];
        foreach ($e->getViolations() as $violation) {
            $violations[] = [
                'field'   => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
                'value'   => (string) $violation->getInvalidValue(),
            ];
        }

        $this->logger->info('Validation failed', [
            ...$this->buildContext($e, $request),
            'violations' => $violations,
        ]);

        return ApiResponse::validationError($violations, 'Les données soumises sont invalides.');
    }

    private function handleHttp(HttpExceptionInterface $e, Request $request): ApiResponse
    {
        $status  = $e->getStatusCode();
        $message = $e->getMessage() ?: (Response::$statusTexts[$status] ?? 'Erreur');
        $context = $this->buildContext($e, $request);
        $context['exception']['http_status'] = $status;

        match (true) {
            $status === 404                => $this->logger->info('Route not found', $context),
            in_array($status, [401, 403]) => $this->logger->warning('Access denied', $context),
            $status >= 500                 => $this->logger->error('HTTP server error', $context),
            default                        => $this->logger->notice('HTTP client error', $context),
        };

        $response = match ($status) {
            404 => ApiResponse::notFound($message),
            401 => ApiResponse::unauthorized($message),
            403 => ApiResponse::forbidden($message),
            409 => ApiResponse::conflict($message),
            default => ApiResponse::error($message, statusCode: $status),
        };

        foreach ($e->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    private function handleUnknown(\Throwable $e, Request $request): ApiResponse
    {
        $this->logger->critical('Unhandled exception', [
            ...$this->buildContext($e, $request),
            'exception' => [
                'class'   => $e::class,
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ],
        ]);

        $response = ApiResponse::serverError('Une erreur interne s\'est produite.');

        if ($this->debug) {
            $data = json_decode($response->getContent(), true);
            $data['debug'] = [
                'exception' => $e::class,
                'message'   => $e->getMessage(),
                'trace'     => explode("\n", $e->getTraceAsString()),
            ];
            $response->setData($data);
        }

        return $response;
    }
}
