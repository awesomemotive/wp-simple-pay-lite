<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimplePay\Vendor\Symfony\Component\CssSelector\Tests\Node;

use SimplePay\Vendor\Symfony\Component\CssSelector\Node\ElementNode;
use SimplePay\Vendor\Symfony\Component\CssSelector\Node\SelectorNode;

class SelectorNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [
            [new SelectorNode(new ElementNode()), 'Selector[Element[*]]'],
            [new SelectorNode(new ElementNode(), 'pseudo'), 'Selector[Element[*]::pseudo]'],
        ];
    }

    public function getSpecificityValueTestData()
    {
        return [
            [new SelectorNode(new ElementNode()), 0],
            [new SelectorNode(new ElementNode(), 'pseudo'), 1],
        ];
    }
}
