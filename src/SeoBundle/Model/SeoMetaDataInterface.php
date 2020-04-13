<?php

namespace SeoBundle\Model;

interface SeoMetaDataInterface
{
    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param string $originalUrl
     */
    public function setOriginalUrl($originalUrl);

    /**
     * @return string
     */
    public function getOriginalUrl();

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param array|\Traversable
     */
    public function setExtraProperties($extraProperties);

    /**
     * @param array|\Traversable
     */
    public function setExtraNames($extraNames);

    /**
     * @param array|\Traversable
     */
    public function setExtraHttp($extraHttp);

    /**
     * @return array
     */
    public function getExtraProperties();

    /**
     * @return array
     */
    public function getExtraNames();

    /**
     * @return array
     */
    public function getExtraHttp();

    /**
     * @param string $key
     * @param string $value
     */
    public function addExtraProperty($key, $value);

    /**
     * @param string $key
     * @param string $value
     */
    public function addExtraName($key, $value);

    /**
     * @param string $key
     * @param string $value
     */
    public function addExtraHttp($key, $value);
}
