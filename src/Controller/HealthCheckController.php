<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Controller;

use Browncat\HealthCheckBundle\Check\HealthCheck;
use Browncat\HealthCheckBundle\Service\GlobalHealthChecker;
use Browncat\HealthCheckBundle\Service\LivenessChecker;
use Browncat\HealthCheckBundle\Service\ReadinessChecker;
use Browncat\HealthCheckBundle\Service\StartupChecker;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo Make this dynamic based on a list of checkers.
 */
class HealthCheckController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function checkLiveness(LivenessChecker $livenessChecker): Response
    {
        $success = true;

        foreach ($livenessChecker->getChecks() as $check) {
            if ($check->isSkipped()) {
                $this->logger->info('Liveness check skipped.', $this->getLoggerContextOfCheck($check));
            } elseif (! $check->isSucceeded()) {
                $success = false;

                $this->logger->critical('Liveness check failed.', $this->getLoggerContextOfCheck($check));
            }
        }

        return new Response('', $success ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function checkReadiness(ReadinessChecker $readinessChecker): Response
    {
        $success = true;

        foreach ($readinessChecker->getChecks() as $check) {
            if ($check->isSkipped()) {
                $this->logger->info('Readiness check skipped.', $this->getLoggerContextOfCheck($check));
            } elseif (! $check->isSucceeded()) {
                $success = false;

                $this->logger->warning('Readiness check failed.', $this->getLoggerContextOfCheck($check));
            }
        }

        return new Response('', $success ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function checkStartup(StartupChecker $startupChecker): Response
    {
        $success = true;

        foreach ($startupChecker->getChecks() as $check) {
            if ($check->isSkipped()) {
                $this->logger->info('Startup check skipped.', $this->getLoggerContextOfCheck($check));
            } elseif (! $check->isSucceeded()) {
                $success = false;

                $this->logger->warning('Startup check failed.', $this->getLoggerContextOfCheck($check));
            }
        }

        return new Response('', $success ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function healthOverview(GlobalHealthChecker $healthChecker): JsonResponse
    {
        $response = [
            'totalChecks' => 0,
            'skippedChecks' => 0,
            'succeededChecks' => 0,
            'failedChecks' => 0,
            'checks' => [],
        ];

        $success = true;

        foreach ($healthChecker->getChecks() as $check) {
            $response['totalChecks']++;

            $response['checks'][] = [
                'name' => $check->getName(),
                'skipped' => $check->isSkipped(),
                'succeeded' => $check->isSkipped() ? null : $check->isSucceeded(),
                'reasonPhrase' => $check->getReasonPhrase(),
            ];

            if ($check->isSkipped()) {
                $response['skippedChecks']++;
            } elseif (! $check->isSucceeded()) {
                $success = false;
                $response['failedChecks']++;
            } else {
                $response['succeededChecks']++;
            }
        }

        return new JsonResponse($response, $success ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * @return string[]
     * @psalm-return array{name: string, class: string, reason: string|null}
     */
    private function getLoggerContextOfCheck(HealthCheck $check): array
    {
        return [
            'name' => $check->getName(),
            'class' => $check::class,
            'reason' => $check->getReasonPhrase(),
        ];
    }
}
