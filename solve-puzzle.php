<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file path
$logFile = 'logfile.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Clear the log file at the beginning of each request
file_put_contents($logFile, '');

// Log the request headers and body
logMessage("Request headers: " . print_r(getallheaders(), true));
logMessage("Request body: " . file_get_contents('php://input'));

class CandySortSolver {
    private $tubes;
    private $tubeCapacity;
    private $moves = [];

    public function __construct(array $initialTubes, int $tubeCapacity = 4) {
        $this->tubes = $initialTubes;
        $this->tubeCapacity = $tubeCapacity;
        logMessage("Solver initialized with tubes: " . print_r($this->tubes, true));
    }

    public function solve(): ?array {
        logMessage("Starting solve method.");
        if ($this->isSolved()) {
            logMessage("Puzzle is already solved.");
            return $this->moves;
        }

        for ($fromTube = 0; $fromTube < count($this->tubes); $fromTube++) {
            if (empty($this->tubes[$fromTube])) {
                logMessage("Tube $fromTube is empty, skipping.");
                continue;
            }

            for ($toTube = 0; $toTube < count($this->tubes); $toTube++) {
                if ($fromTube === $toTube) {
                    logMessage("From tube and to tube are the same ($fromTube), skipping.");
                    continue;
                }

                if ($this->isValidMove($fromTube, $toTube)) {
                    logMessage("Valid move found: from tube $fromTube to tube $toTube.");
                    // Make the move
                    $candy = array_pop($this->tubes[$fromTube]);
                    array_push($this->tubes[$toTube], $candy);
                    $this->moves[] = ["from" => $fromTube, "to" => $toTube];

                    // Log the move
                    logMessage("Move made: from tube $fromTube to tube $toTube");
                    logMessage("Tubes state after move: " . print_r($this->tubes, true));

                    // Try to solve from this new state
                    $solution = $this->solve();
                    if ($solution !== null) {
                        logMessage("Solution found.");
                        return $solution;
                    }

                    // Undo the move if it didn't lead to a solution
                    $candy = array_pop($this->tubes[$toTube]);
                    array_push($this->tubes[$fromTube], $candy);
                    array_pop($this->moves);

                    // Log the undo move
                    logMessage("Move undone: from tube $toTube to tube $fromTube");
                    logMessage("Tubes state after undo: " . print_r($this->tubes, true));
                } else {
                    logMessage("Invalid move: from tube $fromTube to tube $toTube.");
                }
            }
        }

        logMessage("No valid moves found.");
        return null;
    }

    private function isValidMove(int $fromTube, int $toTube): bool {
        logMessage("Checking if move is valid: from tube $fromTube to tube $toTube.");
        if (empty($this->tubes[$fromTube])) {
            logMessage("From tube $fromTube is empty.");
            return false;
        }

        if (count($this->tubes[$toTube]) >= $this->tubeCapacity) {
            logMessage("To tube $toTube is full.");
            return false;
        }

        $candy = end($this->tubes[$fromTube]);

        if (empty($this->tubes[$toTube])) {
            if ($this->isCompleteTube($fromTube)) {
                logMessage("From tube $fromTube is complete.");
                return false;
            }
            return true;
        }

        return end($this->tubes[$toTube]) === $candy;
    }

    private function isSolved(): bool {
        logMessage("Checking if puzzle is solved.");
        foreach ($this->tubes as $tube) {
            if (empty($tube)) {
                continue;
            }
            if (!$this->isCompleteTube(array_search($tube, $this->tubes))) {
                logMessage("Tube is not complete: " . print_r($tube, true));
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

// Log the decoded input
logMessage("Decoded input: " . print_r($data, true));

// Create solver and get solution
$solver = new CandySortSolver($tubes);
$solution = $solver->solve();

// Log the solution
logMessage("Solution: " . print_r($solution, true));

// Return response
echo json_encode([
    'success' => $solution !== null,
    'moves' => $solution
]);
?>
