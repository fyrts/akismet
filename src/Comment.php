<?php
/*
* This file is part of the fyrts/akismet library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akismet;

class Comment
{
    const TYPE_COMMENT = 'comment';
    const TYPE_FORUM_POST = 'forum-post';
    const TYPE_REPLY = 'reply';
    const TYPE_BLOG_POST = 'blog-post';
    const TYPE_CONTACT_FORM = 'contact-form';
    const TYPE_SIGNUP = 'signup';
    const TYPE_MESSAGE = 'message';

    /** @var array */
    protected $parameters = [];
    
    /**
     * @link https://akismet.com/development/api/#comment-check Supported parameters
     *
     * @param null|array $parameters Associative array with comment data
     */
    public function __construct(?array $parameters = null)
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->setUserIp($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else if (!empty($_SERVER['REMOTE_ADDR'])) {
            $this->setUserIp($_SERVER['REMOTE_ADDR']);
        }
        
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        }
        
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $this->setReferrer($_SERVER['HTTP_REFERER']);
        }
        
        if (!empty($parameters)) {
            foreach ($parameters as $key => $value) {
                $this->setParameter($key, $value);
            }
        }
    }
    
    /**
     * Set or change the value for a specific parameter.
     *
     * @link https://akismet.com/development/api/#comment-check Supported parameters
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function setParameter(string $key, $value): Comment
    {
        if (is_null($value)) {
            unset($this->parameters[$key]);
        } else {
            if (!is_string($value)) {
                if (is_int($value) && substr($key, -4) === '_gmt') {
                    $value = date('c', $value);
                } else if ($value instanceof \DateTime) {
                    $value = $value->format('c');
                }
            }
            $this->parameters[$key] = strval($value);
        }
        return $this;
    }
    
    /**
     * Get all currently defined parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    // Parameter specific methods
    
    /**
     * Set or change the IP address of the content submitter.
     *
     * @param string $ip
     *
     * @return self
     */
    public function setUserIp(string $ip): Comment
    {
        return $this->setParameter('user_ip', $ip);
    }
    
    /**
     * Set or change the user agent string of the web browser submitting the
     * content.
     *
     * @param string $user_agent
     *
     * @return self
     */
    public function setUserAgent(string $user_agent): Comment
    {
        return $this->setParameter('user_agent', $user_agent);
    }
    
    /**
     * Set or change the HTTP referrer.
     *
     * @param string $referrer
     *
     * @return self
     */
    public function setReferrer(string $referrer): Comment
    {
        return $this->setParameter('referrer', $referrer);
    }
    
    /**
     * Set or change the full permanent URL of the entry the comment was
     * submitted to.
     *
     * @param string $permalink
     *
     * @return self
     */
    public function setPermalink(string $permalink): Comment
    {
        return $this->setParameter('permalink', $permalink);
    }
    
    /**
     * A string that describes the type of content being sent. Either one of the
     * Comment::TYPE_* constants, or a custom value.
     *
     * @link https://blog.akismet.com/2012/06/19/pro-tip-tell-us-your-comment_type/
     * Information about custom values
     *
     * @param string $type
     *
     * @return self
     */
    public function setCommentType(string $type): Comment
    {
        return $this->setParameter('comment_type', $type);
    }
    
    /**
     * Set or change the name of the author who submitted the content.
     *
     * @param string $author
     *
     * @return self
     */
    public function setCommentAuthor(string $author): Comment
    {
        return $this->setParameter('comment_author', $author);
    }
    
    /**
     * Set or change the email address of the author who submitted the content.
     *
     * @param string $email
     *
     * @return self
     */
    public function setCommentAuthorEmail(string $email): Comment
    {
        return $this->setParameter('comment_author_email', $email);
    }
    
    /**
     * Set or change the website URL of the author who submitted the content.
     *
     * @param string $url
     *
     * @return self
     */
    public function setCommentAuthorUrl(string $url): Comment
    {
        return $this->setParameter('comment_author_url', $url);
    }
    
    /**
     * Set or change the content or message that was submitted.
     *
     * @param string $content
     *
     * @return self
     */
    public function setCommentContent(string $content): Comment
    {
        return $this->setParameter('comment_content', $content);
    }
    
    /**
     * Set or change the UTC timestamp of the creation of the content. May be
     * omitted if the comment is sent to the API at the time it is created.
     *
     * @param \DateTime|int|string $date DateTime object, unix timestamp, or date string in ISO 8601 format.
     *
     * @return self
     */
    public function setCommentDate($date): Comment
    {
        return $this->setParameter('comment_date_gmt', $date);
    }
    
    /**
     * Set or change the UTC publication timestamp of the entry on which the
     * comment was posted.
     *
     * @param \DateTime|int|string $date DateTime object, unix timestamp, or date string in ISO 8601 format.
     *
     * @return self
     */
    public function setCommentPostModifiedDate($date): Comment
    {
        return $this->setParameter('comment_post_modified_gmt', $date);
    }
    
    /**
     * Set or change the language(s) in use on the blog or site.
     *
     * @param string|array $language Language or array of languages in ISO 639-1 format
     *
     * @return self
     */
    public function setBlogLanguage($language): Comment
    {
        if (is_array($language)) {
            $language = implode(',', $language);
        }
        return $this->setParameter('blog_lang', $language);
    }
    
    /**
     * Set or change the character encoding of data included with this comment.
     *
     * @param string $charset
     *
     * @return self
     */
    public function setBlogCharset(string $charset): Comment
    {
        return $this->setParameter('blog_charset', $charset);
    }
    
    /**
     * Set or change the user role of the user who submitted the content.
     *
     * If you set it to “administrator”, Akismet will always classify the
     * content as ham.
     *
     * @param string $role
     *
     * @return self
     */
    public function setUserRole(string $role): Comment
    {
        return $this->setParameter('user_role', $role);
    }
    
    /**
     * Define this comment as test data.
     *
     * @param bool $is_test
     *
     * @return self
     */
    public function setTest(bool $is_test = true): Comment
    {
        return $this->setParameter('is_test', $is_test ? 1 : null);
    }
    
    /**
     * Set or change the reason for rechecking previously checked content.
     *
     * @param string $recheck_reason
     *
     * @return self
     */
    public function setRecheckReason(string $recheck_reason): Comment
    {
        return $this->setParameter('recheck_reason', $recheck_reason);
    }
    
    /**
     * Include additional server and environment variables with this content. If
     * no argument is passed, the following server information is included:
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
    public function includeServerVariables(?array $server_keys = null): Comment
    {
        if (!isset($server_keys)) {
            $server_keys = [
                'HTTP_ACCEPT',
                'HTTP_ACCEPT_CHARSET',
                'HTTP_ACCEPT_ENCODING',
                'HTTP_ACCEPT_LANGUAGE',
                'HTTP_CONNECTION',
                'REQUEST_TIME',
                'REQUEST_TIME_FLOAT',
            ];
        }
        foreach ($server_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $this->setParameter($key, $_SERVER[$key]);
            }
        }
        return $this;
    }
    
    // Method aliases
    
    /** @see Comment::setReferrer() */
    public function setReferer(string $referrer): Comment
    {
        return $this->setReferrer($referrer);
    }
    
    /** @see Comment::setCommentType() */
    public function setType(string $type): Comment
    {
        return $this->setCommentType($type);
    }
    
    /** @see Comment::setCommentAuthor() */
    public function setAuthor(string $author): Comment
    {
        return $this->setCommentAuthor($author);
    }
    
    /** @see Comment::setCommentAuthorEmail() */
    public function setAuthorEmail(string $email): Comment
    {
        return $this->setCommentAuthorEmail($email);
    }
    
    /** @see Comment::setCommentAuthorUrl() */
    public function setAuthorUrl(string $url): Comment
    {
        return $this->setCommentAuthorUrl($url);
    }
    
    /** @see Comment::setCommentContent() */
    public function setContent(string $content): Comment
    {
        return $this->setCommentContent($content);
    }
    
    /** @see Comment::setCommentDate() */
    public function setDate($date): Comment
    {
        return $this->setCommentDate($date);
    }
    
    /** @see Comment::setCommentPostModifiedDate() */
    public function setPostModifiedDate($date): Comment
    {
        return $this->setCommentPostModifiedDate($date);
    }
}