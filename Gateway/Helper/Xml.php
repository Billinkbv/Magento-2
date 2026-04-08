<?php

namespace Billink\Billink\Gateway\Helper;

use Exception;
use InvalidArgumentException;
use SimpleXMLElement;

use function htmlspecialchars;
use function is_array;
use function is_numeric;
use function is_object;
use function is_string;
use function ltrim;

class Xml
{
    /**
     * @throws Exception
     */
    public function convert(array $data, string $root = 'root', ?SimpleXMLElement $xml = null): bool|string
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement('<' . $root . '/>');
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Could not convert non-array data to XML');
        }

        foreach ($data as $key => $value) {
            if (is_numeric($key) || $value === null) {
                continue;
            }

            $key = ltrim($key, '0..9');

            if (is_array($value)) {
                $this->convert($value, $key, $xml->addChild($key));
            } else {
                $xml->addChild($key, htmlspecialchars($value, ENT_QUOTES | ENT_HTML5));
            }
        }

        return $xml->asXML();
    }

    /**
     * @throws Exception
     */
    public function parse(SimpleXMLElement|string|null $xml = null): array
    {
        if (!$xml) {
            return [];
        }

        if (is_string($xml)) {
            $xml = new SimpleXMLElement($xml);
        }

        $result = [];

        foreach ((array) $xml as $index => $node) {
            $result[$index] = (is_object($node)) ? $this->parse($node) : $node;
        }

        return $result;
    }
}
