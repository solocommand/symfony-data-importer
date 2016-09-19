<?php

namespace As3\SymfonyData\Import;

interface PersisterInterface
{
    /**
     * Handles requests to insert multiple documents.
     *
     * @param   string  $typeKey    A model type key
     * @param   array   $items      The items to insert
     *
     * @return  array   The items after insertion
     */
    public function batchInsert($typeKey, array $items);

    /**
     * Creates a reference for the requested field.
     *
     * @param   string  $typeKey    A model type key
     * @param   string  $field      The $typeKey model's referencing field key.
     * @param   mixed   $ident      The identifier value.
     * @param   string  $refTypeKey For discriminated references, the type key of the document being referenced.
     *
     * @return  mixed   For simple refs, the ID. For complex refs, the array to be saved to MongoDB.
     */
    public function createReference($typeKey, $field, $ident, $refTypeKey = null);

    /**
     * Returns the active context
     *
     * @return  string
     */
    public function getActiveContext();

    /**
     * Retrieves the raw mongo collection utilized by this persister.
     *
     * @param   string  $typeKey
     * @return  Doctrine\MongoDB\Collection
     */
    public function getCollectionForModel($typeKey);

    /**
     * Returns the configLoader
     *
     * @return  ConfigLoader
     */
    public function getConfigLoader();

    /**
     * Returns the database name for the specified typeKey.
     *
     * @param   string  $typeKey
     * @return  string
     */
    public function getDatabaseName($typeKey);

    /**
     * Returns the active data mode
     *
     * @return  string
     */
    public function getDataMode();

    /**
     * Returns the active environment key
     *
     * @return  string
     */
    public function getEnv();

    /**
     * Returns the typeKeys that this persister can support.
     *
     * @return  array
     */
    public function getModelTypes();

    /**
     * Returns the persister's storage layer
     *
     * @return  object
     */
    public function getStorageEngine();

    /**
     * Returns a loopable implementation of a subscriber pass.
     *
     * @param   string  $typeKey
     * @return  LooperInterface
     */
    public function getSubscriberLooper($typeKey);

    /**
     * Attempts to find a model for the specified path
     *
     * @param   string  $path   The path to look up
     * @param   string  $url    The original requested url
     * @return  mixed|null
     */
    public function findModelForPath($path, $url);

    /**
     * Handles requests to insert a single document.
     *
     * @param   string  $typeKey    A model type key (Platform\Taxonomy\Category, tag-family, etc.)
     * @param   array   $item       The item to insert
     *
     * @return  array   The item after insertion
     */
    public function insert($typeKey, array $item);

    /**
     * Performs database field typing for passed keyValues
     *
     * @param   string  $typeKey    A model type key
     * @param   array   $kvs        The model's keyValues
     *
     * @return  array   The sanitized/converted keyValues.
     */
    public function sanitizeModel($typeKey, array $kvs);

    /**
     * Determines if this persister is able to support the supplied type
     *
     * @param   string  $typeKey    A modlr type or as3modlr model key
     *
     * @return  bool    If the supplied type is supported.
     */
    public function supports($typeKey);
}
