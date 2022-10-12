<?php

namespace App\Serializer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AutoGroupContextBuilder implements SerializerContextBuilderInterface
{
    
    private $decorated;
    private $isAdmin = false;
    private $isUser = false;
    private $tokenStorage;
    private const ALREADY_CALLED = 'MAX_DEPTH_SET';

    public function __construct(SerializerContextBuilderInterface $decorated, TokenStorageInterface $tokenStorage)
    {
        $this->decorated = $decorated;
        $this->tokenStorage = $tokenStorage;
    }


    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $tmpToken = $this->tokenStorage->getToken();
        if ($tmpToken !== null) {
            $roles = $tmpToken->getRoleNames();
            if (in_array("ROLE_USER", $roles)) {
                $this->isUser = true;
                if (in_array("ROLE_ADMIN", $roles)) {
                    $this->isAdmin = true;
                }
            }
        }
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $context['enable_max_depth'] = true;
        $operation = $context['operation'];
        if($normalization){
            $context["groups"] = $this->updateOperation($operation)->getNormalizationContext()["groups"];
        }else{
            $tmp = $this->updateOperation($operation)->getDenormalizationContext();
            dump($tmp);
            //$context["groups"] = $this->updateOperation($operation)->getDenormalizationContext()["groups"];
        }
        
        $context[self::ALREADY_CALLED] = true;

        return $context;
    }

    private function updateOperation(Operation $operation) {
        $previousNormalizationContext = $operation->getNormalizationContext();
        $groups = [];
        if($previousNormalizationContext && array_key_exists("groups", $previousNormalizationContext)){
            $groups = $previousNormalizationContext["groups"];
        }
        $routeNameTemplate = "_api_%s_%s";
        $uriTemplate = strtolower($operation->getUriTemplate());
        switch($operation->getName()){
            case sprintf($routeNameTemplate, $uriTemplate, "get_collection"):
                $groups = array_unique([...$groups,...$this->getDefaultGroups($operation->getShortName(), true, false, "get")]);
                break;
            case sprintf($routeNameTemplate, $uriTemplate, "get"):
            case sprintf($routeNameTemplate, $uriTemplate, "put"):
            case sprintf($routeNameTemplate, $uriTemplate, "patch"):
            case sprintf($routeNameTemplate, $uriTemplate, "post"):
                $groups = array_unique([...$groups,...$this->getDefaultGroups($operation->getShortName(), true, true, "get")]);
                break;
            case sprintf($routeNameTemplate, $uriTemplate, "delete"):
                $groups = [];
                break;   
        }
        $previousNormalizationContext["groups"] = $groups;
        $updatedOperation = $operation->withNormalizationContext($previousNormalizationContext);

        $previousDenormalizationContext = $operation->getDenormalizationContext();
        $denormalizationGroups = [];
        if($previousDenormalizationContext && array_key_exists("groups", $previousDenormalizationContext)){
            $denormalizationGroups = $previousDenormalizationContext["groups"];
        }
        switch($operation->getName()){
            case sprintf($routeNameTemplate, $uriTemplate, "put"):
                $denormalizationGroups = array_unique([...$denormalizationGroups,...$this->getDefaultGroups($operation->getShortName(), false, true, "put")]);
                break;
            case sprintf($routeNameTemplate, $uriTemplate, "patch"):
                $denormalizationGroups = array_unique([...$denormalizationGroups,...$this->getDefaultGroups($operation->getShortName(), false, true, "patch")]);
                break;
            case sprintf($routeNameTemplate, $uriTemplate, "post"):
                $denormalizationGroups = array_unique([...$denormalizationGroups,...$this->getDefaultGroups($operation->getShortName(), false, true, "post")]);
                break;
        }
        $previousDenormalizationContext["groups"] = $denormalizationGroups;

        $updatedOperation->withDenormalizationContext($previousDenormalizationContext);
    
        return $updatedOperation;
    }

    private function getDefaultGroups(string $shortName, bool $normalization, bool $isItem, string $operationName)
    {

        $shortName = strtolower($shortName);
        $readOrWrite = $normalization ? 'read' : 'write';
        $itemOrCollection = $isItem ? 'item' : 'collection';


        $tmpGroups = [];
        if ($this->isUser) {
            $tmpGroups[] = sprintf('%s:%s:%s:%s', "user", $shortName, $itemOrCollection, $operationName);
            if ($this->isAdmin) {
                $tmpGroups[] = sprintf('%s:%s:%s:%s', "admin", $shortName, $itemOrCollection, $operationName);
            }
        }else{
            $tmpGroups[] = sprintf('%s:%s:%s:%s', "anon", $shortName, $itemOrCollection, $operationName);
            //$tmpGroups[] = sprintf('%s:%s:%s:%s', "anon", $shortName, $itemOrCollection, $readOrWrite);
        }

        return $tmpGroups;
    }
}
