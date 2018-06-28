<?php

namespace Bkstg\NoticeBoardBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class BkstgNoticeBoardExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @param array            $configs   The configuration array.
     * @param ContainerBuilder $container The container.
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // If the timeline bundle is active register timeline services.
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['BkstgTimelineBundle'])) {
            $loader->load('services.timeline.yml');
        }
    }
}
