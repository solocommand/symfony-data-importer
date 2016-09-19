<?php

namespace As3\SymfonyData\Import;

interface ImporterInterface
{
    /**
     * Returns the internal key for this importer
     *
     * @return  string
     */
    public function getKey();

    /**
     * Determines if this class is usable
     */
    public function isEnabled();

    /**
     * Returns the importer's persister
     *
     * @return  PersisterInterface
     */
    public function getPersister();

    /**
     * Returns the importer's character set
     *
     * @return  string
     */
    public function getCharacterSet();

    /**
     * If the importer supports the passed context key
     *
     * @return  bool
     */
    public function supports(Configuration $configuration);

    /**
     * Returns the list of segment keys and SCNs this importer supports
     *
     * @return  array
     */
    public function getSegments();

    /**
     * Hook that is called after schema is updated
     */
    public function postUpdateSchema();

    /**
     * Returns the number of legacy items available for the specified SCN
     *
     * @param   string  $segmentKey     The Modlr SubClassName
     * @return  int
     */
    public function count($segmentKey);

    /**
     * Returns modified legacy items to be persisted.
     *
     * @param   string  $segmentKey     The Modlr SubClassName
     * @param   int     $limit
     * @param   int     $skip
     * @return  array   The modified models
     */
    public function modify($segmentKey, $limit = 200, $skip = 0);

    /**
     * Persists legacy documents
     *
     * @param   string  $segmentKey     The SCN of the documents to store
     * @param   array   $docs           The documents to store
     * @return  array   The inserted documents
     */
    public function persist($segmentKey, array $docs);
}
