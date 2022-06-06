# Health Check Bundle
This bundle can be used to easily write health checks and expose endpoints which can be used by e.g. Kubernetes to determine the health of the application.

This bundle consists of two core components that interweave with eachother, **checkers** and **checks**. 
- A (health) check is an component which contains logic to check if the system is behaving correctly.
- A (health) checker runs these checks when needed. For example when the endpoint `/healthz` is requested.   

## Usage
This package can be installed using composer:

`composer require browncat/healthcheck-bundle`

### Enable endpoints
This bundle comes with a set of endpoints which can be enabled to expose the following set of endpoints:

- `/health/liveness`  Returns 503 if one or multiple checks fail. Coupled to `LivenessChecker`
- `/health/readiness` Returns 503 if one or multiple checks fail. Coupled to `ReadinessChecker`
- `/health/startup` Returns 503 if one or multiple checks fail. Coupled to `StartupChecker`
- `/healthz` JSON result of all checks, returns 503 if one or multiple checks fail.

To enable them all add the following to your `routes.yaml`:

```yaml
health:
  resource: "@HealthCheckBundle/Resources/config/routes.xml"
```

## Bundle provided health checks

This bundle comes with some pre defined health checks. These checks are not enabled by default as to not get into the way of your workflow. They can be enabled and configured through Symfony's config component.

### Enable bundle provided health checks
To enable for example the check `doctrine.connection` create or modify the file `config/packages/healthcheck.yaml` and add the following:

```yaml
# config/packages/healthcheck.yaml

health_check:
    checks:
        doctrine.connection:
```

A list of package provided health checks can be found [here](#list-of-bundle-provided-health-checks).

The config above will enable the doctrine connection check for all available checkers. To use a subset of checkers add the following to the config:

```yaml
# config/packages/healthcheck.yaml

health_check:
    checks:
        doctrine.connection:
            checkers:
                - Browncat\HealthCheckBundle\Service\LivenessChecker
                - Browncat\HealthCheckBundle\Service\ReadinessChecker
```

A list of availble checkers can be found [here](#list-of-available-checkers)

### List of bundle provided health checks

| id                  | description                                            | since  |
|---------------------|--------------------------------------------------------|--------|
| doctrine.connection | Checks if all connections configured in doctrine work. | v0.1.0 |

## Creating your own health checks
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
    protected $name = 'example:connection';

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
A check should have a common name. This makes sure it can be located if a big list of checks is executed. A check can be named by populating the `proteced $name`.

```php
// src/Check/ExampleCheck.php
use Browncat\HealthCheckBundle\Check\HealthCheck;

final class ExampleCheck extends HealthCheck
{
    protected $name = 'example:connection';

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
By default *all* checkers (readiness, liveness and maybe some other configured ones) run the check you've written. If you want to narrow the check down to only run with a specific checker populate `public static $checkers` with the class references of the desired checker.

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

#### List of available checkers
- `Browncat\HealthCheckBundle\Service\LivenessChecker`
- `Browncat\HealthCheckBundle\Service\ReadinessChecker`
- `Browncat\HealthCheckBundle\Service\StartupChecker`
- `Browncat\HealthCheckBundle\Service\GlobalHealthChecker` (this one processes all registered checks no matter what)