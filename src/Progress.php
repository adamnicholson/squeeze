<?php

namespace Adamnicholson\Squeeze;

class Progress
{
    private $listeners = [];

    /**
     * Notify the listeners that we are X percentance complete
     *
     * @param float $percentageComplete
     */
    public function notify(float $percentageComplete)
    {
        foreach ($this->listeners as $listener) {
            $listener($percentageComplete);
        }
    }

    public function listen(callable $listener)
    {
        $this->listeners[] = $listener;

        return $this;
    }
}
