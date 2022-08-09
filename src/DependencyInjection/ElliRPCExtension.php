<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection;

use Ellinaut\ElliRPC\Definition\Loader\ArrayDefinitionLoader;
use Ellinaut\ElliRPC\Definition\Loader\PackageDefinitionLoaderInterface;
use Ellinaut\ElliRPC\Definition\Loader\ProcedureDefinitionLoaderInterface;
use Ellinaut\ElliRPC\Definition\Loader\SchemaDefinitionLoaderInterface;
use Ellinaut\ElliRPC\DefinitionHandler;
use Ellinaut\ElliRPC\Error\Factory\ErrorFactoryChain;
use Ellinaut\ElliRPC\Error\Factory\ErrorFactoryInterface;
use Ellinaut\ElliRPC\Error\Factory\FileErrorFactory;
use Ellinaut\ElliRPC\Error\Translator\ErrorTranslatorChain;
use Ellinaut\ElliRPC\Error\Translator\ErrorTranslatorInterface;
use Ellinaut\ElliRPC\File\Bridge\SymfonyContentTypeGuesser;
use Ellinaut\ElliRPC\File\Bridge\SymfonyFilesystem;
use Ellinaut\ElliRPC\File\ChainableFileLocator;
use Ellinaut\ElliRPC\File\ChainableFilesystem;
use Ellinaut\ElliRPC\File\ContentTypeGuesserInterface;
use Ellinaut\ElliRPC\File\FileLocatorChain;
use Ellinaut\ElliRPC\File\FileLocatorInterface;
use Ellinaut\ElliRPC\File\FilesystemChain;
use Ellinaut\ElliRPC\File\FilesystemInterface;
use Ellinaut\ElliRPC\File\LocalPathLocator;
use Ellinaut\ElliRPC\File\UnresolvedFileLocator;
use Ellinaut\ElliRPC\File\UnsupportedFilesystem;
use Ellinaut\ElliRPC\FileHandler;
use Ellinaut\ElliRPC\Procedure\Processor\ProcedureProcessorInterface;
use Ellinaut\ElliRPC\Procedure\Transaction\TransactionListenerInterface;
use Ellinaut\ElliRPC\Procedure\Transaction\TransactionManager;
use Ellinaut\ElliRPC\Procedure\Transaction\TransactionManagerInterface;
use Ellinaut\ElliRPC\Procedure\Validator\ProcedureValidatorChain;
use Ellinaut\ElliRPC\Procedure\Validator\ProcedureValidatorInterface;
use Ellinaut\ElliRPC\RPCHandler;
use Ellinaut\ElliRPCBundle\Autoconfigure\DetectableProcedureProcessor;
use Ellinaut\ElliRPCBundle\Autoconfigure\ProcedureProcessorRegistry;
use Ellinaut\ElliRPCBundle\Controller\DefinitionController;
use Ellinaut\ElliRPCBundle\Controller\FileController;
use Ellinaut\ElliRPCBundle\Controller\ProcedureController;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Philipp Marien
 */
class ElliRPCExtension extends ConfigurableExtension
{
    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @return void
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        ##############################################################
        ### Registries / Chains || Auto-Tags
        ##############################################################
        $container->registerForAutoconfiguration(ProcedureValidatorInterface::class)
            ->addTag('elli_rpc.procedure_validator');
        $container->autowire(ProcedureValidatorChain::class)->setPublic(false);
        if (!$container->hasDefinition(ProcedureValidatorInterface::class)) {
            $container->setAlias(ProcedureValidatorInterface::class, ProcedureValidatorChain::class);
        }

        $container->registerForAutoconfiguration(DetectableProcedureProcessor::class)
            ->addTag('elli_rpc.procedure_processor.detected');
        $container->autowire(ProcedureProcessorRegistry::class)->setPublic(false);
        if (!$container->hasDefinition(ProcedureProcessorInterface::class)) {
            $container->setAlias(ProcedureProcessorInterface::class, ProcedureProcessorRegistry::class);
        }

        $container->registerForAutoconfiguration(TransactionListenerInterface::class)
            ->addTag('elli_rpc.transaction_listener');
        $container->autowire(TransactionManager::class)
            ->setPublic(false);
        if (!$container->hasDefinition(TransactionManagerInterface::class)) {
            $container->setAlias(TransactionManagerInterface::class, TransactionManager::class);
        }

        $container->registerForAutoconfiguration(ErrorFactoryInterface::class)
            ->addTag('elli_rpc.error_factory');
        $container->autowire(ErrorFactoryChain::class)->setPublic(false);
        if (!$container->hasDefinition(ErrorFactoryInterface::class)) {
            $container->setAlias(ErrorFactoryInterface::class, ErrorFactoryChain::class);
        }

        $container->autowire(FileErrorFactory::class)
            ->addTag('elli_rpc.error_factory');

        $container->registerForAutoconfiguration(ErrorTranslatorInterface::class)
            ->addTag('elli_rpc.error_translator');
        $container->autowire(ErrorTranslatorChain::class)->setPublic(false);
        if (!$container->hasDefinition(ErrorTranslatorInterface::class)) {
            $container->setAlias(ErrorTranslatorInterface::class, ErrorTranslatorChain::class);
        }


