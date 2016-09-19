<?php

namespace As3\SymfonyData\Import;

interface SegmentInterface
{
    /**
     * Pseudo-DI for the segment
     *
     * @param   ImporterInterface   $importer
     * @param   SourceInterface     $source
     */
    public function __construct(ImporterInterface $importer, SourceInterface $source);

    /**
     * Returns the number of legacy items available for the specified SCN
     *
     * @return  int
     */
    public function count();

    /**
     * Returns the segment key
     *
     * @return  string
     */
    public function getKey();

    /**
     * Returns the segment's persister
     *
     * @return  PersisterInterface
     */
    public function getPersister();

    /**
     * Returns modified legacy items to be persisted.
     *
     * @param   int     $limit
     * @param   int     $skip
     *
     * @return  array   The modified models
     */
    public function modify($limit = 200, $skip = 0);

    /**
     * Persists legacy documents
     *
     * @param   array   $items           The documents to store
     *
     * @return  array   The inserted documents
     */
    public function persist(array $items);

    /**
     * Returns the segment limit
     *
     * @return  int
     */
    public function getLimit();
}
