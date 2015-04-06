<?php

namespace RC\core;

class Observable
{

    protected $events = array();

    protected function ensureEvent($event)
    {
        if (!$this->hasListeners($event)) {
            $this->events[$event] = array();
        }
    }

    /**
     * @param string $event
     * @return integer
     */
    public function hasListeners($event)
    {

        return empty($this->events[$event]) ? 0 : count($this->events[$event]);

    }

    /**
     * @param string   $event
     * @param callable $callable
     * @return $this
     */
    public function on($event, $callable)
    {

        $this->ensureEvent($event);

        $this->events[$event][] = $callable;

        return $this;

    }

    /**
     * @param string   $event
     * @param callable $callable
     * @return $this
     */
    public function off($event, $callable = null)
    {

        if ($callable) {

            for ($i = count($this->events[$event]) - 1; $i >= 0; $i--) {

                if ($this->events[$event][$i] == $callable) {
                    unset($this->events[$event][$i]);
                }

            }

        } else {
            unset($this->events[$event]);
        }

        return $this;

    }

    public function offAll()
    {
        $this->events = array();
    }

    /**
     * @param string $event
     * @param mixed  $object
     * @return $this
     */
    public function emit($event, $object = null)
    {

        $this->ensureEvent($event);

        $result = null;

        foreach ($this->events[$event] as $callable) {
            $result = call_user_func($callable, $object);
            if (false === $result) {
                break;
            }
        }

        return $result;

    }

}
