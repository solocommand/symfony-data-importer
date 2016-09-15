<?php

namespace As3\SymfonyData\Import;

class Configuration
{
    const DATA_MODE_OVERWRITE = 'overwrite';
    const DATA_MODE_PROGRESSIVE  = 'progressive';
    const DATA_MODE_WIPE = 'wipe';

    const PROGRESSIVE_MODE_ID = 'id';
    const PROGRESSIVE_MODE_DATE = 'date';

    const ELASTIC_MODE_FULL = 'full';
    const ELASTIC_MODE_NONE = 'none';

    const SUBSCRIBER_MODE_FULL = 'full';
    const SUBSCRIBER_MODE_NONE = 'none';

    const SCHEMA_MODE_CREATE = 'create';
    const SCHEMA_MODE_UPDATE = 'update';
    const SCHEMA_MODE_NONE = 'none';

    /**
     * Current configuration state
     */
    private $modified;
    private $contextKey;
    private $filename;
    private $dataMode;
    private $progressiveMode;
    private $elasticMode;
    private $subscriberMode;
    private $schemaMode;

    /**
     * Available Importers
     *
     * @var ImporterInterface[]
     */
    private $importers = [];

    /**
     * Available Importer Segments
     *
     * @var SegmentInterface[]
     */
    private $segments = [];

    /**
     * @param   string                  $contextKey     The context key use for detecting importer support
     * @param   ImporterInterface[]     $importers      The importers that should be enabled for this configuration
     * @param   array                   $segments       Array containing enabled/disabled segments by key.
     */
    public function __construct($contextKey = 'default', $importers = [], $segments = [])
    {
        $this->contextKey = $contextKey;

        foreach ($importers as $importer) {
            $this->addImporter($importer);
        }

        foreach ($this->getSegments(true) as $segment) {
            $segment->disable();
        }

        foreach ($segments as $key => $bit) {
            if (true === $bit) {
                $segment = $this->getSegment($key);
                $segment->enable();
            }
        }
        $this->touch();
    }

    public function touch()
    {
        $this->modified = new \DateTime();
    }

    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Deletes the specified config model
     * @todo    Move to factory?
     *
     * @param   Configuration
     */
    public function delete(Configuration $config)
    {
        return $this->api->getResource('persistence', 'Import\Configuration')->delete($config);
    }

    public function shouldPopulate()
    {
        return static::ELASTIC_MODE_FULL === $this->getElasticMode();
    }

    public function shouldWipe()
    {
        return static::DATA_MODE_WIPE === $this->getDataMode();
    }

    public function getDataMode()
    {
        return $this->dataMode;
    }

    public function getSchemaMode()
    {
        return $this->schemaMode;
    }

    public function getProgressiveMode()
    {
        return $this->progressiveMode;
    }

