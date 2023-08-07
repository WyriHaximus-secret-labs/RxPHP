<?php

declare(strict_types = 1);

namespace Rx\Observer;

/**
 * @template T
 */
class CallbackObserver extends AbstractObserver
{
    /** @var callable(T): void */
    private $onNext;

    /** @var callable(\Throwable): void */
    private $onError;

    /** @var callable(): void */
    private $onCompleted;

    /**
     * @param (callable(T): void)|null $onNext
     * @param (callable(\Throwable): void)|null $onError
     * @param (callable(): void)|null $onCompleted
     */
    public function __construct(?callable $onNext = null, ?callable $onError = null, ?callable $onCompleted = null)
    {
        $default = function (): void {
        };

        $this->onNext = $this->getOrDefault($onNext, $default);

        $this->onError = $this->getOrDefault($onError, function (\Throwable $e): void {
            throw $e;
        });

        $this->onCompleted = $this->getOrDefault($onCompleted, $default);
    }

    protected function completed()
    {
        // Cannot use $foo() here, see: https://bugs.php.net/bug.php?id=47160
        ($this->onCompleted)();
    }

    protected function error(\Throwable $error)
    {
        ($this->onError)($error);
    }

    /**
     * @param T $value
     * @return void
     */
    protected function next($value)
    {
        ($this->onNext)($value);
    }

    /**
     * @param callable|null $callback
     */
    private function getOrDefault(?callable $callback, callable $default): callable
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}
