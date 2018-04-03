<?php

namespace DRKTettnang\OperationHistory\Routing;

use Neos\Flow\Mvc\Routing\DynamicRoutePart;
use Neos\Flow\Annotations as Flow;
use DRKTettnang\OperationHistory\Domain\Model\Operation;

class OperationRoutePartHandler extends DynamicRoutePart
{
    /**
     * @Flow\Inject
     *
     * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationRepository
     */
    protected $operationRepository;

    protected function findValueToMatch($routePath)
    {
        $valueToMatch = $routePath;
        $splitStringPosition = strpos($valueToMatch, '/', strpos($valueToMatch, '/') + 1);

        if ($splitStringPosition === false) {
            $splitStringPosition = strpos($valueToMatch, '.');
        }
        if ($splitStringPosition !== false) {
            $valueToMatch = substr($valueToMatch, 0, $splitStringPosition);
        }

        return $valueToMatch;
    }

    protected function matchValue($requestPath)
    {
        if (!preg_match('/([0-9]{4})\/([0-9]+)/', $requestPath, $matches)) {
            return false;
        }
        $year = $matches[1];
        $number = $matches[2];

        $operation = $this->operationRepository->findByYearAndNumber($year, $number);

        if ($operation === null) {
            return false;
        }

        $this->value = array('__identity' => $this->persistenceManager->getIdentifierByObject($operation));

        return true;
    }

    /**
     * Checks whether the route part matches the configured RegEx pattern.
     */
    protected function resolveValue($value)
    {
        if (!$value instanceof Operation) {
            return false;
        }

        $this->value = $value->getYear().'/'.$value->getNumber();

        return true;
    }
}
