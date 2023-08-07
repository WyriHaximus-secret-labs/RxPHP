<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

/**
 * @template-implements OperatorInterface<mixed>
 */
final class ReduceOperator implements OperatorInterface
{
    /** @var  callable */
    protected $accumulator;

    /**
     * @var mixed
     */
    protected $seed;

    /**
     * @var bool
     */
    protected $hasSeed;

    /**
     * @param callable $accumulator
     * @param mixed $seed
     */
    public function __construct(callable $accumulator, $seed)
    {
        $this->accumulator = $accumulator;
        $this->seed        = $seed;
        $this->hasSeed     = null !== $seed;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $hasAccumulation = false;
        $accumulation    = null;
        $hasValue        = false;
        $cbObserver      = new CallbackObserver(
            function ($x) use ($observer, &$hasAccumulation, &$accumulation, &$hasValue): void {

                $hasValue = true;

                try {
                    if ($hasAccumulation) {
                        $accumulation = ($this->accumulator)($accumulation, $x);
                    } else {
                        $accumulation    = $this->hasSeed ? ($this->accumulator)($this->seed, $x) : $x;
                        $hasAccumulation = true;
                    }
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            function ($e) use ($observer): void {
                $observer->onError($e);
            },
            function () use ($observer, &$accumulation, &$hasValue): void {
                /** @phpstan-ignore-next-line */
                if ($hasValue) {
                    $observer->onNext($accumulation);
                } else {
                    $this->hasSeed && $observer->onNext($this->seed);
                }

                /** @phpstan-ignore-next-line */
                if (!$hasValue && !$this->hasSeed) {
                    $observer->onError(new \Exception('Missing Seed and or Value'));
                }

                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver);

    }
}
