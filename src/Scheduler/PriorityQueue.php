<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use ReturnTypeWillChange;
use SplPriorityQueue;

class PriorityQueue
{
    /**
     * @var InternalPriorityQueue
     */
    private $queue;

    public function __construct()
    {
        $this->queue = new InternalPriorityQueue;
    }

    /**
     * @return void
     */
    public function enqueue(ScheduledItem $item)
    {
        $this->queue->insert($item, $item);
    }

    /**
     * @return bool
     */
    public function remove(ScheduledItem $item)
    {
        if ($this->count() === 0) {
            return false;
        }

        if ($this->peek() === $item) {
            $this->dequeue();
            return true;
        }

        /**
         * @var InternalPriorityQueue $newQueue
         */
        $newQueue = new InternalPriorityQueue();
        $removed  = false;

        foreach ($this->queue as $element) {
            if ($item === $element) {
                $removed = true;
                continue;
            }

            $newQueue->insert($element, $element);
        }

        $this->queue = $newQueue;

        return $removed;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * @return ScheduledItem
     */
    public function peek()
    {
        $return = $this->queue->top();
        assert($return instanceof ScheduledItem);
        return $return;
    }

    /**
     * @return ScheduledItem
     */
    public function dequeue()
    {
        $return = $this->queue->extract();
        assert($return instanceof ScheduledItem);
        return $return;
    }
}