        ##############################################################
        ### File Utilities
        ##############################################################
        $container->registerForAutoconfiguration(ChainableFileLocator::class)
            ->addTag('elli_rpc.file_locator');

        if (!$container->hasDefinition(FileLocatorInterface::class)) {
            if ($mergedConfig['files']['localPath']) {
                $container->autowire(LocalPathLocator::class)
                    ->setArgument('$localPath', $mergedConfig['files']['localPath'])
                    ->setPublic(false);
                $container->autowire(FileLocatorChain::class)
                    ->setArgument('$fallback', new Reference(LocalPathLocator::class))
                    ->setPublic(false);
            } else {
                $container->autowire(UnresolvedFileLocator::class)
                    ->setPublic(false);
                $container->autowire(FileLocatorChain::class)
                    ->setArgument('$fallback', new Reference(UnresolvedFileLocator::class))
                    ->setPublic(false);
            }

            $container->setAlias(FileLocatorInterface::class, FileLocatorChain::class);
        }

        $container->registerForAutoconfiguration(ChainableFilesystem::class)
            ->addTag('elli_rpc.filesystem');

        $container->autowire(UnsupportedFilesystem::class)->setPublic(false);
        if ($container->hasDefinition(Filesystem::class)) {
            $container->autowire(SymfonyFilesystem::class)->setPublic(false);
        }

        if (!$container->hasDefinition(FilesystemInterface::class)) {
            if ($mergedConfig['files']['enabled']) {
                if ($mergedConfig['files']['enableFallback'] && $container->hasDefinition(SymfonyFilesystem::class)) {
                    $container->autowire(FilesystemChain::class)
                        ->setArgument('$fallback', new Reference(SymfonyFilesystem::class))
                        ->setPublic(false);
                } else {
                    $container->autowire(FilesystemChain::class)
                        ->setArgument('$fallback', new Reference(UnsupportedFilesystem::class))
                        ->setPublic(false);
                }

                $container->setAlias(FilesystemInterface::class, FilesystemChain::class);
            } else {
                $container->setAlias(FilesystemInterface::class, UnsupportedFilesystem::class);
            }
        }

        if (!$container->hasDefinition(ContentTypeGuesserInterface::class)) {
            $container->autowire(SymfonyContentTypeGuesser::class)->setPublic(false);
            $container->setAlias(ContentTypeGuesserInterface::class, SymfonyContentTypeGuesser::class);
        }

        ##############################################################
        ### Definition Loader
        ##############################################################
        $definitionLoader = $container->autowire(ArrayDefinitionLoader::class)->setPublic(false);
        foreach ($mergedConfig['packages'] as $packageDefinition) {
            $definitionLoader->addMethodCall('registerPackage', [$packageDefinition]);
        }

        if (!$container->hasDefinition(PackageDefinitionLoaderInterface::class)) {
            $container->setAlias(PackageDefinitionLoaderInterface::class, ArrayDefinitionLoader::class);
        }

        if (!$container->hasDefinition(SchemaDefinitionLoaderInterface::class)) {
            $container->setAlias(SchemaDefinitionLoaderInterface::class, ArrayDefinitionLoader::class);
        }

        if (!$container->hasDefinition(ProcedureDefinitionLoaderInterface::class)) {
            $container->setAlias(ProcedureDefinitionLoaderInterface::class, ArrayDefinitionLoader::class);
        }

        ##############################################################
        ### Handler
        ##############################################################
        $container->autowire(DefinitionHandler::class)
            ->setPublic(false)
            ->setArgument('$application', $mergedConfig['application'])
            ->setArgument('$description', $mergedConfig['description']);

        $container->autowire(RPCHandler::class)->setPublic(false);

        $container->autowire(FileHandler::class)->setPublic(false);

        ##############################################################
        ### Symfony Psr-Http-Message-Bridge
        ##############################################################
        if (!$container->hasDefinition(HttpMessageFactoryInterface::class)) {
            if (!$container->hasDefinition(PsrHttpFactory::class)) {
                $container->autowire(PsrHttpFactory::class)
                    ->setPublic(false);
            }

            $container->setAlias(HttpMessageFactoryInterface::class, PsrHttpFactory::class)
                ->setPublic(false);
        }

        if (!$container->hasDefinition(HttpFoundationFactoryInterface::class)) {
            if (!$container->hasDefinition(HttpFoundationFactory::class)) {
                $container->autowire(HttpFoundationFactory::class)
                    ->setPublic(false);
            }

            $container->setAlias(HttpFoundationFactoryInterface::class, HttpFoundationFactory::class)
                ->setPublic(false);
        }

        ##############################################################
        ### Symfony Controller
        ##############################################################
        $container->autowire(DefinitionController::class)
            ->addTag('controller.service_arguments')
            ->setPublic(true);


        $container->autowire(ProcedureController::class)
            ->addTag('controller.service_arguments')
            ->setPublic(true);

        $container->autowire(FileController::class)
            ->addTag('controller.service_arguments')
            ->setPublic(true);
    }
}
