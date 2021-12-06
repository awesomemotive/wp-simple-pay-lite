<?php

namespace SimplePay\Vendor\League\Container;

interface ContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \SimplePay\Vendor\League\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the container
     *
     * @return \SimplePay\Vendor\League\Container\ContainerInterface
     */
    public function getContainer();
}
