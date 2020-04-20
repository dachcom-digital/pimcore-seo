<?php

namespace SeoBundle\Model;

class SeoMetaData implements SeoMetaDataInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $originalUrl;

    /**
     * @var string
     */
    private $metaDescription;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $extraProperties = [];

    /**
     * @var array
     */
    private $extraNames = [];

    /**
     * @var array
     */
    private $extraHttp = [];

    /**
     * @var array
     */
    private $schema = [];

    /**
     * @deprecated
     *
     * @var array
     */
    private $raw = [];

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalUrl($originalUrl)
    {
        $this->originalUrl = $originalUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalUrl()
    {
        return $this->originalUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtraProperties($extraProperties)
    {
        $this->extraProperties = $this->toArray($extraProperties);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraProperties()
    {
        return $this->extraProperties;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtraProperty($key, $value)
    {
        $this->extraProperties[$key] = (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function removeExtraProperty($key)
    {
        if (array_key_exists($key, $this->extraProperties)) {
            unset($this->extraProperties[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setExtraNames($extraNames)
    {
        $this->extraNames = $this->toArray($extraNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraNames()
    {
        return $this->extraNames;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtraName($key, $value)
    {
        $this->extraNames[$key] = (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function removeExtraName($key)
    {
        if (array_key_exists($key, $this->extraNames)) {
            unset($this->extraNames[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setExtraHttp($extraHttp)
    {
        $this->extraHttp = $this->toArray($extraHttp);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraHttp()
    {
        return $this->extraHttp;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtraHttp($key, $value)
    {
        $this->extraHttp[$key] = (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function removeExtraHttp($key)
    {
        if (array_key_exists($key, $this->extraHttp)) {
            unset($this->extraHttp[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function addSchema(array $schemaJsonLd)
    {
        $this->schema[] = $schemaJsonLd;
    }

    /**
     * {@inheritdoc}
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * {@inheritdoc}
     */
    public function addRaw(string $value)
    {
        $this->raw[] = $value;
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    private function toArray($data)
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof \Traversable) {
            return iterator_to_array($data);
        }

        throw new \InvalidArgumentException(
            sprintf('Expected array or Traversable, got "%s"', is_object($data) ? get_class($data) : gettype($data))
        );
    }
}
