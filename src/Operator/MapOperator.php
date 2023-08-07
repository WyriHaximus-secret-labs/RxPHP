<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

/**
 * @template-implements OperatorInterface<mixed>
 */
final class MapOperator implements OperatorInterface
{
    /**
     * @var callable
     */
    private $selector;

    public function __construct(callable $selector)
    {
        $this->selector = $selector;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposed   = false;
        $disposable = new CompositeDisposable();

        $selectObserver = new CallbackObserver(
            function ($nextValue) use ($observer, &$disposed): void {

                $value = null;
                try {
                    $value = ($this->selector)($nextValue);
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
                /** @phpstan-ignore-next-line */
                if (!$disposed) {
                    $observer->onNext($value);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        $disposable->add(new CallbackDisposable(function () use (&$disposed): void {
            $disposed = true;
        }));

        $disposable->add($observable->subscribe($selectObserver));

        return $disposable;
    }
}
