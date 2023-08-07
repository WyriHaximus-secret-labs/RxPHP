<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\AsyncSchedulerInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

/**
 * @template-implements OperatorInterface<mixed>
 */
final class ThrottleOperator implements OperatorInterface
{
    /**
     * @var int
     */
    private $nextSend = 0;

    /**
     * @var int
     */
    private $throttleTime = 0;

    /**
     * @var bool
     */
    private $completed = false;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    public function __construct(int $debounceTime, SchedulerInterface $scheduler)
    {
        $this->throttleTime = $debounceTime;
        $this->scheduler    = $scheduler;
    }

    /**
     * @template T
     * @param ObservableInterface<T> $observable
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $innerDisp = new SerialDisposable();

        $disp = $observable->subscribe(new CallbackObserver(
            function ($x) use ($innerDisp, $observer): void {
                $now = $this->scheduler->now();
                if ($this->nextSend <= $now) {
                    $innerDisp->setDisposable(new EmptyDisposable());
                    $observer->onNext($x);
                    $this->nextSend = $now + $this->throttleTime - 1;
                    return;
                }

                /**
                 * @var Observable<T> $observable
                 */
                $observable = Observable::of($x, $this->scheduler);
                /** @var AsyncSchedulerInterface $scheduler */
                $scheduler = $this->scheduler;
                $newDisp = $observable->delay($this->nextSend - $now, $scheduler)
                    ->subscribe(new CallbackObserver(
                        function ($x) use ($observer): void {
                            $observer->onNext($x);
                            $this->nextSend = $this->scheduler->now() + $this->throttleTime - 1;
                            if ($this->completed) {
                                $observer->onCompleted();
                            }
                        },
                        [$observer, 'onError']
                    ));

                $innerDisp->setDisposable($newDisp);
            },
            function (\Throwable $e) use ($observer, $innerDisp): void {
                $innerDisp->dispose();
                $observer->onError($e);
            },
            function () use ($observer): void {
                $this->completed = true;
                if ($this->nextSend === 0) {
                    $observer->onCompleted();
                }
            }
        ));

        return new CompositeDisposable([$disp, $innerDisp]);
    }
}
