# Health Checker Bundle
This bundle adds some health checks and two endpoints which can be used by e.g. Kubernetes to determine the health of the application.

## Usage
This package can be installed using composer:

`composer require browncat/healthcheck-bundle`

### Enable endpoints
This package comes with a set of endpoints which can be enabled to expose the following set of endpoints:

- `/health/liveness`  Returns 503 if one or multiple checks fail. Coupled to `LivenessChecker`
- `/health/readiness` Returns 503 if one or multiple checks fail. Coupled to `ReadinessChecker`
- `/health/startup` Returns 503 if one or multiple checks fail. Coupled to `StartupChecker`
- `/healthz` JSON result of all checks, returns 503 if one or multiple checks fail.

To enable them all add the following to your `routes.yaml`:

```yaml
health:
  resource: "@HealthCheckBundle/Resources/config/routes.yaml"
```

## Custom health checks

### Creating a health check
Health checks are defined in classes extending `Browncat\HealthCheckBundle\Check\HealthCheck`. For example, you may want to check the connection between the application and a remote system:

```php
// src/Check/ExampleCheck.php
<?php

namespace App\Check;

use Browncat\HealthCheckBundle\Check\HealthCheck;
use Browncat\HealthCheckBundle\Service\LivenessChecker;
use Browncat\HealthCheckBundle\Service\ReadinessChecker;
use Psr\Container\ContainerInterface;

final class ExampleCheck extends HealthCheck
{
    // Name of the health check
    protected static $name = 'example:connection';

    // List of checkers who should execute this check.
    public static $checkers = [ReadinessChecker::class, LivenessChecker::class];

    public function __construct(ContainerInterface $container)
    {
        if ($container->has('example')) {
            $exampleService = $container->get('example');
                
            if (!$exampleService->isConnected()) {
                $this->succeeded = false;
                $this->reasonPhrase = "Could not establish connection to example " . $connection->getName() . ".";
            } else {
                $this->succeeded = true;
            }
        } else {
            $this->skipped = true;
            $this->reasonPhrase = "example is not installed so this check is skipped.";
        }
    }
}
```

### Registering the health check
Health checks must be registered as services and tagged with the `health_check.check` tag. If you’re using the [default services.yaml configuration](https://symfony.com/doc/current/service_container.html#service-container-services-load-example), this is already done for you, thanks to [autoconfiguration](https://symfony.com/doc/current/service_container.html#services-autoconfigure).

### Naming a check
A check should have a common name. This makes sure it can be located if a big list of checks is executed. A check can be named by populating the `proteced string $name`.

```php
// src/Check/ExampleCheck.php
use Browncat\HealthCheckBundle\Check\HealthCheck;

final class ExampleCheck extends HealthCheck
{
    protected static string $name = 'example:connection';

    ...
}
```

### Passing or failing a check
A check can be failed or passed by passing a boolean value to the `$succeeded` propety.

```php
// src/Check/ExampleCheck.php
...
use Browncat\HealthCheckBundle\Check\HealthCheck;
...
final class ExampleCheck extends HealthCheck
{
    public function __construct(SomeService $someService)
    {
        if ($someService->isLoaded() {
            $this->succeeded = true 
        } else {
            $this->succeeded = false;
            // (optional) set a reason for the failed test
            $this->reasonPhrase = "SomeService Could not be loaded!";
        }

        
    }
}
```

### Skipping a check
To skip a check set the property `$skipped` to true.

```php
// src/Check/ExampleCheck.php
...
use Browncat\HealthCheckBundle\Check\HealthCheck;
use Psr\Container\ContainerInterface;
...
final class ExampleCheck extends HealthCheck
{
    public function __construct(ContainerInterface $container)
    {
        if (!$container->has('someService')) {
            $this->skipped = true;
            $this->reasonPhrase = 'SomeService is skipped because it does not exist.';
        }
        ...
    }
}
```

### (Optional) set checkers
By default *all* checkers (readiness, liveness and maybe some other configured ones) run the check you've written. If you want to narrow the check down to only run with a specific checker populate `public static array $checkers` with the class references of the desired checker.

```php
// src/Check/ExampleCheck.php
use Browncat\HealthCheckBundle\Check\HealthCheck;
use Browncat\HealthCheckBundle\Service\ReadinessChecker;

final class ExampleCheck extends HealthCheck
{
    public static $checkers = [ReadinessChecker::class]; 

    ...
}
```

#### Available checkers
- `Browncat\HealthCheckBundle\Service\LivenessChecker`
- `Browncat\HealthCheckBundle\Service\ReadinessChecker`
- `Browncat\HealthCheckBundle\Service\StartupChecker`
- `Browncat\HealthCheckBundle\Service\GlobalHealthChecker` (this one processes all registered checks no matter what)