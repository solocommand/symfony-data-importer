<?php

namespace As3\SymfonyData\Import;

/**
 *
 */
abstract class Importer implements ImporterInterface
{
    use Enableable;

    /**
     * {@inheritdoc}
     */
    const IMPORTER_KEY = 'importer';

    /**
     * @var PersisterInterface
     */
    protected $persister;

    /**
     * @var string
     */
    protected $characterSet = 'UTF-8';

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * List of supported context keys: account.group
     *
     * @var     array
     */
    protected $supportedContexts = [];

    /**
     * List of registered segments to be processed.
     *
     * @var     SegmentInterface[]
     */
    protected $segments = [];

    /**
     * @var     TransformerManager
     */
    protected $transformerManager;

    /**
     * DI
     *
     * @param   PersisterInterface  $persister
     * @param   SourceInterface     $source
     */
    public function __construct(PersisterInterface $persister, SourceInterface $source)
    {
        $this->persister = $persister;
        $this->source = $source;
    }

    final public function setConfiguration(Configuration $configuration)
    {
        return $this->persister->setConfiguration($configuration);
    }

    /**
     * {@inheritdoc}
     */
    final public function supports(Configuration $configuration)
    {
        return in_array($configuration->getContextKey(), $this->supportedContexts);
    }

    /**
     * {@inheritdoc}
     */
    final public function getCharacterSet()
    {
        return $this->characterSet;
    }

    /**
     * {@inheritdoc}
     */
    final public function getSegments()
    {
        return $this->segments;
    }

    /**
     * {@inheritdoc}
     */
    final public function hasSegment($key)
    {
        foreach ($this->getSegments() as $segment) {
            if ($segment->getKey() === $key) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    final public function getSegment($key)
    {
        foreach ($this->getSegments() as $segment) {
            if ($key === $segment->getKey()) {
                return $segment;
            }
        }
        throw new Exception\ImporterException(sprintf('Could not retrieve segment "%s"!', $key));
    }

    /**
     * {@inheritdoc}
     */
    final public function getPersister()
    {
        return $this->persister;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getKey();

    /**
     * {@inheritdoc}
     */
    public function postUpdateSchema()
    {
    }

    /**
     * {@inheritdoc}
     */
    final public function count($segmentKey)
    {
        throw new \BadMethodCallException('deprecated');
        return $this->getSegment($segmentKey)->count();
    }

    /**
     * {@inheritdoc}
     */
    final public function modify($segmentKey, $limit = 200, $skip = 0)
    {
        throw new \BadMethodCallException('deprecated');
        return $this->getSegment($segmentKey)->modify($limit, $skip);
    }

    /**
     * {@inheritdoc}
     */
    final public function persist($segmentKey, array $docs)
    {
        throw new \BadMethodCallException('deprecated');
        return $this->getSegment($segmentKey)->persist($docs);
    }
}
