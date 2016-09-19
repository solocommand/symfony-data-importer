<?php

namespace As3\SymfonyData\Import\Source;

use As3\SymfonyData\Import\Source;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\Query\Builder;

class Mongo extends Source
{
    /**
     * {@inheritdoc}
     */
    const SOURCE_KEY = 'mongo';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var String
     */
    private $database;

    /**
     * DI Constructor
     *
     * @param Connection    $connection
     * @param string        $database
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function count($from, array $criteria = [])
    {
        return $this
            ->selectCollection($from)
            ->count($criteria)
        ;
    }

    /**
     * Returns the mongo conneciton
     * @return  Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns the database value
     * @return  string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set database
     *
     * @param   string $database        The mongo database name
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve($from, array $criteria = [], array $fields = [], array $sort = [], $limit = 200, $skip = 0)
    {
        return $this
            ->selectCollection($from)
            ->find($criteria, $fields)
            ->sort($sort)
            ->limit($limit)
            ->skip($skip)
        ;
    }

    /**
     * Performs a batch update, required for preimport segments
     *
     * @param   string  $collection
     * @param   array   $criteria   The targeting parameters
     * @param   array   $update     The modifications to make to the targeted documents
     */
    final public function batchUpdate($collection, array $criteria, array $update)
    {
        return $this->selectCollection($collection)->update($criteria, $update, ['multiple' => true]);
    }

    /**
     * Gets duplicate Ids from a collection given criteria using aggregation
     *
     */
    final public function getDuplicateIds($collection, array $groupCriteria)
    {
        $dupeIds = [];
        $pipeline = [
            [
                '$match' => [
                    'status' => 1
                ]
            ],
            [
                '$group' => [
                    '_id' => $groupCriteria,
                    'dupes' => [
                        '$addToSet' => '$_id',
                    ],
                    'count' => [
                        '$sum' => 1,
                    ],
                ],
            ],
            [
                '$match' => [
                    'count' => [
                        '$gt' => 1
                    ]
                ]
            ]
        ];
        $groups = $this->selectCollection($collection)->aggregate($pipeline, ['allowDiskUse' => true]);
        $keep = 0;
        foreach ($groups AS $group) {
            $keep = true;
            foreach ($group['dupes'] as $id) {
                if ($keep === true) {
                    $keep = false;
                    continue;
                }
                $dupeIds[] = $id;
            }
        }
        return $dupeIds;
    }

    /**
     * Returns the mongo collection
     *
     * @param   string  $collection     The mongo collection name
     * @return  Doctrine\MongoDB\Collection
     */
    private function selectCollection($collection)
    {
        if (null === $this->database) {
            throw new InvalidArgumentException('Database was not specified, use `setDatabase`!');
        }
        return $this->connection->selectCollection($this->database, $collection);
    }
}
