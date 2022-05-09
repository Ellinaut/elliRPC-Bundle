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

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('elli_rpc');
        $root = $treeBuilder->getRootNode()->children();

        $root->scalarNode('defaultFileStorage')->defaultValue($this->projectDir . '/public/elliRpc');

        $root->scalarNode('application')->defaultValue('API');
        $root->scalarNode('description')->defaultNull();

        ###############################################
        ### Package
        ###############################################
        $package = $root->arrayNode('packages')
            ->useAttributeAsKey('name')
            ->validate()
            ->always()
            ->then(static function ($v) {
                $normalized = [];
                foreach ($v as $key => $value) {
                    $normalized[] = array_merge(
                        ['name' => $key],
                        $value
                    );
                }

                return $normalized;
            })
            ->end()
            ->arrayPrototype()
            ->children();

        $package->scalarNode('description')->defaultNull();

        ###############################################
        ### Package >>> Procedures
        ###############################################
        $procedure = $package->arrayNode('procedures')
            ->useAttributeAsKey('name')
            ->validate()
            ->always()
            ->then(static function ($v) {
                $normalized = [];
                foreach ($v as $key => $value) {
                    $normalized[] = array_merge(
                        ['name' => $key],
                        $value
                    );
                }

                return $normalized;
            })
            ->end()
            ->arrayPrototype()
            ->children();

        $procedure->scalarNode('description')->defaultNull();

        $request = $procedure->arrayNode('request')->children();
        $this->addTransportDefinition($request);

        $response = $procedure->arrayNode('response')->children();
        $this->addTransportDefinition($response);

        $procedure->arrayNode('errors')->scalarPrototype();

        ###############################################
        ### Package >>> Schemas
        ###############################################
        $schema = $package->arrayNode('schemas')
            ->useAttributeAsKey('name')
            ->validate()
            ->always()
            ->then(static function ($v) {
                $normalized = [];
                foreach ($v as $key => $value) {
                    $normalized[] = array_merge(
                        ['name' => $key],
                        $value
                    );
                }

                return $normalized;
            })
            ->end()
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
            ->validate()
            ->always()
            ->then(static function ($v) {
                $normalized = [];
                foreach ($v as $key => $value) {
                    $normalized[] = array_merge(
                        ['name' => $key],
                        $value
                    );
                }

                return $normalized;
            })
            ->end()
            ->arrayPrototype()
            ->children();

        $property->scalarNode('description')->defaultNull();

        $type = $property->arrayNode('type')->isRequired()->children();

        $type->scalarNode('context')->defaultNull();
        $type->scalarNode('type')->isRequired()->cannotBeEmpty();
        $type->arrayNode('options')->scalarPrototype();

        ###############################################
        ### Package >>> Errors
        ###############################################
        $error = $package->arrayNode('errors')
            ->useAttributeAsKey('code')
            ->validate()
            ->always()
            ->then(static function ($v) {
                $normalized = [];
                foreach ($v as $key => $value) {
                    $normalized[] = array_merge(
                        ['code' => $key],
                        $value
                    );
                }

                return $normalized;
            })
            ->end()
            ->arrayPrototype()
            ->children();

        $error->scalarNode('description')->defaultNull();

        $error->variableNode('context')
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
            ->thenInvalid('Invalid value for key "context".');

        return $treeBuilder;
    }

    protected function addTransportDefinition(NodeBuilder $builder): void
    {
        $builder->variableNode('data')
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
            ->thenInvalid('Invalid value for key "data".');

        $builder->variableNode('meta')
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
            ->thenInvalid('Invalid value for key "meta".');
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

        $nodeDefinition->children()->booleanNode('nullable')->defaultFalse();

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
