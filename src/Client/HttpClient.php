<?php
/*
* This file is part of the fyrts/akismet library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akismet\Client;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Request;

/**
 * @codeCoverageIgnore
 */
class HttpClient extends ClientBase
{
    /** @var \GuzzleHttp\Client */
    protected $client;
    
    public function __construct(string $api_key)
    {   
        $this->client = new Client([
            'base_uri' => 'https://' . $api_key . '.rest.akismet.com/1.1/',
            'http_errors' => false,
        ]);
    }
    
    /**
     * Get response from external server.
     *
     * @param string $endpoint
     * @param array $parameters Associative array of POST parameters
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function executeRequest(string $endpoint, array $parameters): ResponseInterface
    {
        return $this->client->post($endpoint, [
            'form_params' => $parameters,
        ]);
    }
}