<?php

namespace Billink\Billink\Gateway\Helper;

/**
 * Class Xml
 * @package Billink\Billink\Gateway\Helper
 */
class Xml
{
    /**
     * @var array
     */
    protected $exceptions = [];

    /**
     * @param array $data
     * @param string $root
     * @param null $xml
     * @return mixed
     */
    public function convert(array $data, $root = 'root', $xml = null)
    {
        if ($xml === null) {
            $xml = new \SimpleXMLElement('<' . $root . '/>');
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Could not convert non-array data to XML');
        }

        foreach ($data as $key => $value) {
            if (is_numeric($key) || $value === NULL) {
                continue;
            }

            $key = ltrim($key, '0..9');

            if (is_array($value)) {
                $this->convert($value, $key, $xml->addChild($key));
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }

    /**
     * @param \SimpleXMLElement|string|null $xml
     * @return array
     */
    public function parse($xml = null)
    {
        if (!$xml) {
            return [];
        } elseif (is_string($xml)) {
            $xml = new \SimpleXMLElement($xml);
        }

        $result = [];

        foreach ((array)$xml as $index => $node) {
            $result[$index] = (is_object($node)) ? $this->parse($node) : $node;
        }

        return $result;
    }
}