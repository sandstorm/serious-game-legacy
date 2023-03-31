<?php
declare(strict_types=1);

namespace MyVendor\AwesomeNeosProject\Eel\Helper;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;

class FilterNodesByReferenceHelper implements ProtectedContextAwareInterface
{
    /**
     * We have an array of nodes that we want to filter according to the nodes they are referencing,
     * e.g. "give me all blog posts that have a reference to tag xyz".
     *
     * @param array $givenUnfilteredNodes
     * @param string $referencePropertyName
     * @param string|null $referencedTitlesToFind
     * @return array
     */
    public function filterNodes(array $givenUnfilteredNodes, string $referencePropertyName, ?string $referencedTitlesToFind)
    {

        if ($referencedTitlesToFind === '' || $referencedTitlesToFind === null) {
            return $givenUnfilteredNodes;
        }

        $referencedTitlesToFindArray = explode(',', $referencedTitlesToFind);

        $filteredNodes = [];
        foreach ($givenUnfilteredNodes as $givenNode) {
            /** @var NodeInterface $givenNode */
            $referencedNodesByProperty = $givenNode->getProperty($referencePropertyName);
            if ($referencedNodesByProperty) {
                foreach ($referencedNodesByProperty as $referencedNodeByProperty) {
                    foreach ($referencedTitlesToFindArray as $referencedTitleToFind) {
                        /** @var NodeInterface $referencedNodeByProperty */
                        if (strtolower($referencedNodeByProperty->getProperty('title')) === strtolower($referencedTitleToFind) && !in_array($givenNode, $filteredNodes)) {
                            $filteredNodes[] = $givenNode;
                        }
                    }
                }
            }
        }
        return $filteredNodes;
    }

    /**
     * All methods are considered safe, i.e. can be executed  from within Eel
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}

