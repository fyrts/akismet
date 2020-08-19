<?php
/*
* This file is part of the fyrts/akismet library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akismet\Tests;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;
use Akismet\Client\ClientBase;

class TestClient extends ClientBase
{
    /** @var array */
    protected $responseQueue = [];
    
    /**
    * Add a response to the response queue.
    *
    * @param string $body
    * @param array $header
    * @param int $status HTTP status code
    */
    public function queueResponse(
        string $body,
        array $headers = [],
        int $status = 200
    ): void {
        $this->responseQueue[] = new Response($status, $headers, $body);
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
        return array_shift($this->responseQueue);
    }
}