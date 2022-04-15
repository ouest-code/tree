<?php

namespace Tests;

use OuestCode\Tree\Tree;
use PHPUnit\Framework\TestCase;
use Tests\Artifact\Person;

class TreeTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideTreeData
     */
    public function can_forge_a_tree(...$items)
    {
        $tree = new Tree($items);

        $godNode = $tree->getNodeById(1);
        $this->assertEquals('God', $godNode->toArray()['username']);
        $this->assertCount(2, $godNode->getChildren());
        $this->assertCount(3, $godNode->getDescendants());
        $this->assertNull($godNode->getParent());
        $this->assertCount(0, $godNode->getAncestors());

        $zeusNode = $tree->getNodeById(2);
        $this->assertEquals('Zeus', $zeusNode->toArray()['username']);
        $this->assertCount(0, $zeusNode->getChildren());
        $this->assertCount(0, $zeusNode->getDescendants());
        $this->assertSame($godNode, $zeusNode->getParent());
        $this->assertCount(1, $zeusNode->getAncestors());
        $this->assertContains($godNode, $zeusNode->getAncestors());
        $this->assertContains($zeusNode, $godNode->getChildren());

        $janeNode = $tree->getNodeById(3);
        $this->assertEquals('Jane', $janeNode->toArray()['username']);
        $this->assertCount(1, $janeNode->getChildren());
        $this->assertCount(1, $janeNode->getDescendants());
        $this->assertSame($godNode, $janeNode->getParent());
        $this->assertCount(1, $janeNode->getAncestors());
        $this->assertContains($godNode, $janeNode->getAncestors());

        $johnNode = $tree->getNodeById(4);
        $this->assertEquals('John', $johnNode->toArray()['username']);
        $this->assertCount(0, $johnNode->getChildren());
        $this->assertCount(0, $johnNode->getDescendants());
        $this->assertSame($janeNode, $johnNode->getParent());
        $this->assertCount(2, $johnNode->getAncestors());
        $this->assertContains($janeNode, $johnNode->getAncestors());
        $this->assertContains($godNode, $johnNode->getAncestors());
        $this->assertContains($johnNode, $janeNode->getChildren());
    }

    /** @test */
    public function can_make_a_tree_with_custom_primary_key()
    {
        $tree = new Tree([
            [
                'uuid' => 'b08f82b9-e8cc-44fb-b99a-4929bfcf02a4',
                'parentUuid' => null,
                'username' => 'God'
            ],
            [
                'uuid' => 'eaaf3215-17ba-4779-b444-d4a8203f1096',
                'parentUuid' => 'b08f82b9-e8cc-44fb-b99a-4929bfcf02a4',
                'username' => 'Zeus'
            ],
        ], 'parentUuid', 'uuid');

        $godNode = $tree->getNodeById('b08f82b9-e8cc-44fb-b99a-4929bfcf02a4');
        $this->assertEquals('God', $godNode->toArray()['username']);
        $this->assertCount(1, $godNode->getChildren());
        $this->assertCount(1, $godNode->getDescendants());
        $this->assertNull($godNode->getParent());
        $this->assertCount(0, $godNode->getAncestors());

        $zeusNode = $tree->getNodeById('eaaf3215-17ba-4779-b444-d4a8203f1096');
        $this->assertEquals('Zeus', $zeusNode->toArray()['username']);
        $this->assertCount(0, $zeusNode->getChildren());
        $this->assertCount(0, $zeusNode->getDescendants());
        $this->assertSame($godNode, $zeusNode->getParent());
        $this->assertCount(1, $zeusNode->getAncestors());
        $this->assertContains($godNode, $zeusNode->getAncestors());
        $this->assertSame($zeusNode, $godNode->getChildren()[0]);
    }

    /** @test */
    public function can_convert_to_array()
    {
        $items = [
            ['id' => 1, 'parentId' => null, 'name' => 'Chicken'],
            ['id' => 2, 'parentId' => 1, 'name' => 'Egg'],
        ];

        $tree = new Tree($items);

        $expectedArray = [
            'primaryKey' => 'id',
            'parentKey' => 'parentId',
            'nodes' => $items,
        ];

        $this->assertEquals($expectedArray, $tree->toArray());
    }

    /** @test */
    public function can_serialize_to_json()
    {
        $items = [
            ['id' => 1, 'parentId' => null, 'name' => 'Chicken'],
            ['id' => 2, 'parentId' => 1, 'name' => 'Egg'],
        ];

        $tree = new Tree($items);

        $expectedJson = '{
            "primaryKey": "id",
            "parentKey": "parentId",
            "nodes": [
                {"id": 1, "parentId": null, "name": "Chicken"},
                {"id": 2, "parentId": 1, "name": "Egg"}
            ]
        }';

        $actualJson = json_encode($tree);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $actualJson
        );
    }

    public function provideTreeData(): array
    {
        return [
            [
                ['id' => 1, 'parentId' => null, 'username' => 'God'],
                ['id' => 2, 'parentId' => 1, 'username' => 'Zeus'],
                ['id' => 3, 'parentId' => 1, 'username' => 'Jane'],
                ['id' => 4, 'parentId' => 3, 'username' => 'John'],
            ],
            [
                ['id' => 3, 'parentId' => 1, 'username' => 'Jane'],
                ['id' => 1, 'parentId' => null, 'username' => 'God'],
                ['id' => 4, 'parentId' => 3, 'username' => 'John'],
                ['id' => 2, 'parentId' => 1, 'username' => 'Zeus'],
            ],
            [
                ['id' => 4, 'parentId' => 3, 'username' => 'John'],
                ['id' => 2, 'parentId' => 1, 'username' => 'Zeus'],
                ['id' => 3, 'parentId' => 1, 'username' => 'Jane'],
                ['id' => 1, 'parentId' => null, 'username' => 'God'],
            ],
            [
                new Person(4, 3, 'John'),
                new Person(2, 1, 'Zeus'),
                new Person(3, 1, 'Jane'),
                new Person(1, null, 'God'),
            ],
        ];
    }
}