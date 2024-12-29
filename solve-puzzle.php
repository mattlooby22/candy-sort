<?php
header('Content-Type: application/json');

class CandySortSolver {
    private $tubes;
    private $tubeCapacity;
    private $moves = [];

    public function __construct(array $initialTubes, int $tubeCapacity = 4) {
        $this->tubes = $initialTubes;
        $this->tubeCapacity = $tubeCapacity;
    }

    public function solve(): ?array {
        if ($this->isSolved()) {
            return $this->moves;
        }

        for ($fromTube = 0; $fromTube < count($this->tubes); $fromTube++) {
            if (empty($this->tubes[$fromTube])) {
                continue;
            }

            for ($toTube = 0; $toTube < count($this->tubes); $toTube++) {
                if ($fromTube === $toTube) {
                    continue;
                }

                if ($this->isValidMove($fromTube, $toTube)) {
                    // Make the move
                    $candy = array_pop($this->tubes[$fromTube]);
                    array_push($this->tubes[$toTube], $candy);
                    $this->moves[] = ["from" => $fromTube, "to" => $toTube];

                    // Try to solve from this new state
                    $solution = $this->solve();
                    if ($solution !== null) {
                        return $solution;
                    }

                    // Undo the move if it didn't lead to a solution
                    $candy = array_pop($this->tubes[$toTube]);
                    array_push($this->tubes[$fromTube], $candy);
                    array_pop($this->moves);
                }
            }
        }

        return null;
    }

    private function isValidMove(int $fromTube, int $toTube): bool {
        if (empty($this->tubes[$fromTube])) {
            return false;
        }

        if (count($this->tubes[$toTube]) >= $this->tubeCapacity) {
            return false;
        }

        $candy = end($this->tubes[$fromTube]);

        if (empty($this->tubes[$toTube])) {
            if ($this->isCompleteTube($fromTube)) {
                return false;
            }
            return true;
        }

        return end($this->tubes[$toTube]) === $candy;
    }

    private function isSolved(): bool {
        foreach ($this->tubes as $tube) {
            if (empty($tube)) {
                continue;
            }
            if (!$this->isCompleteTube(array_search($tube, $this->tubes))) {
                return false;
            }
        }
        return true;
    }

    private function isCompleteTube(int $tubeIndex): bool {
        $tube = $this->tubes[$tubeIndex];
        if (empty($tube)) {
            return true;
        }
        $firstCandy = $tube[0];
        foreach ($tube as $candy) {
            if ($candy !== $firstCandy) {
                return false;
            }
        }
        return true;
    }
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$tubes = $data['tubes'] ?? [];

// Create solver and get solution
$solver = new CandySortSolver($tubes);
$solution = $solver->solve();

// Return response
echo json_encode([
    'success' => $solution !== null,
    'moves' => $solution
]);
?>
