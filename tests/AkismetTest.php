<?php
/*
* This file is part of the fyrts/akismet library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akismet\Tests;

use PHPUnit\Framework\TestCase;
use Akismet\Akismet;
use Akismet\Comment;
use Akismet\Exception;

class AkismetTest extends TestCase
{
    /** @var TestClient */
    protected $client;
    
    /** @var Akismet */
    protected $api;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_REFERER'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test';
        
        $_SERVER['HTTP_ACCEPT'] = 'Test';
        $_SERVER['HTTP_ACCEPT_CHARSET'] = 'Test';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'Test';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'Test';
        $_SERVER['HTTP_CONNECTION'] = 'Test';
        
        $this->client = new TestClient();
        $this->api = new Akismet('api-key', 'blog-url');
        $this->api->setClient($this->client);
    }
    
    public function testDebugHeader()
    {
        $this->expectException(Exception::class);
        
        $this->client->queueResponse('invalid', [
            'X-akismet-debug-help' => 'error',
        ]);
        $this->api->verifyKey();
    }
    
    public function testAlertHeader()
    {
        $this->expectException(Exception::class);
        
        $this->client->queueResponse('invalid', [
            'X-akismet-alert-msg' => 'error',
        ]);
        $this->api->verifyKey();
    }
    
    public function testHttpError()
    {
        $this->expectException(Exception::class);
        
        $this->client->queueResponse('', [], 404);
        $this->api->verifyKey();
    }
    
    public function testCommentDefaultConstructor()
    {   
        $comment = new Comment();
        $parameters = $comment->getParameters();
        
        $this->assertArrayHasKey('user_ip', $parameters);
        $this->assertArrayHasKey('user_agent', $parameters);
        $this->assertArrayHasKey('referrer', $parameters);
    }
    
    public function testCommentDates()
    {
        $comment = new Comment([
            'date_a_gmt' => time(),
            'date_b_gmt' => new \DateTime(),
            'date_c_gmt' => date('c'),
        ]);
        $parameters = $comment->getParameters();
        
        $this->assertEquals(date('Y'), substr($parameters['date_a_gmt'], 0, 4));
        $this->assertEquals(date('Y'), substr($parameters['date_b_gmt'], 0, 4));
        $this->assertEquals(date('Y'), substr($parameters['date_c_gmt'], 0, 4));
    }
    
    public function testCommentParameterMethods()
    {
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        
        $comment = new Comment();
        $comment->setUserIp('127.0.0.1');
        $comment->setUserAgent('test');
        $comment->setReferrer('127.0.0.1');
        $comment->setPermalink('127.0.0.1');
        $comment->setCommentType('test');
        $comment->setCommentAuthor('test');
        $comment->setCommentAuthorEmail('test@example.org');
        $comment->setCommentAuthorUrl('127.0.0.1');
        $comment->setCommentContent('test');
        $comment->setCommentDate(time());
        $comment->setCommentPostModifiedDate(time());
        $comment->setBlogLanguage(['en', 'nl']);
        $comment->setBlogCharset('UTF-8');
        $comment->setUserRole('test');
        $comment->setTest();
        $comment->setRecheckReason('test');
        $parameters = $comment->getParameters();
        
        $this->assertArrayHasKey('user_ip', $parameters);
        $this->assertArrayHasKey('user_agent', $parameters);
        $this->assertArrayHasKey('referrer', $parameters);
        $this->assertArrayHasKey('permalink', $parameters);
        $this->assertArrayHasKey('comment_type', $parameters);
        $this->assertArrayHasKey('comment_author', $parameters);
        $this->assertArrayHasKey('comment_author_email', $parameters);
        $this->assertArrayHasKey('comment_author_url', $parameters);
        $this->assertArrayHasKey('comment_content', $parameters);
        $this->assertArrayHasKey('comment_date_gmt', $parameters);
        $this->assertArrayHasKey('comment_post_modified_gmt', $parameters);
        $this->assertArrayHasKey('blog_lang', $parameters);
        $this->assertArrayHasKey('blog_charset', $parameters);
        $this->assertArrayHasKey('user_role', $parameters);
        $this->assertArrayHasKey('is_test', $parameters);
        $this->assertArrayHasKey('recheck_reason', $parameters);
    }
    
    public function testCommentParameterAliases()
    {
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        
        $comment = new Comment();
        $comment->setReferer('127.0.0.1');
        $comment->setType('test');
        $comment->setAuthor('test');
        $comment->setAuthorEmail('test@example.org');
        $comment->setAuthorUrl('127.0.0.1');
        $comment->setContent('test');
        $comment->setDate(time());
        $comment->setPostModifiedDate(time());
        $parameters = $comment->getParameters();
        
        $this->assertArrayHasKey('referrer', $parameters);
        $this->assertArrayHasKey('comment_type', $parameters);
        $this->assertArrayHasKey('comment_author', $parameters);
        $this->assertArrayHasKey('comment_author_email', $parameters);
        $this->assertArrayHasKey('comment_author_url', $parameters);
        $this->assertArrayHasKey('comment_content', $parameters);
        $this->assertArrayHasKey('comment_date_gmt', $parameters);
        $this->assertArrayHasKey('comment_post_modified_gmt', $parameters);
    }
    
    public function testCommentParameterUnset()
    {
        $comment = new Comment();
        $comment->setParameter('user_ip', null);
        $parameters = $comment->getParameters();
        
        $this->assertArrayNotHasKey('user_ip', $parameters);
    }
    
    public function testCommentServerVariables()
    {
        $comment = new Comment();
        $parameters = $comment->includeServerVariables()->getParameters();
        
        $this->assertArrayHasKey('HTTP_ACCEPT', $parameters);
        $this->assertArrayHasKey('HTTP_ACCEPT_CHARSET', $parameters);
        $this->assertArrayHasKey('HTTP_ACCEPT_ENCODING', $parameters);
        $this->assertArrayHasKey('HTTP_ACCEPT_LANGUAGE', $parameters);
        $this->assertArrayHasKey('HTTP_CONNECTION', $parameters);
        $this->assertArrayHasKey('REQUEST_TIME', $parameters);
        $this->assertArrayHasKey('REQUEST_TIME_FLOAT', $parameters);
    }
    
    public function testInvalidComment()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->api->check('test');
    }
    
    public function testVerifyKeyValid()
    {
        $this->client->queueResponse('valid');
        $result = $this->api->verifyKey();
        
        $this->assertTrue($result);
    }
    
    public function testVerifyKeyInvalid()
    {
        $this->client->queueResponse('invalid');
        $result = $this->api->verifyKey();
        
        $this->assertFalse($result);
    }
    
    public function testCheckCommentHam()
    {
        $comment = new Comment();
        
        $this->client->queueResponse('false');
        $result = $this->api->check($comment);
        
        $this->assertFalse($result->isSpam);
        $this->assertFalse($result->isDiscardable);
    }
    
    public function testCheckCommentSpam()
    {
        $comment = new Comment();
        
        $this->client->queueResponse('true');
        $result = $this->api->check($comment);
        
        $this->assertTrue($result->isSpam);
        $this->assertFalse($result->isDiscardable);
    }
    
    public function testCheckCommentDiscardable()
    {
        $this->client->queueResponse('true', [
            'X-akismet-pro-tip' => 'discard',
        ]);
        $result = $this->api->commentCheck([]);
        
        $this->assertTrue($result->isSpam);
        $this->assertTrue($result->isDiscardable);
    }
    
    public function testSubmitSpamSuccess()
    {
        $comment = new Comment();
        
        $this->client->queueResponse('Thanks for making the web a better place.');
        $result = $this->api->submitSpam($comment);
        
        $this->assertTrue($result);
    }
    
    public function testSubmitSpamFailure()
    {
        $comment = new Comment();
        
        $this->client->queueResponse('error');
        $result = $this->api->submitSpam($comment);
        
        $this->assertFalse($result);
    }
    
    public function testSubmitHamSuccess()
    {
        $comment = new Comment();
        
        $this->client->queueResponse('Thanks for making the web a better place.');
        $result = $this->api->submitHam($comment);
        
        $this->assertTrue($result);
    }
    
    public function testSubmitHamFailure()
    {
        $comment = new Comment();
        
        $this->client->queueResponse('error');
        $result = $this->api->submitHam($comment);
        
        $this->assertFalse($result);
    }
    
    public function testGlobalParameters()
    {
        $comment = new Comment();
        
        $this->api->setLanguage('en');
        $this->api->setCharset('UTF-8');
        $this->api->includeServerVariables();
        
        $this->client->queueResponse('false');
        $this->api->check($comment);
        
        $parameters = $comment->getParameters();
        
        $this->assertArrayHasKey('blog_lang', $parameters);
        $this->assertArrayHasKey('blog_charset', $parameters);
        $this->assertArrayHasKey('HTTP_ACCEPT', $parameters);
    }
    
    public function testServerVariablesArray()
    {
        $comment = new Comment();
        
        $this->api->includeServerVariables(['HTTP_ACCEPT']);
        
        $this->client->queueResponse('false');
        $this->api->check($comment);
        
        $parameters = $comment->getParameters();
        
        $this->assertArrayHasKey('HTTP_ACCEPT', $parameters);
        $this->assertArrayNotHasKey('HTTP_CONNECTION', $parameters);
    }
}