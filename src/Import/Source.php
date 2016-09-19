<?php

namespace As3\SymfonyData\Import;

/**
 *
 */
abstract class Source implements SourceInterface
{
    /**
     * The key of the import source
     */
    const SOURCE_KEY = 'source';

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return static::SOURCE_KEY;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function count($from, array $criteria = []);

    /**
     * {@inheritdoc}
     */
    abstract public function retrieve($from, array $criteria = [], array $fields = [], array $sort = [], $limit = 200, $skip = 0);
}
