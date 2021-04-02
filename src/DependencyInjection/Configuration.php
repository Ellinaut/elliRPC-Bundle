<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Philipp Marien
 */
class Configuration implements ConfigurationInterface
{
    protected const NODE_KEY = 'node';

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

        $this->addPackageConfigurations($definition);

        $this->addSchemaConfigurations($definition);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $builder
     */
    protected function addPackageConfigurations(NodeBuilder $builder): void
    {
        $package = $builder->arrayNode('packages')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $package->scalarNode('description')->defaultNull();

        $procedure = $package->arrayNode('procedures')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $procedure->scalarNode('description')->defaultNull();

        $procedure->arrayNode('methods')
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->scalarPrototype()
            ->validate()
            ->ifNotInArray(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])
            ->thenInvalid('Invalid value for key "methods".');

        $procedure->arrayNode('contentTypes')->defaultValue(['json'])->scalarPrototype();

        $procedure->variableNode('request')
            ->defaultNull()
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value) {
                return self::processNodeDefinition(self::getRequestNodeDefinition(), $value);
            })
            ->end()
            ->validate()
            ->ifTrue(static function ($value) {
                return ($value !== null && !is_array($value));
            })
            ->thenInvalid('Invalid value for key "request".');

        $procedure->variableNode('response')
            ->defaultNull()
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value) {
                return self::processNodeDefinition(self::getDataNodeDefinition(), $value);
            })
            ->end()
            ->validate()
            ->ifTrue(static function ($value) {
                return ($value !== null && !is_array($value));
            })
            ->thenInvalid('Invalid value for key "response".');
    }

    protected function addSchemaConfigurations(NodeBuilder $builder): void
    {
        $schema = $builder->arrayNode('schemas')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();


        $schema->booleanNode('abstract')->defaultFalse();

        $schema->variableNode('extends')
            ->defaultNull()
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value) {
                return self::processNodeDefinition(self::getSchemaReferenceNodeDefinition(), $value);
            })
            ->end()
            ->validate()
            ->ifTrue(static function ($value) {
                return ($value !== null && !is_array($value));
            })
            ->thenInvalid('Invalid value for key "extends".');

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
    }

    /**
     * @param NodeDefinition $nodeDefinition
     * @param array $value
     * @return array
     */
    protected static function processNodeDefinition(NodeDefinition $nodeDefinition, array $value): array
    {
        return (new Processor())->process($nodeDefinition->getNode(true), [self::NODE_KEY => $value]);
    }

    /**
     * @return ArrayNodeDefinition
     */
    protected static function getRequestNodeDefinition(): ArrayNodeDefinition
    {
        $node = (new TreeBuilder(self::NODE_KEY))->getRootNode();

        $nodeBuilder = $node->children();
        $nodeBuilder->variableNode('data')
            ->defaultNull()
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value) {
                return self::processNodeDefinition(self::getDataNodeDefinition(), $value);
            });
        $nodeBuilder->variableNode('paginatedBy')
            ->defaultNull()
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value) {
                return self::processNodeDefinition(self::getSchemaReferenceNodeDefinition(), $value);
            });
        $nodeBuilder->arrayNode('sortedBy')->useAttributeAsKey('name')->scalarPrototype();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    protected static function getDataNodeDefinition(): ArrayNodeDefinition
    {
        $nodeDefinition = self::getSchemaReferenceNodeDefinition();
        $nodeDefinition->children()->variableNode('wrappedBy')
            ->defaultNull()
            ->beforeNormalization()
            ->ifArray()
            ->then(static function (array $value) {
                return self::processNodeDefinition(self::getSchemaReferenceNodeDefinition(), $value);
            });

        return $nodeDefinition;
    }

    /**
     * @return ArrayNodeDefinition
     */
    protected static function getSchemaReferenceNodeDefinition(): ArrayNodeDefinition
    {
        $node = (new TreeBuilder(self::NODE_KEY))->getRootNode();

        $nodeBuilder = $node->children();
        $nodeBuilder->scalarNode('context')->defaultNull();
        $nodeBuilder->scalarNode('schema')->isRequired()->cannotBeEmpty();

        return $node;
    }
}
