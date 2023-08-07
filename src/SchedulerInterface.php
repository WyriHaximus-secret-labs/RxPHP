<?php

declare(strict_types = 1);

namespace Rx;

interface SchedulerInterface
{
    /**
     * @param callable(): mixed $action
     * @param int $delay
     *
     * @return DisposableInterface
     */
    public function schedule(callable $action, $delay = 0): DisposableInterface;

    /**
     * @param callable(callable(): void): mixed $action
     *
     * @return DisposableInterface
     */
    public function scheduleRecursive(callable $action): DisposableInterface;

    /**
     * @param callable(): mixed $action
     * @param int $delay
     * @param int $period
     *
     * @return DisposableInterface
     */
    public function schedulePeriodic(callable $action, $delay, $period): DisposableInterface;

    /**
     * Gets the current representation of now for the scheduler
     *
     * @return int
     */
    public function now(): int;
}
