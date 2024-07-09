<?php

namespace App\Supports;

use App\Contracts\FileReader;
use App\Dto\OrderDto;

class XMLReader implements FileReader
{
    protected $doc;
    protected array $errors = [];

    public function __construct(protected string $content)
    {
    }
    public function validate(): bool
    {
        $prev = libxml_use_internal_errors(true);
        $this->doc = simplexml_load_string(data: $this->content, options: LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $this->errors = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return false !== $this->doc && empty($this->errors);
    }

    /**
     * Return associative array
     * @return array
     */
    public function toArray(): array
    {
        $json = json_encode($this->doc);
        $data = json_decode($json, true);
        return convertEmptyArrToEmptyString($data);
    }

    /**
     * getErrors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
