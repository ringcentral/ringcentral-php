<?php

namespace RC\core;

class Observable
{

    protected $events = array();

    protected function ensureEvent($event)
    {
        if (empty($this->events[$event])) {
            $this->events[$event] = array();
        }
    }

    public function on($event, $callable)
    {

        $this->ensureEvent($event);

        $this->events[$event][] = $callable;

        return $this;

    }

    public function off($event, $callable = null)
    {
        $this->ensureEvent($event);

        if ($callable) {

            for ($i = count($this->events); $i >= 0; $i--) {

                if ($this->events[$i] == $callable) {
                    unset($this->events[$event][$i]);
                }

            }

        } else {
            unset($this->events[$event]);
        }

        return $this;

    }

    public function emit($event, $object)
    {

        $this->ensureEvent($event);

        foreach ($this->events[$event] as $callable) {
            call_user_func($callable, $object);
        }

        return $this;

    }

}
