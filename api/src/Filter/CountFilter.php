<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

final class CountFilter extends AbstractFilter
{

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ($property !== "order_by_relation_count") return;

        $rootAlias = $queryBuilder->getRootAliases()[0];
        foreach ($value as $prop => $sortBy) { //NOTE: we use array_keys because getProperties() returns a map of property => strategy
            $parameterName = $queryNameGenerator->generateParameterName($prop);

            // TODO: if no explicit properties are queried via &properites[]=field, an error occurs!
            $queryBuilder
                ->innerJoin(sprintf("%s.%s", $rootAlias, $prop), "joined_" . $parameterName)
                ->addSelect(sprintf('COUNT(%s) as HIDDEN tmpCount ', "joined_" . $parameterName))
                ->addGroupBy(sprintf("%s.id", $rootAlias))
                ->addOrderBy("tmpCount", $sortBy ?? "asc");
        }
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["order_by_relation_count"] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'swagger' => [
                    'description' => 'Count by number of many to many relation.',
                    'name' => 'Order by relation count',
                ],
            ];
        }

        return $description;
    }
}
