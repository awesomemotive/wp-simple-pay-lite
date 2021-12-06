<?php

namespace SimplePay\Vendor\League\Container;

use SimplePay\Vendor\Interop\Container\ContainerInterface as InteropContainerInterface;

trait ImmutableContainerAwareTrait
{
    /**
     * @var \SimplePay\Vendor\Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param  \SimplePay\Vendor\Interop\Container\ContainerInterface $container
     * @return $this
     */
    public function setContainer(InteropContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \SimplePay\Vendor\League\Container\ImmutableContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
