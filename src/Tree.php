<?php

namespace OuestCode\Tree;

use JsonSerializable;

class Tree implements JsonSerializable
{
    /** @var Node[] */
    protected array $nodes = [];

    public function __construct(
        array $items,
        protected string $parentKey = 'parentId',
        protected string $primaryKey = 'id'
    ) {
        $this->forge($items);
    }

    public function getNodeById($id): ?Node
    {
        return $this->nodes[$id] ?? null;
    }

    public function toArray(): array
    {
        $nodes = array_map(fn (Node $node) => $node->toArray(), $this->nodes);
        $nodes = array_values($nodes);

        return [
            'primaryKey' => $this->primaryKey,
            'parentKey' => $this->parentKey,
            'nodes' => $nodes,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function forge(array $items)
    {
        $children = [];

        foreach ($items as $item) {
            $node = $this->createNode($item);

            if (! $node->getParentId()) {
                continue;
            }

            if (empty($children[$node->getParentId()])) {
                $children[$node->getParentId()] = [];
            }

            $children[$node->getParentId()][] = $node->getId();
        }

        foreach ($children as $parentId => $childrenIds) {
            foreach ($childrenIds as $childId) {
                $this->nodes[$parentId]->addChild(
                    $this->nodes[$childId]
                );
            }
        }
    }

    private function createNode(array|object $item): Node
    {
        $node = new Node(
            $item,
            $this->parentKey,
            $this->primaryKey
        );

        $this->nodes[$node->getId()] = $node;

        return $node;
    }
}
