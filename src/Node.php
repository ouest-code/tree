<?php

namespace OuestCode\Tree;

use JsonSerializable;
use UnexpectedValueException;

class Node implements JsonSerializable
{
    protected ?Node $parent = null;

    protected array $children = [];

    public function __construct(
        protected array|object $item,
        protected string $parentKey,
        protected string $primaryKey
    ) {
    }

    public function __get(string $name)
    {
        return $this->getAttribute($name);
    }

    public function __isset(string $name): bool
    {
        if (is_object($this->item)) {
            return isset($this->item->$name);
        }

        return isset($this->item[$name]);
    }

    public function getId()
    {
        return $this->getAttribute($this->primaryKey);
    }

    public function getParentId()
    {
        return $this->getAttribute($this->parentKey);
    }

    public function getItem()
    {
        return $this->item;
    }

    public function toArray(): array
    {
        return (array) $this->item;
    }

    public function setParent(Node $parent): void
    {
        if ($parent->getId() !== $this->getParentId()) {
            throw new UnexpectedValueException('Given parent not match parent id.');
        }

        $this->parent = $parent;
    }

    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function addChild(Node $child): void
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** @return Node[] */
    public function getDescendantsAndSelf(): array
    {
        return array_merge($this->getDescendants(), [$this]);
    }

    /** @return Node[] */
    public function getDescendants(): array
    {
        $descendants = [];

        foreach ($this->children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getDescendants());
        }

        return $descendants;
    }

    /** @return Node[] */
    public function getAncestorsAndSelf(): array
    {
        return array_merge($this->getAncestors(), [$this]);
    }

    /** @return Node[] */
    public function getAncestors(): array
    {
        if (! $this->parent) {
            return [];
        }

        return array_merge([], $this->parent->getAncestorsAndSelf());
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function getAttribute(string $name)
    {
        if (is_object($this->item)) {
            return $this->item->$name;
        }

        return $this->item[$name];
    }
}
