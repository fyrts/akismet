<?php
/*
* This file is part of the fyrts/akismet library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akismet\Client;

use Psr\Http\Message\ResponseInterface;
use Akismet\Exception;

abstract class ClientBase
{
    /**
     * @param string $endpoint
     * @param array $parameters Associative array of POST parameters
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $endpoint, array $parameters = []): ResponseInterface
    {
        $response = $this->executeRequest($endpoint, $parameters);
        $headers = $response->getHeaders();
        if (!empty($headers['X-akismet-debug-help'])) {
            throw new Exception(implode(', ', $headers['X-akismet-debug-help']));
        }
        if (!empty($headers['X-akismet-alert-msg'])) {
            throw new Exception(implode(', ', $headers['X-akismet-alert-msg']));
        }
        if ($response->getStatusCode() >= 400) {
            throw new Exception($response->getReasonPhrase());
        }
        return $response;
    }
    
    /**
     * Get response from external server.
     *
     * @param string $endpoint
     * @param array $parameters Associative array of POST parameters
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    abstract protected function executeRequest(string $endpoint, array $parameters): ResponseInterface;
}
