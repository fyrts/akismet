# Akismet

PHP client for the [Akismet API](https://akismet.com/development/). [Akismet
terms of use](https://akismet.com/tos/) apply to usage of this library.

## Installation

`composer require fyrts/akismet`

## Usage

```php
use Akismet\Akismet;
use Akismet\Comment;

// Instantiate API client with your API key and website root URL
$akismet = new Akismet('api-key', 'https://www.example.org');

// Define content to check for spam
$comment = new Comment();
$comment->setAuthor('Author Name')
        ->setAuthorEmail('author@example.org')
        ->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');

// Optionally include additional environment variables for more accurate result
$comment->includeServerVariables();

// Check for spam
$result = $akismet->check($comment);

if ($result->isSpam) {
    echo 'Comment is spam';
}
if ($result->isDiscardable) {
    echo 'Comment is safe to discard';
}
```

It's possible to set Akismet parameters directly if preferred. A list of parameters is available at [the official API
documentation](https://akismet.com/development/api/#comment-check).

```php
$comment = new Comment([
    'comment_author' => 'Author Name',
    'comment_author_email' => 'author@example.org',
    'comment_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
]);
$comment->includeServerVariables();
```

If you'd like to provide all parameters by hand, you can just pass the array to the client directly.

```php
$result = $akismet->check([
    'comment_author' => 'Author Name',
    'comment_author_email' => 'author@example.org',
    'comment_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
]);
```

### Setting global parameters

For checking multiple comments, it's possible to set global language, charset and environment parameters for all
requests.

```php
$akismet->setLanguage('en');
$akismet->setCharset('UTF-8');
$akismet->includeServerVariables();

$result = $akismet->check($comment);
```

## Available endpoints

The following endpoints are accessible through the Akismet\Akismet class:

* `Akismet::verifyKey()`
* `Akismet::check($comment)` or `Akismet::commentCheck($comment)`
* `Akismet::submitSpam($comment)`
* `Akismet::submitHam($comment)`

## Available comment parameter methods

The following methods are accessible through the Akismet\Comment class for setting parameters:

* `Comment::setUserIp($ip)`
* `Comment::setUserAgent($user_agent)`
* `Comment::setReferrer($referrer)` or `Comment::setReferer($referrer)`
* `Comment::setPermalink($permalink)`
* `Comment::setType($type)` or `Comment::setCommentType($type)`
* `Comment::setAuthor($author)` or `Comment::setCommentAuthor($author)`
* `Comment::setAuthorEmail($email)` or `Comment::setCommentAuthorEmail($email)`
* `Comment::setAuthorUrl($url)` or `Comment::setCommentAuthorUrl($url)`
* `Comment::setContent($content)` or `Comment::setCommentContent($content)`
* `Comment::setDate($date)` or `Comment::setCommentDate($date)`
* `Comment::setPostModifiedDate($date)` or `Comment::setCommentPostModifiedDate($date)`
* `Comment::setBlogLanguage($language)`
* `Comment::setBlogCharset($charset)`
* `Comment::setUserRole($role)`
* `Comment::setTest()`
* `Comment::setRecheckReason($reason)`

Any custom parameters can be set through `Comment::setParameter($key, $value)`.

Date parameters accept DateTime objects, unix timestamps or date strings in ISO 8601 format.

## License

`fyrts/akismet` is licensed under the MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
