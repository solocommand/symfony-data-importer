<?php

namespace Cygnus\ApplicationBundle\Import;

trait Enableable
{
    /**
     * @var Boolean
     */
    protected $enabled = true;

    /**
     * Determines if this class is enabled.
     *
     * @return  bool
     */
    public function isEnabled()
    {
        return true === $this->enabled;
    }

    /**
     * Enables the class
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disables the class
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Toggles the class' status
     */
    public function toggle()
    {
        $this->enabled = !$this->enabled;
    }
}
