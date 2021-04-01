<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Philipp Marien
 */
abstract class AbstractCompilerPass implements CompilerPassInterface
{
    /**
     * @return string
     */
    abstract protected function getServiceId(): string;

    /**
     * @return string
     */
    abstract protected function getTagName(): string;

    /**
     * @param Definition $definition
     * @param string $serviceId
     * @param array $config
     */
    abstract protected function modifyDefinition(Definition $definition, string $serviceId, array $config): void;

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->getServiceId())) {
            return;
        }

        $definition = $container->getDefinition($this->getServiceId());
        foreach ($container->findTaggedServiceIds($this->getTagName(), true) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $this->modifyDefinition($definition, $serviceId, $tag);
            }
        }
    }
}
