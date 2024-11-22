<?php
namespace Tests\Unit\Topic;

use Coyote\Feature\TreeTopic\ArrayLinkedSorter;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArrayLinearTreeTest extends TestCase
{
    private ArrayLinkedSorter $sorter;

    #[Before]
    public function initialize(): void
    {
        $this->sorter = new ArrayLinkedSorter();
    }

    #[Test]
    public function emptyArray(): void
    {
        $this->assertSame([], $this->sorter->sort([], '', ''));
    }

    #[Test]
    public function singleEntry(): void
    {
        $this->assertSame(
            [$this->entry()],
            $this->sorter->sort([$this->entry()], '', ''));
    }

    #[Test]
    public function moveItemUp_closerToItsParent(): void
    {
        $input = [
            ['id' => 4, 'field' => 'blue', 'parent_id' => null],
            ['id' => 5, 'field' => 'red', 'parent_id' => null],
            ['id' => 6, 'field' => 'green', 'parent_id' => 4],
        ];
        $output = $this->sorter->sort($input, 'id', 'parent_id');
        $this->assertSame(
            [
                ['id' => 4, 'field' => 'blue', 'parent_id' => null],
                ['id' => 6, 'field' => 'green', 'parent_id' => 4],
                ['id' => 5, 'field' => 'red', 'parent_id' => null],
            ],
            $output);
    }

    #[Test]
    public function joinTwoElements(): void
    {
        $input = [
            ['id' => 4, 'field' => 'one', 'parent_id' => null],
            ['id' => 5, 'field' => 'two1', 'parent_id' => 4],
            ['id' => 6, 'field' => 'three', 'parent_id' => 5],
            ['id' => 7, 'field' => 'two2', 'parent_id' => 4],
        ];
        $output = $this->sorter->sort($input, 'id', 'parent_id');
        $this->assertSame(
            ['one', 'two1', 'three', 'two2'],
            \array_column($output, 'field'));
    }

    private function entry(): array
    {
        return ['field' => 'value'];
    }
}
