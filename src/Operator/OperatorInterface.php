<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

/**
 * @template T
 */
interface OperatorInterface
{
    /**
     * @param ObservableInterface<T> $observable
     * @param ObserverInterface $observer
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface;
}
