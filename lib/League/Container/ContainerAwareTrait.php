<?php

namespace SimplePay\Vendor\League\Container;

trait ContainerAwareTrait
{
    /**
     * @var \SimplePay\Vendor\League\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param  \SimplePay\Vendor\League\Container\ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \SimplePay\Vendor\League\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
