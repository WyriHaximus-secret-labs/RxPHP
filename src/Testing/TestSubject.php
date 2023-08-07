<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

/**
 * @template T
 * @template-extends Subject<T>
 * Class TestSubject
 * @package Rx\Testing
 */
class TestSubject extends Subject
{
    /** @var int */
    private $subscribeCount;

    /** @var ObserverInterface */
    private $observer;

    /** @var array<array-key, DisposableInterface> */
    private $disposeOnMap = [];

    public function __construct()
    {
        $this->subscribeCount = 0;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {

        $this->subscribeCount++;
        $this->observer = $observer;

        return new CallbackDisposable(function (): void {
            $this->dispose();
        });

    }

    /**
     * @param array-key $value
     * @param DisposableInterface $disposable
     * @return void
     */
    public function disposeOn($value, DisposableInterface $disposable)
    {
        $this->disposeOnMap[$value] = $disposable;
    }

    /**
     * @param T $value
     */
    public function onNext($value)
    {
        $this->observer->onNext($value);
        if (is_int($value) || is_string($value)) {
            if (isset($this->disposeOnMap[$value])) {
                $this->disposeOnMap[$value]->dispose();
            }
        }
    }

    /**
     * @param \Throwable $exception
     */
    public function onError(\Throwable $exception)
    {
        $this->observer->onError($exception);
    }

    public function onCompleted()
    {
        $this->observer->onCompleted();
    }

    /**
     * @return int
     */
    public function getSubscribeCount(): int
    {
        return $this->subscribeCount;
    }
}
