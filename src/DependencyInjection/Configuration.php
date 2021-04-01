<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Philipp Marien
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ellirpc');
        $root = $treeBuilder->getRootNode()->children();

        $definition = $root->arrayNode('definition')->children();

        $definition->scalarNode('application')->cannotBeEmpty()->isRequired();
        $definition->arrayNode('contentTypes')->defaultValue(['json'])->scalarPrototype();
        $definition->scalarNode('description')->defaultNull();

        $package = $definition->arrayNode('packages')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $schema = $definition->arrayNode('schemas')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $package->scalarNode('description')->defaultNull();

        $procedure = $package->arrayNode('procedures')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $procedure->scalarNode('description')->defaultNull();
        $procedure->arrayNode('methods')->isRequired()->scalarPrototype();
        $procedure->arrayNode('contentTypes')->defaultValue(['json'])->scalarPrototype();

        $request = $procedure->arrayNode('request');
        $this->appendDataDefinition($request);

        $paginatedBy = $request->children()->arrayNode('paginatedBy');
        $this->appendSchemaReferenceDefinition($paginatedBy);

        $request->children()->arrayNode('sortedBy')
            ->useAttributeAsKey('name')
            ->scalarPrototype();

        $response = $procedure->arrayNode('response');
        $this->appendSchemaReferenceDefinition($response);

        $schema->booleanNode('abstract')->defaultFalse();
        $this->appendSchemaReferenceDefinition($schema->arrayNode('extends'));
        $schema->scalarNode('description')->defaultNull();

        $property = $schema->arrayNode('properties')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $property->scalarNode('description')->defaultNull();

        $type = $property->arrayNode('type')->isRequired()->children();

        $type->scalarNode('context')->defaultNull();
        $type->scalarNode('type')->isRequired()->cannotBeEmpty();
        $type->arrayNode('options')->scalarPrototype();

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $definition
     */
    protected function appendDataDefinition(ArrayNodeDefinition $definition): void
    {
        $this->appendSchemaReferenceDefinition($definition);

        $data = $definition->children();

        $wrappedBy = $data->arrayNode('wrappedBy');
        $this->appendSchemaReferenceDefinition($wrappedBy);
    }

    /**
     * @param ArrayNodeDefinition $definition
     */
    protected function appendSchemaReferenceDefinition(ArrayNodeDefinition $definition): void
    {
        $schemaReference = $definition->children();
        $schemaReference->scalarNode('context')->defaultNull();
        $schemaReference->scalarNode('schema')->isRequired()->cannotBeEmpty();
    }
}
