<?php
$gameInstructions = <<<GAME
A Y
B X
C Z
GAME;

class Processor
{
    public function __construct(public Game $game)
    {
    }

    public static function fromPayload(string $gameInstructionsAsText): self
    {
        $game = explode("\n", $gameInstructionsAsText);
        $rounds = [];
        foreach($game as $round) {
            if (!empty($round)) {
                $roundStrategy = explode(" ", $round);
                $rounds[] = new Round(new PlayerOne($roundStrategy[0]), new Result($roundStrategy[1]));
            }
        }

        return new self(new Game($rounds));
    }
}

class Game
{
    /**
     * @param array<Round> $rounds
     */
    public function __construct(private array $rounds)
    {
    }

    public function totalScore(): int
    {
        $scores = array_map(fn(Round $round) => $round->scoreCalculation(), $this->rounds);

        return array_sum($scores);
    }
}

class Round
{
    private Me $me;
    private array $drawMovements = [
        PlayerOne::SCISSORS => Me::SCISSORS,
        PlayerOne::PAPER => Me::PAPER,
        PlayerOne::ROCK => Me::ROCK,
    ];
    private array $winMovementsForMe = [
        PlayerOne::SCISSORS => Me::ROCK,
        PlayerOne::PAPER => Me::SCISSORS,
        PlayerOne::ROCK => Me::PAPER,
    ];
    private array $lostMovementsForMe = [
        PlayerOne::ROCK => Me::SCISSORS,
        PlayerOne::SCISSORS => Me::PAPER,
        PlayerOne::PAPER => Me::ROCK,
    ];

    public function __construct(private PlayerOne $playerOne, private Result $result)
    {
        $myMovement = match(true) {
            $result->isDraw() => $this->drawMovements[$this->playerOne->movement],
            $result->isLost() => $this->lostMovementsForMe[$this->playerOne->movement],
            $result->isWin() => $this->winMovementsForMe[$this->playerOne->movement],
        };
        $this->me = new Me($myMovement);
    }

    public function scoreCalculation(): int
    {
        $movementScore = match($this->me->movement) {
            Me::ROCK => Scores::ROCK,
            Me::PAPER => Scores::PAPER,
            Me::SCISSORS => Scores::SCISSORS,
        };

        return $movementScore + $this->result->toScore();
    }
}

class Scores
{
    const ROCK = 1;
    const PAPER = 2;
    const SCISSORS = 3;

    const LOST = 0;
    const DRAW = 3;
    const WON = 6;
}

class Result
{
    const LOSE = 'X';
    const DRAW = 'Y';
    const WIN = 'Z';

    public function __construct(private string $result)
    {
    }

    public function toScore()
    {
        return match($this->result) {
            self::LOSE => Scores::LOST,
            self::DRAW => Scores::DRAW,
            self::WIN => Scores::WON,
        };
    }

    public function isDraw()
    {
        return $this->result === self::DRAW;
    }

    public function isLost()
    {
        return $this->result === self::LOSE;
    }

    public function isWin()
    {
        return $this->result === self::WIN;
    }
}

abstract class Player
{
    public function __construct(public string $movement)
    {
    }

    abstract protected static function rock(): string;
    abstract protected static function paper(): string;
    abstract protected static function scissors(): string;

    public function movementInNaturalLanguage()
    {
        return match($this->movement) {
            self::rock() => 'rock',
            self::paper() => 'paper',
            self::scissors() => 'scissors',
        };
    }
}

class PlayerOne extends Player
{
    const ROCK = 'A';
    const PAPER = 'B';
    const SCISSORS = 'C';

    protected static function rock(): string
    {
        return self::ROCK;
    }

    protected static function paper(): string
    {
        return self::PAPER;
    }

    protected static function scissors(): string
    {
        return self::SCISSORS;
    }
}

class Me extends Player
{
    const ROCK = 'X';
    const PAPER = 'Y';
    const SCISSORS = 'Z';

    protected static function rock(): string
    {
        return self::ROCK;
    }

    protected static function paper(): string
    {
        return self::PAPER;
    }

    protected static function scissors(): string
    {
        return self::SCISSORS;
    }
}

$gameInstructions = file_get_contents('day-02-input-01.txt');
echo Processor::fromPayload($gameInstructions)->game->totalScore();


// Dead Code
class RoundPhaseOne
{
    public function __construct(private PlayerOne $playerOne, private Me $me)
    {
    }

    public function scoreCalculation(): int
    {
        $movementScore = match($this->me->movement) {
            Me::ROCK => Scores::ROCK,
            Me::PAPER => Scores::PAPER,
            Me::SCISSORS => Scores::SCISSORS,
        };

        return $movementScore + $this->calculateResultPoints();
    }

    private function calculateResultPoints()
    {
        $winMovements = [
            Me::ROCK => PlayerOne::SCISSORS,
            Me::SCISSORS => PlayerOne::PAPER,
            Me::PAPER => PlayerOne::ROCK,
        ];

        if ($this->playerOne->movementInNaturalLanguage() === $this->me->movementInNaturalLanguage()) {
            return Scores::DRAW;
        }

        if ($winMovements[$this->me->movement] === $this->playerOne->movement) {
            return Scores::WON;
        }

        return Scores::LOST;
    }
}