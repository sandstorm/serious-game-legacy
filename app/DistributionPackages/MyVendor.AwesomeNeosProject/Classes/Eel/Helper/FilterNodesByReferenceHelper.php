<?php
declare(strict_types=1);

namespace MyVendor\AwesomeNeosProject\Eel\Helper;

use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;

class FilterNodesByReferenceHelper implements ProtectedContextAwareInterface
{
    /**
     * We have an array of nodes that we want to filter according to the nodes they are referencing,
     * e.g. "give me all blog posts that have a reference to tag xyz".
     *
     * @param array<NodeInterface> $givenUnfilteredNodes
     * @param string $referencePropertyName (e.g. "tags")
     * @param string|null $referencedTitlesToFind (e.g. "tag1,tag2,tag3")
     * @return array<NodeInterface>
     */
    public function filterNodes(array $givenUnfilteredNodes, string $referencePropertyName, ?string $referencedTitlesToFind): array
    {

        if ($referencedTitlesToFind === '' || $referencedTitlesToFind === null) {
            return $givenUnfilteredNodes;
        }

        $referencedTitlesToFindArray = explode(',', $referencedTitlesToFind);

        $filteredNodes = [];
        foreach ($givenUnfilteredNodes as $givenNode) {
            /**
             * @var array<NodeInterface> $referencedNodesByProperty
             */
            $referencedNodesByProperty = $givenNode->getProperty($referencePropertyName);
            if ($referencedNodesByProperty) {
                foreach ($referencedNodesByProperty as $referencedNodeByProperty) {
                    foreach ($referencedTitlesToFindArray as $referencedTitleToFind) {
                        /**
                         * @var string $referencedNodeByPropertyTitle
                         */
                        $referencedNodeByPropertyTitle = $referencedNodeByProperty->getProperty('title');
                        if ($this->sanitizeQueryParameter($referencedNodeByPropertyTitle) === strtolower($referencedTitleToFind) && !in_array($givenNode, $filteredNodes)) {
                            $filteredNodes[] = $givenNode;
                        }
                    }
                }
            }
        }
        return $filteredNodes;
    }

    // the analogue function exists in EventList.ts -> keep in sync
    public function sanitizeQueryParameter(string $text): string
    {
        $text = mb_strtolower($text, 'utf8');

        $regexReplacements = [
            '/ä/' => 'ae',
            '/ö/' => 'oe',
            '/ü/' => 'ue',
            '/ß/' => 'ss',
            '/%/' => '-',
            '/î/u' => 'i',
            '/ç/u' => 'c',
            '/°/u' => 'o',
            '/@/u' => 'at',
            '/[áàâ]/u' => 'a',
            '/[éèê]/u' => 'e',
            '/[óòô]/u' => 'o',
            '/[úùû]/u' => 'u',
            '/[\(\)]/' => '',
            '/[\"<>]/' => '',
            '/[+,:\'\s\/#?!&\.\*–]+/' => '-',
            '/-+/' => '-',
            '/(^-)|(-$)/' => '',
            '/[^a-z0-9._~-]/' => '_',
        ];

        foreach ($regexReplacements as $pattern => $replacement) {
            /**
             * @var string $text
             */
            $text = preg_replace($pattern, $replacement, $text);
        }

        // remove duplicates
        $noDuplicates = ['-', '_'];
        foreach ($noDuplicates as $char) {
            /**
             * @var string $text
             */
            $text = preg_replace('/' . $char . $char . '+/', $char, $text);
        }

        return $text;
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