    public function getElasticMode()
    {
        return $this->elasticMode;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getContextKey()
    {
        return $this->contextKey;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    public function setContextKey($contextKey)
    {
        $this->contextKey = $contextKey;
        return $this;
    }

    public function getSubscriberMode()
    {
        return $this->subscriberMode;
    }

    public function setSchemaMode($mode)
    {
        if (!in_array($mode, [static::SCHEMA_MODE_NONE, static::SCHEMA_MODE_UPDATE, static::SCHEMA_MODE_CREATE])) {
            throw new \InvalidArgumentException(sprintf('Passed schema mode "%s" is invalid!', $mode));
        }
        $this->schemaMode = $mode;
        return $this;
    }

    public function setElasticMode($mode)
    {
        if (!in_array($mode, [static::ELASTIC_MODE_NONE, static::ELASTIC_MODE_FULL])) {
            throw new \InvalidArgumentException(sprintf('Passed elastic mode "%s" is invalid!', $mode));
        }
        $this->elasticMode = $mode;
        return $this;
    }

    public function setSubscriberMode($mode)
    {
        if (!in_array($mode, [static::SUBSCRIBER_MODE_NONE, static::SUBSCRIBER_MODE_FULL])) {
            throw new \InvalidArgumentException(sprintf('Passed subscriber mode "%s" is invalid!', $mode));
        }
        $this->subscriberMode = $mode;
        return $this;
    }

    public function setProgressiveMode($mode)
    {
        if (!in_array($mode, [static::PROGRESSIVE_MODE_ID, static::PROGRESSIVE_MODE_DATE])) {
            throw new \InvalidArgumentException(sprintf('Passed progressive mode "%s" is invalid!', $mode));
        }
        $this->progressiveMode = $mode;
        return $this;
    }

    public function setDataMode($mode)
    {
        if (!in_array($mode, [static::DATA_MODE_WIPE, static::DATA_MODE_PROGRESSIVE, static::DATA_MODE_OVERWRITE])) {
            throw new \InvalidArgumentException(sprintf('Passed data mode "%s" is invalid!', $mode));
        }
        $this->dataMode = $mode;
        if (static::DATA_MODE_WIPE === $mode) {
            $this->setSchemaMode(static::SCHEMA_MODE_CREATE);
        }
        return $this;
    }

    public function addImporter(ImporterInterface $importer)
    {
        $this->importers[] = $importer;
        foreach ($importer->getSegments() as $segment) {
            $this->addSegment($segment);
        }
        $importer->setConfiguration($this);
    }

    public function hasSegment(SegmentInterface $segment)
    {
        foreach ($this->segments as $seg) {
            if ($seg->getKey() === $segment->getKey()) {
                return true;
            }
        }
        return false;
    }

    public function hasImporter(ImporterInterface $importer)
    {
        foreach ($this->importers as $imp) {
            if ($imp->getKey() === $importer->getKey()) {
                return true;
            }
        }
        return false;
    }

    public function addSegment(SegmentInterface $segment)
    {
        if (false === $this->hasSegment($segment)) {
            $this->segments[] = $segment;
        }
    }

    public function removeSegment(SegmentInterface $segment)
    {
        if (true === $this->hasSegment($segment)) {
            foreach ($this->segments as $key => $seg) {
                if ($seg->getKey() === $segment->getKey()) {
                    unset($this->segments[$key]);
                    $this->segments = array_values($this->segments);
                }
            }
        }
    }

    /**
     * Returns importers keyed by their internal key.
     *
     * @param   bool    $all    If all importers should be returned, regardless of status.
     * @return  array
     */
    public function getImporters($all = false)
    {
        $importers = [];
        foreach ($this->importers as $importer) {
            if ($importer->isEnabled() || true === $all) {
                $importers[$importer->getKey()] = $importer;
            }
        }
        return $importers;
    }

    /**
     * Retrieves an importer by key
     *
     * @param   string              $key
     * @return  ImporterInterface
     * @throws  InvalidArgumentInterface
     */
    public function getImporter($key)
    {
        foreach ($this->getImporters(true) as $k => $importer) {
            if ($key === $k) {
                return $importer;
            }
        }
        throw new \InvalidArgumentException(sprintf('Importer could not be found by key `%s`.', $key));
    }

    /**
     * Retrieves a segment by key
     *
     * @param   string              $key
     * @return  SegmentInterface
     * @throws  InvalidArgumentInterface
     */
    public function getSegment($key)
    {
        foreach ($this->getImporters() as $importer) {
            if ($importer->hasSegment($key)) {
                return $importer->getSegment($key);
            }
        }
        throw new \InvalidArgumentException(sprintf('Segment could not be found by key `%s`.', $key));
    }

    /**
     * Toggles an importer and its segments
     *
     * @param   string $key
     */
    public function toggleImporter($key)
    {
        $importer = $this->getImporter($key);
        $importer->toggle();
        foreach ($importer->getSegments() as $segment) {
            if ($importer->isEnabled()) {
                $this->addSegment($segment);
            } else {
                $this->removeSegment($segment);
            }
        }
    }

    /**
     * Toggles a segment
     *
     * @param   string $key
     */
    public function toggleSegment($key)
    {
        $segment = $this->getSegment($key);
        if (false === $segment->isEnabled()) {
            return $segment->enable();
        }
        return $segment->disable();
    }


    /**
     * Returns segments keyed by their internal key.
     *
     * @param   bool    $all    If all segments should be returned, regardless of status.
     * @return  array
     */
    public function getSegments($all = false)
    {
        if ($all) {
            return $this->segments;
        }
        $segments = [];
        foreach ($this->segments as $segment) {
            if ($segment->isEnabled()) {
                $segments[] = $segment;
            }
        }
        return $segments;
    }
}
