<?php

declare(strict_types = 1);

namespace Rx;

interface ObserverInterface
{
    /**
     * @return void
     */
    public function onCompleted();

    /**
     * @return void
     */
    public function onError(\Throwable $error);

    /**
     * @param mixed $value
     * @return void
     */
    public function onNext($value);
}
