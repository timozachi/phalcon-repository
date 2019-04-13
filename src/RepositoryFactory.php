<?php

declare(strict_types=1);

namespace TZachi\PhalconRepository;

use Phalcon\Annotations\AdapterInterface as AnnotationsAdapterInterface;
use RuntimeException;
use function class_exists;

class RepositoryFactory
{
    public const REPOSITORY_ANNOTATION_NAME = 'Repository';

    /**
     * @var AnnotationsAdapterInterface
     */
    protected $annotations;

    /**
     * @var Repository[]
     */
    protected $repositories = [];

    public function __construct(AnnotationsAdapterInterface $annotations)
    {
        $this->annotations = $annotations;
    }

    /**
     * Gets an instance of a model's repository. If one doesn't exist, create it
     */
    public function get(string $modelName): Repository
    {
        if (isset($this->repositories[$modelName])) {
            return $this->repositories[$modelName];
        }

        return $this->repositories[$modelName] = $this->create($modelName);
    }

    /**
     * Creates a new repository for a specific model
     *
     * @param string $modelName The class name of the phalcon model
     */
    public function create(string $modelName): Repository
    {
        $repositoryClassName = Repository::class;

        $annotationsCollection = $this->annotations->get($modelName)->getClassAnnotations();
        if ($annotationsCollection !== false && $annotationsCollection->has(self::REPOSITORY_ANNOTATION_NAME)) {
            $repositoryClassName = $annotationsCollection->get(self::REPOSITORY_ANNOTATION_NAME)->getArgument(0);
            if ($repositoryClassName === null || !class_exists($repositoryClassName)) {
                throw new RuntimeException("Repository class '" . $repositoryClassName . "' doesn't exists");
            }
        }

        return new $repositoryClassName(new ModelWrapper($modelName));
    }
}