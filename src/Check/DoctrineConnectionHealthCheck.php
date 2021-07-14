<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Check;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

use function assert;
use function sprintf;

final class DoctrineConnectionHealthCheck extends HealthCheck
{
    /** @var string */
    protected static $name = 'doctrine:connection';

    public function __construct(?ManagerRegistry $doctrine)
    {
        $this->succeeded = true;

        if ($doctrine !== null) {
            foreach ($doctrine->getConnections() as $connection) {
                assert($connection instanceof Connection);
                try {
                    $connection->connect();

                    if (! $connection->isConnected()) {
                        $this->succeeded    = false;
                        $this->reasonPhrase = sprintf(
                            'Could not establish connection to db `%s`.',
                            $connection->getDatabase() ?? 'UNKNOWN'
                        );
                    }
                } catch (Throwable $e) {
                    $this->succeeded    = false;
                    $this->reasonPhrase = sprintf(
                        'Could not establish connection to db `%s`. %s.',
                        $connection->getDatabase() ?? 'UNKNOWN',
                        $e->getMessage()
                    );
                }
            }
        } else {
            $this->skipped      = true;
            $this->reasonPhrase = 'Doctrine package is not installed so this check is skipped.';
        }
    }
}
