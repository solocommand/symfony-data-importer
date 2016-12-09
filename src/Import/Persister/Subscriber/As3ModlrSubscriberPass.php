<?php

namespace As3\SymfonyData\Import\Persister\Subscriber;

use As3\Modlr\Store\Store;
use As3\SymfonyData\Import\LoopableInterface;
use As3\SymfonyData\Import\PersisterInterface;

class As3ModlrSubscriberPass implements LoopableInterface
{
    /**
     * @var     string
     */
    protected $type;

    /**
     * @var     PersisterInterface
     */
    protected $persister;

    /**
     * DI constructor
     *
     * @param   string              $type       The modlr model type
     * @param   PersisterInterface  $persister  The modlr persister
     */
    public function __construct($type, PersisterInterface $persister)
    {
        $this->type = $type;
        $this->persister = $persister;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getCollection()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function modify($item)
    {
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve($limit = 200, $skip = 0)
    {
        $ids = [];
        $cursor = $this->getCollection()->find([], ['_id' => 1])->limit($limit)->skip($skip);
        foreach ($cursor as $item) {
            $ids[] = $item['_id'];
        }

        $criteria = ['_id' => ['$in' => $ids]];
        $items = $this->persister->getStorageEngine()->findQuery($this->type, $criteria);
        // return iterator_to_array($items);
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(array $items)
    {
        foreach ($items as $item) {
            $item->save();
        }
    }

    /**
     * Gets the underlying mongo collection
     *
     * @return  \MongoCollection
     */
    private function getCollection()
    {
        return $this->persister->getCollectionForModel($this->type);
    }
}
