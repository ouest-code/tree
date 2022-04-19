<?php

namespace Tests;

use OuestCode\Tree\Node;
use PHPUnit\Framework\TestCase;
use Tests\Artifact\Person;
use UnexpectedValueException;

class NodeTest extends TestCase
{
    /** @test */
    public function can_make_a_node()
    {
        $item = [
            'id' => 1789,
            'parentId' => null,
            'name' => 'France',
        ];

        $node = new Node($item, 'parentId', 'id');

        $this->assertEquals(1789, $node->getId());
        $this->assertEquals(1789, $node->id);
        $this->assertEquals('France', $node->name);
    }

    /** @test */
    public function can_make_a_node_with_a_string_has_id()
    {
        $item = [
            'id' => $id = 'hello',
            'parentId' => null,
            'name' => 'France',
        ];

        $node = new Node($item, 'parentId', 'id');

        $this->assertEquals($id, $node->getId());
    }

    /** @test */
    public function can_make_a_node_with_a_specific_primary_key()
    {
        $item = [
            'uuid' => $id = '18ddf912-949b-4dc2-9e4a-9c81190233d1',
            'parentId' => null,
            'name' => 'France',
        ];

        $node = new Node($item, 'parentId', 'uuid');

        $this->assertEquals($id, $node->getId());
    }

    /** @test */
    public function can_make_a_node_with_parent()
    {
        $parentItem = [
            'id' => 42,
            'parentId' => null,
            'name' => 'Parent'
        ];

        $parent = new Node($parentItem, 'parentId', 'id');

        $item = [
            'id' => 1789,
            'parentId' => 42,
            'name' => 'Child',
        ];

        $node = new Node($item, 'parentId', 'id');

        $node->setParent($parent);

        $this->assertEquals($parent, $node->getParent());
    }

    /** @test */
    public function cant_make_a_node_with_unexpected_parent()
    {
        $parentItem = [
            'id' => 42,
            'parentId' => null,
            'name' => 'Parent'
        ];

        $parent = new Node($parentItem, 'parentId', 'id');

        $item = [
            'id' => 1789,
            'parentId' => 66666666,
            'name' => 'Child',
        ];

        $node = new Node($item, 'parentId', 'id');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Given parent not match parent id.');

        $node->setParent($parent);
    }

    /** @test */
    public function can_make_a_node_with_children()
    {
        $item = [
            'id' => 1789,
            'parentId' => null,
            'name' => 'Robert',
        ];

        $node = new Node($item, 'parentId', 'id');

        $children = [
            [
                'id' => 11,
                'parentId' => 1789,
                'name' => 'John',
            ],
            [
                'id' => 12,
                'parentId' => 1789,
                'name' => 'Jane',
            ],
        ];

        $children = array_map(
            fn (array $child) => new Node($child, 'parentId', 'id'),
            $children
        );

        foreach ($children as $child) {
            $node->addChild($child);
        }

        $this->assertCount(2, $node->getChildren());
        $this->assertContainsOnly(Node::class, $node->getChildren());
        $this->assertContains($children[0], $node->getChildren());
        $this->assertContains($children[1], $node->getChildren());
    }

    /** @test */
    public function can_convert_to_array()
    {
        $item = [
            'id' => 1789,
            'parentId' => null,
            'name' => 'Robert',
        ];

        $node = new Node($item, 'parentId', 'id');

        $this->assertEquals($item, $node->toArray());
    }

    /** @test */
    public function can_have_ancestors()
    {
        $grandParentItem = [
            'id' => 79,
            'parentId' => null,
            'name' => 'Parent'
        ];

        $grandParent = new Node($grandParentItem, 'parentId', 'id');

        $parentItem = [
            'id' => 42,
            'parentId' => 79,
            'name' => 'Parent'
        ];

        $parent = new Node($parentItem, 'parentId', 'id');
        $parent->setParent($grandParent);

        $nodeItem = [
            'id' => 2,
            'parentId' => 42,
            'name' => 'Child',
        ];

        $node = new Node($nodeItem, 'parentId', 'id');
        $node->setParent($parent);

        $this->assertCount(2, $node->getAncestors());
        $this->assertContains($parent, $node->getAncestors());
        $this->assertContains($grandParent, $node->getAncestors());

        $this->assertCount(3, $node->getAncestorsAndSelf());
        $this->assertContains($node, $node->getAncestorsAndSelf());
        $this->assertContains($parent, $node->getAncestorsAndSelf());
        $this->assertContains($grandParent, $node->getAncestorsAndSelf());
    }

    /** @test */
    public function can_have_descendants()
    {
        $nodeItem = [
            'id' => 1,
            'parentId' => 42,
            'name' => 'Child',
        ];

        $node = new Node($nodeItem, 'parentId', 'id');

        $firstChildItem = [
            'id' => 2,
            'parentId' => 1,
            'name' => 'Child',
        ];

        $firstChild = new Node($firstChildItem, 'parentId', 'id');

        $grandChildItem = [
            'id' => 4,
            'parentId' => 2,
            'name' => 'Child',
        ];

        $grandChild = new Node($grandChildItem, 'parentId', 'id');

        $secondChildItem =  [
            'id' => 2,
            'parentId' => 1,
            'name' => 'Child',
        ];

        $secondChild = new Node($secondChildItem, 'parentId', 'id');

        $node->addChild($firstChild);
        $node->addChild($secondChild);

        $firstChild->addChild($grandChild);

        $this->assertCount(3, $node->getDescendants());
        $this->assertContains($firstChild, $node->getDescendants());
        $this->assertContains($secondChild, $node->getDescendants());
        $this->assertContains($grandChild, $node->getDescendants());

        $this->assertCount(1, $firstChild->getDescendants());
        $this->assertContains($grandChild, $firstChild->getDescendants());

        $this->assertCount(4, $node->getDescendantsAndSelf());
        $this->assertContains($node, $node->getDescendantsAndSelf());
        $this->assertContains($firstChild, $node->getDescendantsAndSelf());
        $this->assertContains($secondChild, $node->getDescendantsAndSelf());
        $this->assertContains($grandChild, $node->getDescendantsAndSelf());
    }

    /** @test */
    public function can_serialize_to_json()
    {
        $item = ['id' => 1, 'parentId' => null, 'name' => 'Chicken'];

        $node = new Node($item, 'parentId', 'id');

        $expectedJson = '{
            "id": 1,
            "parentId": null,
            "name": "Chicken"
        }';

        $actualJson = json_encode($node);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $actualJson
        );
    }

    /** @test */
    public function can_make_with_object_as_item()
    {
        $item = new Person(5, 1, 'J.Doe');

        $node = new Node($item, 'parentId', 'id');

        $this->assertEquals(5, $node->getId());
        $this->assertEquals(1, $node->getParentId());
        $this->assertEquals('J.Doe', $node->username);
    }

    /** @test */
    public function can_serialise_with_object_as_item()
    {
        $item = new Person(1,null, 'Doomy');

        $node = new Node($item, 'parentId', 'id');

        $expectedJson = '{
            "id": 1,
            "parentId": null,
            "username": "Doomy"
        }';

        $actualJson = json_encode($node);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $actualJson
        );
    }
}
