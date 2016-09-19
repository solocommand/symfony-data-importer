<?php

namespace As3\SymfonyData\Import;

interface SourceInterface
{
    /**
     * Returns the Source key
     */
    public function getKey();

    /**
     * @param   string  $from       The table/collection/etc to use
     * @param   mixed   $criteria   Query criteria
     */
    public function count($from, array $criteria = []);

    /**
     * @param   string  $from       The table/collection/etc to use
     * @param   array   $criteria   Query criteria
     * @param   array   $fields     Fields to return
     * @param   array   $sort       Sort
     * @param   int     $imit       Number of items to return
     * @param   int     $skip       Number of items to skip
     */
    public function retrieve($from, array $criteria = [], array $fields = [], array $sort = [], $limit = 200, $skip = 0);
}
