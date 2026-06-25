<?php

namespace App\Services;

use Hidehalo\Nanoid\Client;

class NanoIdGenerator
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Generate a secure, unique, and unpredictable NanoID.
     * Default length: 12. Matches pattern [a-zA-Z0-9_-].
     *
     * @param int $size
     * @return string
     */
    public function generate(int $size = 12): string
    {
        return $this->client->generateId($size);
    }
}
