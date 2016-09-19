<?php

namespace As3\SymfonyData\Import;

/**
 * This interface ensures the class can be used with the Console's loop command.
 * @see     Cygnus\ApplicationBundle\Console\Traits\IO::loop()
 * @author  Josh Worden <jworden@southcomm.com>
 */
interface LoopableInterface
{
    /**
     * The counter method.
     *
     * @return  int
     */
    public function count();

    /**
     * The retrieve method.
     *
     * @return  int
     */
    public function retrieve($limit = 200, $skip = 0);

    /**
     * The modifier method
     *
     * @param   mixed   $item   The item to modify
     * @return  mixed   $item   The modified item
     */
    public function modify($item);

    /**
     * The persister method
     *
     * @param   array   $items  The items to persist
     */
    public function persist(array $items);
}
