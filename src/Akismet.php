<?php
/*
* This file is part of the fyrts/akismet library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akismet;

use Akismet\Client\ClientBase;
use Akismet\Client\HttpClient;

class Akismet
{
    /** @var null|ClientBase */
    protected $client;
    
    /** @var string */
    protected $apiKey;
    
    /** @var string */
    protected $url;
    
    /** @var null|string */
    protected $language;
    
    /** @var null|string */
    protected $charset;
    
    /** @var null|bool|array */
    protected $alwaysIncludeServerVariables;
    
    /**
     * @param string $api_key Akismet API key
     * @param string $url     Root URL of the website or application
     */
    public function __construct(string $api_key, string $url)
    {
        $this->apiKey = $api_key;
        $this->url = $url;
    }
    
    /**
     * Define the language(s) in use on the blog or site.
     *
     * @param string|array $language Language or array of languages in ISO 639-1 format
     *
     * @return self
     */
    public function setLanguage($language): Akismet
    {
        $this->language = $language;
        return $this;
    }
    
    /**
     * Define the character encoding of values included with comments.
     *
     * @param string $charset
     *
     * @return self
     */
    public function setCharset(string $charset): Akismet
    {
        $this->charset = $charset;
        return $this;
    }
    
    /**
     * Always include additional server variables when submitting content to
     * Akismet. If no argument is passed, the following server information is
     * included:
     * * HTTP_ACCEPT
     * * HTTP_ACCEPT_CHARSET
     * * HTTP_ACCEPT_ENCODING
     * * HTTP_ACCEPT_LANGUAGE
     * * HTTP_CONNECTION
     * * REQUEST_TIME
     * * REQUEST_TIME_FLOAT
     *
     * @param null|array Array of $_SERVER indices to include, null for default
     *
     * @return self
     */
    public function includeServerVariables(?array $server_keys = null): Akismet
    {
        if (isset($server_keys)) {
            $this->alwaysIncludeServerVariables = $server_keys;
        } else {
            $this->alwaysIncludeServerVariables = true;
        }
        return $this;
    }
    
    /**
     * Normalize a comment for submission, and fill global parameters.
     *
     * @param array|Comment $comment
     *
     * @return self
     */
    protected function prepareComment($comment): Comment
    {
        if (is_array($comment)) {
            $comment = new Comment($comment);
        } else if (!$comment instanceof Comment) {
            throw new \InvalidArgumentException('Invalid argument. Expected Akismet\Comment or array.');
        }
        
        // Set global parameters
        $comment->setParameter('blog', $this->url);
        if (!empty($this->language)) {
            $comment->setBlogLanguage($this->language);
        }
        if (!empty($this->charset)) {
            $comment->setBlogCharset($this->charset);
        }
        if (!empty($this->alwaysIncludeServerVariables)) {
            $comment->includeServerVariables(
                $this->alwaysIncludeServerVariables === true ? null : $this->alwaysIncludeServerVariables
            );
        }
        
        return $comment;
    }
    
    // Endpoints
    
    /**
     * Key verification authenticates your key before calling other methods for
     * the first time. This is the first call that you should make to Akismet
     * and is especially useful if you will have multiple users with their own
     * Akismet subscriptions using your application.
     * 
     * Note that you do not need to call this method before every call; you
     * should only make this request to confirm that an API key is valid before
     * storing it locally.
     *
     * @link https://akismet.com/development/api/#verify-key Official documentation
     *
     * @throws Akismet\Exception if Akismet encounters an error
     *
     * @return bool Returns true if the API key is valid
     */
    public function verifyKey(): bool
    {
        $response = $this->getClient()->request('https://rest.akismet.com/1.1/verify-key', [
            'blog' => $this->url,
            'key' => $this->apiKey,
        ]);
        $body = $response->getBody()->getContents();
        return $body === 'valid';
    }
    
    /**
     * Check submitted content for spam.
     * 
     * Performance can drop dramatically if you choose to exclude data points.
     * The more data you send Akismet about each comment, the greater the
     * accuracy.
     *
     * @link https://akismet.com/development/api/#comment-check Official documentation
     *
     * @throws Akismet\Exception if Akismet encounters an error
     *
     * @param Comment|array The submitted content to check for spam. Either a
     *                      Akismet\Comment object, or an associative array
     *                      accepted by Akismet\Comment.
     *
     * @return CommentClassification Spam classification of the submitted content
     */
    public function check($comment): CommentClassification
    {
        $comment = $this->prepareComment($comment);
        
        $response = $this->getClient()->request('comment-check', $comment->getParameters());
        $headers = $response->getHeaders();
        $body = $response->getBody()->getContents();
        
        $is_spam = ($body === 'true');
        $is_discardable = ($headers['X-akismet-pro-tip'][0] ?? null === 'discard');
        $guid = empty($headers['X-akismet-guid']) ? null : $headers['X-akismet-guid'][0];
        
        return new CommentClassification($is_spam, $is_discardable, $guid);
    }
    
    /**
     * Submit content that wasn't marked as spam but should have been.
     *
     * It is very important that the values you submit with this call match
     * those of your comment-check calls as closely as possible.
     *
     * @link https://akismet.com/development/api/#submit-spam Official documentation
     *
     * @throws Akismet\Exception if Akismet encounters an error
     *
     * @param Comment|array The content to submit as spam. Either a
     *                      Akismet\Comment object, or an associative array
     *                      accepted by Akismet\Comment.
     *
     * @return bool Returns true on success
     */
    public function submitSpam($comment): bool
    {
        $comment = $this->prepareComment($comment);
        
        $response = $this->getClient()->request('submit-spam', $comment->getParameters());
        $body = $response->getBody()->getContents();
        
        return $body === 'Thanks for making the web a better place.';
    }
    
    /**
     * Submit content that was marked as spam but should not have been.
     *
     * It is very important that the values you submit with this call match
     * those of your comment-check calls as closely as possible.
     *
     * @link https://akismet.com/development/api/#submit-ham Official documentation
     *
     * @throws Akismet\Exception if Akismet encounters an error
     *
     * @param Comment|array The content to submit as ham. Either a
     *                      Akismet\Comment object, or an associative array
     *                      accepted by Akismet\Comment.
     *
     * @return bool Returns true on success
     */
    public function submitHam($comment): bool
    {
        $comment = $this->prepareComment($comment);
        
        $response = $this->getClient()->request('submit-ham', $comment->getParameters());
        $body = $response->getBody()->getContents();
        
        return $body === 'Thanks for making the web a better place.';
    }
    
    // Method aliases
    
    /** @see Akismet::check() */
    public function commentCheck($comment): CommentClassification
    {
        return $this->check($comment);
    }
    
    // Client management methods
    
    protected function getClient(): ClientBase
    {
        if (!isset($this->client)) {
            // @codeCoverageIgnoreStart
            $this->setClient(new HttpClient($this->apiKey));
            // @codeCoverageIgnoreEnd
        }
        return $this->client;
    }
    
    public function setClient(ClientBase $client): void
    {
        $this->client = $client;
    }
}