<?php

namespace SimplePay\Vendor\League\Container\Exception;

use SimplePay\Vendor\Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
