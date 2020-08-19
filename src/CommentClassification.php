<?php
/*
* This file is part of the fyrts/akismet library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akismet;

class CommentClassification
{
    /** @var bool */
    public $isSpam;
    
    /** @var bool */
    public $isDiscardable;
    
    /** @var string */
    public $guid;
    
    public function __construct(bool $is_spam, bool $is_discardable, ?string $guid = null)
    {
        $this->isSpam = $is_spam;
        $this->isDiscardable = $is_discardable;
        $this->guid = $guid;
    }
}