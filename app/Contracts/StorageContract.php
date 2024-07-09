<?php

namespace App\Contracts;

interface StorageContract
{
    public function save(string $path, string $filename);
    public function download(string $path, string $filename);
}
