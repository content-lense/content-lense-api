<?php
// api/src/Filter/MultipleFieldSearchFilter.php
namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Selects entities where each search term is found somewhere
 * in at least one of the specified properties.
 * Search terms must be separated by spaces.
 * Search is case insensitive.
 * All specified properties type must be string.
 * @package App\Filter
 */
class MultipleFieldSearchFilter extends AbstractFilter {
    private $searchParameterName;

    private $joinedSubressources = [];
    /**
     * Add configuration parameter
     * {@inheritdoc}
     * @param string $searchParameterName The parameter whose value this filter searches for
     */
    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger = null, array $properties = null, NameConverterInterface $nameConverter = null, string $searchParameterName = 'q') {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
        $this->searchParameterName = $searchParameterName;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void {
        if (null === $value || $property !== $this->searchParameterName) {
            return;
        }

        $words = explode(' ', $value);
        foreach ($words as $word) {
            if (empty($word)) continue;

            $this->addWhere($queryBuilder, $word, $queryNameGenerator->generateParameterName($property));
        }
    }



    private function addWhere($queryBuilder, $word, $parameterName) {


        // Build OR expression
        $orExp = $queryBuilder->expr()->orX();

        foreach ($this->getProperties() as $prop => $ignoored) {
            $explodedByDot = explode(".", $prop);
            $isSubresourceFilter = count($explodedByDot) > 1;
            $alias = $queryBuilder->getRootAliases()[0];
            if ($isSubresourceFilter) {
                $subresource = $explodedByDot[0];
                $subresourceField = $explodedByDot[1];
                if (!in_array($subresource, $this->joinedSubressources)) {
                    $queryBuilder->join(sprintf("%s.%s", $alias, $subresource), $subresource);
                    $this->joinedSubressources[] = $subresource;
                }
                $alias = $subresource;
                $prop = $subresourceField;
            }

            $orExp->add($queryBuilder->expr()->like('LOWER(CAST(' . $alias . '.' . $prop . ' as string))', ':' . $parameterName));
        }

        $queryBuilder
            ->andWhere('(' . $orExp . ')')
            ->setParameter($parameterName, '%' . strtolower($word) . '%');
    }

    /** {@inheritdoc} */
    public function getDescription(string $resourceClass): array {
        $props = $this->getProperties();
        if (null === $props) {
            throw new BadRequestHttpException('Properties must be specified');
        }
        return [
            $this->searchParameterName => [
                'property' => implode(', ', array_keys($props)),
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Selects entities where each search term is found somewhere in at least one of the specified properties',
                ]
            ]
        ];
    }
}
