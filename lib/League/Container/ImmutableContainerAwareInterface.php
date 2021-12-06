<?php

namespace SimplePay\Vendor\League\Container;

use SimplePay\Vendor\Interop\Container\ContainerInterface as InteropContainerInterface;

interface ImmutableContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \SimplePay\Vendor\Interop\Container\ContainerInterface $container
     */
    public function setContainer(InteropContainerInterface $container);

    /**
     * Get the container
     *
     * @return \SimplePay\Vendor\League\Container\ImmutableContainerInterface
     */
    public function getContainer();
}
