<?php

namespace Ciconia\Extension\Gfm;

use Ciconia\Common\Text;
use Ciconia\Extension\ExtensionInterface;
use Ciconia\Markdown;

/**
 * Original source code from GitHub Flavored Markdown
 *
 * > Copyright 2013 GitHub Inc.
 * > https://help.github.com/articles/github-flavored-markdown
 *
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class InlineStyleExtension implements ExtensionInterface
{

    /**
     * @var array
     */
    private $hashes;

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('inline', array($this, 'processMultipleUnderScore'), 10);
    }

    /**
     * Multiple underscores in words
     *
     * It is not reasonable to italicize just part of a word, especially when you're dealing with code and names often
     * appear with multiple underscores. Therefore, GFM ignores multiple underscores in words.
     *
     * @param Text $text
     */
    public function processMultipleUnderScore(Text $text)
    {
        $text->replace('{<pre>.*?</pre>}m', function (Text $w) {
            $md5 = md5($w);
            $this->hashes[$md5] = $w;

            return "{gfm-extraction-$md5}";
        });

        $text->replace('/(^(?! {4}|\t)\w+_\w+_\w[\w_]*)/', function (Text $w) {
            $underscores = $w->split('//')->filter(function (Text $item) {
                return $item == '_';
            });

            if (count($underscores) >= 2) {
                $w->replace('/_/', '\\_');
            }

            return $w;
        });

        /** @noinspection PhpUnusedParameterInspection */
        $text->replace('/\{gfm-extraction-([0-9a-f]{32})\}/m', function (Text $w, Text $md5) {
            return "\n\n" . $this->hashes[(string)$md5];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gfmInlineStyle';
    }

}