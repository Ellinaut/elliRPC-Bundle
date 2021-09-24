<?php

namespace Ellinaut\ElliRPCBundle\DependencyInjection;

use Ellinaut\ElliRPC\DataTransfer\Request\DocumentationRequest;
use Ellinaut\ElliRPC\DataTransfer\Request\FileDownloadRequest;
use Ellinaut\ElliRPC\DataTransfer\Request\PackageDefinitionsRequest;
use Ellinaut\ElliRPC\DataTransfer\Request\ProcedureExecutionBulkRequest;
use Ellinaut\ElliRPC\DataTransfer\Request\ProcedureExecutionRequest;
use Ellinaut\ElliRPC\DataTransfer\Request\SchemaDefinitionRequest;
use Ellinaut\ElliRPC\DataTransfer\Request\TransactionExecutionRequest;
use Ellinaut\ElliRPC\Definition\Factory\ApplicationDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\ApplicationDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\DataDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\DataDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\PackageDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\PackageDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\ProcedureDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\ProcedureDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\PropertyDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\PropertyDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\PropertyTypeDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\PropertyTypeDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\RequestDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\RequestDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\SchemaDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\SchemaDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Factory\SchemaReferenceDefinitionFactory;
use Ellinaut\ElliRPC\Definition\Factory\SchemaReferenceDefinitionFactoryInterface;
use Ellinaut\ElliRPC\Definition\Provider\ArrayDefinitionProvider;
use Ellinaut\ElliRPC\Definition\Provider\DefinitionProviderInterface;
use Ellinaut\ElliRPC\FileHandler\FileHandlerInterface;
use Ellinaut\ElliRPC\FileHandler\PhpFileHandler;
use Ellinaut\ElliRPC\Procedure\ProcessorInterface;
use Ellinaut\ElliRPC\Procedure\ProcessorRegistry;
use Ellinaut\ElliRPC\RequestParser\DefinitionRequestParser;
use Ellinaut\ElliRPC\RequestParser\FileRequestParser;
use Ellinaut\ElliRPC\RequestParser\ProcedureExecutionBulkRequestParser;
use Ellinaut\ElliRPC\RequestParser\ProcedureExecutionRequestParser;
use Ellinaut\ElliRPC\RequestParser\RequestParserChain;
use Ellinaut\ElliRPC\RequestParser\RequestParserInterface;
use Ellinaut\ElliRPC\RequestParser\TransactionsExecutionRequestParser;
use Ellinaut\ElliRPC\RequestProcessor\DefinitionProcessor;
use Ellinaut\ElliRPC\RequestProcessor\FileDownloadProcessor;
use Ellinaut\ElliRPC\RequestProcessor\FileUploadProcessor;
use Ellinaut\ElliRPC\RequestProcessor\ProcedureExecutionBulkProcessor;
use Ellinaut\ElliRPC\RequestProcessor\ProcedureExecutionProcessor;
use Ellinaut\ElliRPC\RequestProcessor\RequestProcessorInterface;
use Ellinaut\ElliRPC\RequestProcessor\RequestProcessorRegistry;
use Ellinaut\ElliRPC\RequestProcessor\TransactionExecutionProcessor;
use Ellinaut\ElliRPC\ResponseFactory\DefinitionJsonResponseFactory;
use Ellinaut\ElliRPC\ResponseFactory\FileResponseFactory;
use Ellinaut\ElliRPC\ResponseFactory\ProcedureBulkJsonResponseFactory;
use Ellinaut\ElliRPC\ResponseFactory\ProcedureExecutionJsonResponseFactory;
use Ellinaut\ElliRPC\ResponseFactory\ResponseFactoryInterface;
use Ellinaut\ElliRPC\ResponseFactory\ResponseFactoryRegistry;
use Ellinaut\ElliRPC\Server;
use Ellinaut\ElliRPCBundle\Controller\RPCController;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $container->autowire(PhpFileHandler::class)
            ->setArgument('$storageDirectory', $mergedConfig['fileStorage']['directory'])
            ->setArgument('$fileMode', $mergedConfig['fileStorage']['mode'])
            ->setPublic(false);
        $container->setAlias(FileHandlerInterface::class, PhpFileHandler::class)
            ->setPublic(false);

        $this->addDefinitionFactories($container);

        $container->autowire(ArrayDefinitionProvider::class)
            ->setArgument('$rawApplicationDefinition', $mergedConfig['definition'])
            ->setPublic(false);
        $container->setAlias(DefinitionProviderInterface::class, ArrayDefinitionProvider::class)
            ->setPublic(false);

        $this->addRequestParsers($container);

        $this->addRequestProcessors($container);

        $this->addProcessors($container);

        $this->addResponseFactories($container);

        $this->addPsrHttpMessageBridge($container);

        $container->autowire(Server::class)
            ->setPublic(false);

        $container->autowire(RPCController::class)
            ->addTag('controller.service_arguments')
            ->setPublic(true);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addDefinitionFactories(ContainerBuilder $container): void
    {
        $container->autowire(ApplicationDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(ApplicationDefinitionFactoryInterface::class, ApplicationDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(DataDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(DataDefinitionFactoryInterface::class, DataDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(PackageDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(PackageDefinitionFactoryInterface::class, PackageDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(ProcedureDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(ProcedureDefinitionFactoryInterface::class, ProcedureDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(PropertyDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(PropertyDefinitionFactoryInterface::class, PropertyDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(PropertyTypeDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(PropertyTypeDefinitionFactoryInterface::class, PropertyTypeDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(RequestDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(RequestDefinitionFactoryInterface::class, RequestDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(SchemaDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(SchemaDefinitionFactoryInterface::class, SchemaDefinitionFactory::class)
            ->setPublic(false);

        $container->autowire(SchemaReferenceDefinitionFactory::class)
            ->setPublic(false);
        $container->setAlias(SchemaReferenceDefinitionFactoryInterface::class, SchemaReferenceDefinitionFactory::class)
            ->setPublic(false);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addRequestParsers(ContainerBuilder $container): void
    {
        $container->autowire(RequestParserChain::class)
            ->setPublic(false);
        $container->setAlias(RequestParserInterface::class, RequestParserChain::class)
            ->setPublic(false);

        $container->autowire(DefinitionRequestParser::class)
            ->setPublic(false)
            ->addTag('elli_rpc.request_parser', ['priority' => 0]);

        $container->autowire(ProcedureExecutionRequestParser::class)
            ->setPublic(false)
            ->addTag('elli_rpc.request_parser', ['priority' => 1]);

        $container->autowire(ProcedureExecutionBulkRequestParser::class)
            ->setPublic(false)
            ->addTag('elli_rpc.request_parser', ['priority' => 2]);

        $container->autowire(TransactionsExecutionRequestParser::class)
            ->setPublic(false)
            ->addTag('elli_rpc.request_parser', ['priority' => 3]);

        $container->autowire(FileRequestParser::class)
            ->setPublic(false)
            ->addTag('elli_rpc.request_parser', ['priority' => 4]);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addRequestProcessors(ContainerBuilder $container): void
    {
        $container->autowire(RequestProcessorRegistry::class)->setPublic(false);
        $container->setAlias(RequestProcessorInterface::class, RequestProcessorRegistry::class)
            ->setPublic(false);

        $container->autowire(DefinitionProcessor::class)
            ->setPublic(false)
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => DocumentationRequest::class]
            )
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => PackageDefinitionsRequest::class]
            )
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => SchemaDefinitionRequest::class]
            );

        $container->autowire(FileDownloadProcessor::class)
            ->setPublic(false)
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => FileDownloadRequest::class]
            );

        $container->autowire(FileUploadProcessor::class)
            ->setPublic(false)
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => 4]
            );

        $container->autowire(ProcedureExecutionBulkProcessor::class)
            ->setPublic(false)
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => ProcedureExecutionBulkRequest::class]
            );

        $container->autowire(ProcedureExecutionProcessor::class)
            ->setPublic(false)
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => ProcedureExecutionRequest::class]
            );

        $container->autowire(TransactionExecutionProcessor::class)
            ->setPublic(false)
            ->addTag(
                'elli_rpc.request_processor',
                ['requestClass' => TransactionExecutionRequest::class]
            );
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addProcessors(ContainerBuilder $container): void
    {
        $container->autowire(ProcessorRegistry::class)
            ->setPublic(false);
        $container->setAlias(ProcessorInterface::class, ProcessorRegistry::class)
            ->setPublic(false);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addResponseFactories(ContainerBuilder $container): void
    {
        $container->autowire(ResponseFactoryRegistry::class)
            ->setPublic(false);
        $container->setAlias(ResponseFactoryInterface::class, ResponseFactoryRegistry::class)
            ->setPublic(false);

        $container->autowire(DefinitionJsonResponseFactory::class)
            ->setPublic(false)
            ->addTag('elli_rpc.response_factory');

        $container->autowire(FileResponseFactory::class)
            ->setPublic(false)
            ->addTag('elli_rpc.response_factory');

        $container->autowire(ProcedureBulkJsonResponseFactory::class)
            ->setPublic(false)
            ->addTag('elli_rpc.response_factory');

        $container->autowire(ProcedureExecutionJsonResponseFactory::class)
            ->setPublic(false)
            ->addTag('elli_rpc.response_factory');
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addPsrHttpMessageBridge(ContainerBuilder $container): void
    {
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
    }
}
