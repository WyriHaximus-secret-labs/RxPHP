<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

/**
 * @template-implements OperatorInterface<mixed>
 */
final class ConcatMapOperator implements OperatorInterface
{
    /** @var callable(mixed, int, ObservableInterface<mixed>): ObservableInterface<mixed> */
    private $selector;

    /** @var (callable(mixed, mixed, int, int): mixed)|null */
    private $resultSelector;

    /**
     * @param callable(mixed, int, ObservableInterface<mixed>): ObservableInterface<mixed> $selector
     * @param (callable(mixed, mixed, int, int): mixed)|null $resultSelector
     */
    public function __construct(callable $selector, ?callable $resultSelector = null)
    {
        $this->selector       = $selector;
        $this->resultSelector = $resultSelector;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        assert($observable instanceof Observable);
        return $observable->mapWithIndex(function (int $index, $value) use ($observable, $observer) {
            try {
                /** @var ObservableInterface<mixed> $result */
                $result = ($this->selector)($value, $index, $observable);

                if ($this->resultSelector) {
                    /** @var Observable<mixed> $result */
                    return $result->mapWithIndex(function ($innerIndex, $innerValue) use ($value, $index) {
                        /** @phpstan-ignore-next-line */
                        return ($this->resultSelector)($value, $innerValue, $index, $innerIndex);
                    });
                }

                return $result;

            } catch (\Throwable $e) {
                $observer->onError($e);
            }
        })
            ->concatAll()
            ->subscribe($observer);
    }
}
