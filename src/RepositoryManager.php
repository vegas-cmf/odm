<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM;

class RepositoryManager
{
    public function getRepository($entityClassName)
    {
        $entity = new $entityClassName;
        $repositoryClassName = new $entity->getRepository();
        $repository = new $repositoryClassName($entity);
    }
}