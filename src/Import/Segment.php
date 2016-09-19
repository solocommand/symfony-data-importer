<?php

namespace As3\SymfonyData\Import;

use Cygnus\ModlrBundle\Component\Utility;
use ForceUTF8\Encoding;
use DateTime;

abstract class Segment implements SegmentInterface
{
    use Enableable;

    /**
     * @var string
     */
    protected $characterSet;

    /**
     * @var ImporterInterface
     */
    protected $importer;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * {@inheritdoc}
     */
    final public function __construct(ImporterInterface $importer, SourceInterface $source)
    {
        $this->importer = $importer;
        $this->source = $source;
        if (null === $this->characterSet) {
            $this->characterSet = $importer->getCharacterSet();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return 200;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersister()
    {
        return $this->importer->getPersister();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function count();

    /**
     * {@inheritdoc}
     */
    abstract public function getKey();

    /**
     * {@inheritdoc}
     */
    abstract public function modify($limit = 200, $skip = 0);

    /**
     * {@inheritdoc}
     */
    abstract public function persist(array $items);

    /**
     * Performs character set conversion
     * @see     mb_convert_encoding()
     *
     * @todo    The $to option should be removed, since we are requiring UTF-8.
     * @param   string  $string     The text to convert
     * @param   string  $to         Override default character encoding (outgoing)
     * @param   array   $from       encoding conversion order
     *
     * @return  string  The converted text
     */
    protected function convertEncoding($string, $to = 'UTF-8', array $from = [])
    {
        if (empty($from)) {
            $from[] = $this->characterSet;
        }

        if (count($from) > 1 || $from[0] !== 'UTF-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $from);
        }
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        $string = Encoding::toUTF8($string);
        return $string;
    }

    /**
     * Performs specific body text formatting
     *
     * @param   string  The body text
     * @param   array   The legacy document
     *
     * @return  value
     */
    protected function formatBody($value, array $doc)
    {
        $value = $this->convertEncoding($value);

        // Replace html titles
        $value = mb_ereg_replace('<h[1-5].*?>(.*?)</h[1-5]>', "<h3>\\1</h3>", $value);

        // Strip jump tags
        $value = str_replace('[jump]', '<!-- [jump] -->', $value);

        // Fix spaces in <a> elements
        $value = mb_ereg_replace('<a\shref="\s+', '<a href="', $value, 'i');

        // Remove empty p tags
        $value = mb_ereg_replace('<p[^>]*>[\s|&nbsp;]*<\/p>', '', $value, 'ims');

        // Replace '--' with em dash
        $value = $this->replaceEmDash($value);

        // Handle oembeds
        $value = $this->transformOEmbeds($value);

        // Trim
        return trim($value);
    }

    /**
     *
     */
    private function transformOEmbeds($value)
    {
        $transformer = $this->importer->getTransformerManager()->getTransformerFor('oembed');
        return $transformer->transform($value);
    }

    /**
     *
     */
    private function replaceEmDash($value)
    {
        return preg_replace('/(?<!<!)(--)(?!([^<]+)?>)/', '—', $value);
    }

    /**
     * Performs generic text formatting
     *
     * @param   string
     *
     * @return  string
     */
    protected function formatText($string)
    {
        $string = $this->convertEncoding($string);
        return trim($string);
    }

    /**
     * Performs generic text formatting
     *
     * @param   string
     *
     * @return  string
     */
    protected function formatRedirect($alias)
    {
        return $this->formatText(ltrim($alias, '/'));
    }

    /**
     * strips non vanilla html tags from text, currently just wrapper for strip_tags but possibly add other logic (deafult leaveTags to simple ones like img,a,iframe, etc)
     *
     * @param   string
     *
     * @return  string
     */
    protected function stripTags($string)
    {
        return strip_tags($string, '<i><b><u><em><strong><del>');
    }

    /**
     * Returns a standard media path from supplied type and date.
     *
     * @param  string   $type The media type
     * @param  DateTime $date The date to use in the path
     * @return string
     */
    protected function getFilePath($type = 'image', DateTime $date = null)
    {
        $date = $date ?: new DateTime();
        return sprintf('files/base/%s/%s/%s/%s/%s', $this->getAccountKey(), $this->getGroupKey(), $type, $date->format('Y'), $date->format('m'));
    }

    /**
     * Returns the context's account key
     *
     * @return  string
     */
    final protected function getAccountKey()
    {
        return $this->importer->getPersister()->getConfigLoader()->getContext()->getGroup()->getAccount()->getKey();
    }

    /**
     * Returns the context's group key
     *
     * @return  string
     */
    final protected function getGroupKey()
    {
        return $this->importer->getPersister()->getConfigLoader()->getContext()->getGroup()->getKey();
    }

    /**
     * Gets a configuration value
     *
     * @param   string  $path           The dot-notated path to retrive eg import.channel_map
     * @param   mixed   $returnValue    The value that should be returned if the path was not found.
     *
     * @return  Cygnus\PlatformBundle\Models\ConfigurationValues
     */
    final protected function getConfigValue($path = null, $returnValue = null)
    {
        if (null !== $path) {
            return $this->importer->getPersister()->getConfigLoader()->getValues()->get($path, $returnValue);
        }
        return $this->importer->getPersister()->getConfigLoader()->getValues();
    }

    /**
     * @see PersisterInterface::getCollectionForModel()
     *
     * @param   string  $typeKey
     * @return  MongoCollection
     */
    final protected function getCollectionForModel($typeKey)
    {
        return $this->importer->getPersister()->getCollectionForModel($typeKey);
    }
}
