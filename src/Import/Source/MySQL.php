<?php

namespace As3\SymfonyData\Import\Source;

use As3\SymfonyData\Import\Source;
use Doctrine\DBAL\Connection;

class MySQL extends Source
{
    /**
     * {@inheritdoc}
     */
    const SOURCE_KEY = 'mysql';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * DI Constructor
     *
     * @param Connection    $connection
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
        return $this->executeCount(
            $this->buildQuery($from, $criteria, [], [], true)
        );
    }

    /**
     * Executes a raw count using supplied SQL -- for complex queries
     *
     * @param   string  $sql
     * @return  int     The count of returned rows
     */
    public function countRaw($sql)
    {
        return $this->executeCount($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve($from, array $criteria = [], array $fields = [], array $sort = [], $limit = 200, $skip = 0)
    {
        return $this->executeQuery(
            $this->buildQuery($from, $criteria, $fields, $sort, false, $limit, $skip)
        );
    }

    /**
     * Executes a raw query using supplied SQL -- for complex queries
     *
     * @param   string  $sql
     * @return  array   The SQL results
     */
    public function retrieveRaw($sql)
    {
        return $this->executeQuery($sql);
    }

    /**
     * Generates a simple SQL query -- no JOIN/GROUP/etc support.
     *
     * @param   String  $table      The SQL table to query
     * @param   Array   $criteria   The criteria / where clause
     * @param   Array   $fields     The fields to return -- can support `AS` keyword
     * @param   Array   $sort       The ORDER BY clause
     * @param   Boolean $count      If the SQL query should be a count of results
     * @param   String  $where      The where clause. Values SHOULD be quoted (CRUD\getDoctrineConnection()->parse())
     * @param   Integer $limit      The limit for paging results. Non-zero value adds `LIMIT` clause to query
     * @param   Integer $skip       Number of results to skip. Ignored if $limit is 0
     *
     * @return  String              The SQL query
     */
    final protected function buildQuery($table, array $criteria = [], array $fields = [], array $sort = [], $count = false, $limit = 0, $skip = 0)
    {
        // Where/criteria
        $where = $this->parseCriteria($criteria);

        // Count
        if (true === $count) {
            return sprintf('SELECT COUNT(*) FROM `%s`%s', $table, $where);
        }

        // Fields
        if (empty($fields)) {
            $fields = ['*'];
        }

        // Sorting
        $sort = $this->parseSort($sort);

        // Limit/skip
        if (0 !== $limit) {
            $limiter = sprintf(' LIMIT %s, %s', $skip, $limit);
        } else {
            $limiter = '';
        }
        return sprintf('SELECT %s FROM %s%s%s%s', implode(', ', $fields), $table, $where, $sort, $limiter);
    }

    /**
     * Executes an SQL count
     *
     * @param   string  $sql
     * @return  int     The count of rows affected/returned by the query.
     */
    final protected function executeCount($sql)
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        return (Integer) $statement->fetchColumn();
    }

    /**
     * Executes an SQL query using the specified doctrine connection name
     *
     * @param   string  $sql
     * @return  array   The MySQL Results
     */
    final protected function executeQuery($sql)
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    /**
     * Parses query criteria into a query string
     *
     * @param   array   $criteria
     * @return  string
     */
    private function parseCriteria(array $criteria)
    {
        $out = '';
        if (!empty($criteria)) {
            $conditions = [];
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $values = array_map(function($value) { return $this->connection->quote($value); }, $value);
                    $conditions[] = sprintf('%s IN (%s)', $field, implode(', ', $values));
                } else {
                    $operator = '=';
                    $ops = ['<>', '<', '>', '<=', '>='];
                    foreach ($ops as $op) {
                        if (false !== strpos($value, $op)) {
                            $operator = $op;
                            $value = str_replace(sprintf('%s ', $op), '', $value);
                            break;
                        }
                    }
                    $conditions[] = sprintf('%s %s %s', $field, $operator, $this->connection->quote($value));
                }
            }
            $out = sprintf(' WHERE %s', implode(' AND ', $conditions));
        }
        return $out;
    }

    /**
     * Parses sort fields into an order by clause
     *
     * @param   array   $sort
     * @return  string
     */
    private function parseSort(array $sort)
    {
        $out = '';
        if (!empty($sort)) {
            $conditions = [];
            foreach ($sort as $field => $direction) {
                $direction = $direction ? 'DESC' : 'ASC';
                $conditions[] = sprintf('%s %s', $field, $direction);
            }
            $out = sprintf(' ORDER BY %s', implode(', ', $conditions));
        }
        return $out;
    }
}
