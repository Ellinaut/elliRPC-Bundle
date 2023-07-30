# elliRPC-Bundle

Symfony bundle to integrate the elliRPC library into a symfony 5 project.

1. Requirements
2. How to install?
3. How to configure?
    1. Routing
    2. File Storage
    3. API-Definition
4. How to provide implementations?
    1. Procedure Validator
    2. Procedure Processor
    3. Transaction Listener
    4. Error Factory
    5. Error Translator

## Requirements

The bundle requires PHP in version 8.1 or higher and `symfony/framework-bundle` in version 5.* or 6.*.

The elliRPC-library requires implementations of `psr/http-message` and `psr/http-factory`.

We suggest to use `nyholm/psr7` as implementation for `psr/http-message` and `psr/http-factory`.

If you don't want to provide your own implementations of `Ellinaut\ElliRPC\File\ContentTypeGuesserInterface` and
`Ellinaut\ElliRPC\File\FilesystemInterface` we suggest to use `symfony/mime` and `symfony/filesystem`.

## How to install?

The bundle should be installed via composer: `ellinaut/ellirpc-bundle`.

Execute this command to install the bundle with all suggested implementations:

```shell
composer req ellinaut/ellirpc-bundle nyholm/psr7 symfony/mime symfony/filesystem
```

or execute this command to only install the bundle and use your own implementations matching the requirements:

```shell
composer req ellinaut/ellirpc-bundle
```

## How to enable endpoints?

To make all API endpoints available add this snippet to `config/routes.yaml`:

```yaml
elliRPC:
  resource: '@ElliRPCBundle/Resources/config/routes.xml'
```

## How to configure?

If you want to configure the bundle you should add the file `config/packages/elli_rpc.yaml`.

### File Storage

If you use the default file storage implementation via local file system, the default file storage will
be `%kernel.project_dir%/assets/elliRPC`.

You can change it via configuration:

```yaml
elli_rpc:
  defaultFileStorage: '%kernel.project_dir%/apiFiles'
```

### API-Definition

To configure the concrete definition of your API please configure it as follows:

```yaml
elli_rpc:
  application: 'My API' # default: 'API'
  description: 'My custom API' # default: null
  packages: # list of packages
    myPackage: # name of the package
      description: 'Description of my package.' # default: null
      fallbackLanguage: 'de' # default: null
      procedures: # list of procedure definitions
        myProcedure: # name of the procedure
          description: 'My procedure description' # default: null
          request: # request definition
            data: # transport definition, default: null
              context: null # default: null (for same package)
              schema: 'MySchema' # used schema name
              wrappedBy: # default: null
                context: 'OtherPackageName' # default: null
                schema: 'List'
              nullable: true # default: false
            meta: # meta definition, default: null
              context: 'OtherPackageName' # default: null (for same package)
              schema: 'MetaInfo' # used schema name
          response: # response definition
            data: null # transport definition, default: null, see: request -> data
            meta: null # meta definition, default: null, see: request -> meta
          errors: [ 'my_error' ] # list of possible error codes, default: []
          allowedUsage: 'STANDALONE' # default: null, possible: 'STANDALONE' or 'TRANSACTION'
      schemas: # list of schema definitions
        MySchema: # name of the schema
          abstract: true # default: false
          extends: # default: null
            context: 'https://schema.org' # default: null
            schema: 'Event' # name of the extended schema
          description: 'My schema description' # default: null
          properties: # list of property definitions
            myProperty: # name of the property
              description: '' # default: null
              type: # property type definition
                context: null # default: null
                type: 'string' # the build-in type or name of the used schema
                options: [ '@list' ] # a list of assigned options in the correct order, default: []
      errors: # list of error definitions
        my_error: # unique error code within the package
          description: 'My error description' # default: null
          context: # default: null
            context: 'https://schema.org' # default: null
            schema: 'Thing' # name of the used schema
```

## How to provide implementations?

Your custom implementations can be added via tagging your services in the symfony dependency injection container.

If your container uses autoconfiguration most of your services will be tagged automatically because the symfony
container will identify them by their implemented interfaces.

### Procedure Validator

Your procedure validator, which have to implement `Ellinaut\ElliRPC\Procedure\Validator\ProcedureValidatorInterface`,
have to be tagged with `elli_rpc.procedure_validator` to be found and used by this bundle.

```yaml
services:
  App\Api\Validator\CustomProcedureValidator:
    tags: [ 'elli_rpc.procedure_validator' ]
```

If your container uses autoconfiguration your service will be tagged and used automatically without the need of manual
tagging.

### Procedure Processor

Your procedure processors could be configured in two different ways.

First (and suggested) variant will be implementing `Ellinaut\ElliRPCBundle\Autoconfigure\DetectableProcedureProcessor`
or extending `Ellinaut\ElliRPCBundle\Autoconfigure\AbstractDetectableProcedureProcessor`.
These services should be tagged with `elli_rpc.procedure_processor.detected`.

```yaml
services:
  App\Api\Procedure\MyProcedure:
    tags: [ 'elli_rpc.procedure_processor.detected' ]
```

If your container uses autoconfiguration your services will be tagged and used automatically without the need of manual
tagging in this case.

The second variant will be implementing only `Ellinaut\ElliRPC\Procedure\Processor\ProcedureProcessorInterface` and
configure `package` and `procedure` manually via service definition:

```yaml
services:
  App\Api\Procedure\CustomProcedureProcessor:
    tags:
      - { name: 'elli_rpc.procedure_processor', package: 'myPackage', procedure: 'myProcedure' }
```

These variant is not autoconfigured but provides the possibility of register a single processor for multiple procedures.

### Transaction Listener

Your transaction listener, which have to implement `Ellinaut\ElliRPC\Procedure\Transaction\TransactionListenerInterface`
, have to be tagged with `elli_rpc.transaction_listener` to be found and used by this bundle.

```yaml
services:
  App\Api\Transaction\CustomTransactionListener:
    tags: [ 'elli_rpc.transaction_listener' ]
```

If your container uses autoconfiguration your service will be tagged and used automatically without the need of manual
tagging.

### Error Factory

Your error factory, which have to implement `Ellinaut\ElliRPC\Error\Factory\ErrorFactoryInterface`, have to be
tagged with `elli_rpc.error_factory` to be found and used by this bundle.

```yaml
services:
  App\Api\Error\CustomErrorFactory:
    tags: [ 'elli_rpc.error_factory' ]
```

If your container uses autoconfiguration your service will be tagged and used automatically without the need of manual
tagging.

### Error Translator

Your error translator, which have to implement `Ellinaut\ElliRPC\Error\Translator\ErrorTranslatorInterface`, have to be
tagged with `elli_rpc.error_translator` to be found and used by this bundle.

```yaml
services:
  App\Api\Error\CustomErrorTranslator:
    tags: [ 'elli_rpc.error_translator' ]
```

If your container uses autoconfiguration your service will be tagged and used automatically without the need of manual
tagging.
