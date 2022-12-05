<?php

$inventory = <<<INVENTORY
1000
2000
3000

4000

5000
6000

7000
8000
9000

10000
INVENTORY;

function assertEquals($expected, $actual)
{
    if ($expected!==$actual) {
        throw new \Exception('The actual value ' . $actual . ' is not equal to the expected value ' . $expected);
    }
}

class Processor
{
    private function __construct(public Expedition $expedition)
    {
    }

    public static function fromPayload(string $payload): Processor
    {
	$elves = [];
	$inventoryString = explode("\n\n", $payload);
	foreach($inventoryString as $elfInventoryString) {
	    $elves[] = new Elf(explode("\n", $elfInventoryString));
	}

	return new Processor(new Expedition($elves)); 
    }
}

class Elf
{
    public function __construct(private array $selfInventory)
    {
    }

    public function totalCalories(): int
    {
	return array_sum($this->selfInventory);
    }
}

class Expedition
{
    public function __construct(private array $elves)
    {
    }

    public function topThreeElvesCalories(): int
    {
	$elvesCalories = array_map(fn($elf) => $elf->totalCalories(), $this->elves);
	arsort($elvesCalories, SORT_NATURAL);

	return array_sum(array_chunk($elvesCalories, 3)[0]);	
    }
}

$inventory = file_get_contents('day-01-input-01.txt');
echo Processor::fromPayload($inventory)->expedition->topThreeElvesCalories();

