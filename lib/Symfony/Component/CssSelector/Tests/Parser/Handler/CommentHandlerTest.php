<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimplePay\Vendor\Symfony\Component\CssSelector\Tests\Parser\Handler;

use SimplePay\Vendor\Symfony\Component\CssSelector\Parser\Handler\CommentHandler;
use SimplePay\Vendor\Symfony\Component\CssSelector\Parser\Reader;
use SimplePay\Vendor\Symfony\Component\CssSelector\Parser\Token;
use SimplePay\Vendor\Symfony\Component\CssSelector\Parser\TokenStream;

class CommentHandlerTest extends AbstractHandlerTest
{
    /** @dataProvider getHandleValueTestData */
    public function testHandleValue($value, Token $unusedArgument, $remainingContent)
    {
        $reader = new Reader($value);
        $stream = new TokenStream();

        $this->assertTrue($this->generateHandler()->handle($reader, $stream));
        // comments are ignored (not pushed as token in stream)
        $this->assertStreamEmpty($stream);
        $this->assertRemainingContent($reader, $remainingContent);
    }

    public function getHandleValueTestData()
    {
        return [
            // 2nd argument only exists for inherited method compatibility
            ['/* comment */', new Token(null, null, null), ''],
            ['/* comment */foo', new Token(null, null, null), 'foo'],
        ];
    }

    public function getDontHandleValueTestData()
    {
        return [
            ['>'],
            ['+'],
            [' '],
        ];
    }

    protected function generateHandler()
    {
        return new CommentHandler();
    }
}
