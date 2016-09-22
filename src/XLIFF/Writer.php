<?php

namespace Mooneye\Yaml2XliffConverter\XLIFF;

use XMLWriter;

class Writer
{
    /**
     * @var XMLWriter
     */
    private $writer;

    public function __construct()
    {
        $this->writer = new XMLWriter();
    }

    /**
     * @return bool
     */
    public function openMemory()
    {
        return $this->writer->openMemory();
    }

    /**
     * @param string $uri
     * @return bool
     */
    public function openUri($uri)
    {
        return $this->writer->openUri($uri);
    }

    public function startDocument()
    {
        $this->writer->setIndent(true);
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->startElement('xliff');
        $this->writer->writeAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
        $this->writer->writeAttribute('version', '1.2');
    }

    public function endDocument()
    {
        $this->writer->endElement();
        $this->writer->endDocument();
    }

    /**
     * @param string $sourceLanguage
     * @param string $targetLanguage
     */
    public function startFile($sourceLanguage, $targetLanguage)
    {
        $this->writer->startElement('file');
        $this->writer->writeAttribute('original', 'yml-file');
        $this->writer->writeAttribute('datatype', 'plaintext');
        $this->writer->writeAttribute('source-language', $sourceLanguage);
        $this->writer->writeAttribute('target-language', $targetLanguage);
        $this->writer->startElement('body');
    }

    public function endFile()
    {
        $this->writer->endElement(); // body
        $this->writer->endElement(); // file
    }

    /**
     * @param string $source
     * @param string $target
     * @param int $id
     * @param bool $keepSpaces
     */
    public function writeTransUnit($source, $target, $id = null, $keepSpaces = false)
    {
        $this->writer->startElement('trans-unit');

        if (isset($id)) {
            $this->writer->writeAttribute('id', $id);
        }

        if ($keepSpaces) {
            $this->writer->writeAttribute('xml:space', 'preserve');
        }

        $this->writer->writeElement('source', $source);
        $this->writer->writeElement('target', $target);
        $this->writer->endElement(); // trans-unit
    }

    /**
     * @param bool $empty
     * @return string
     */
    public function flush($empty = true)
    {
        return $this->writer->flush($empty);
    }
}
