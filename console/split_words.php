<?php


if ($argc !== 4) {
    echo "Please pass correct arguments. " . PHP_EOL;
    echo "Usage: php [path]/" . $argv[0] . " source_path output_path output_file_type_extension ...\n" . PHP_EOL;
    exit(1);
}

$script_path = __DIR__ . $argv[0] . PHP_EOL;
$file_path = $argv[1];
$outputFileFolder = $argv[2];
$outputFileExt = $argv[3];
$allowed_output_file_extensions = ["txt", "json"];


try {
    echo "******** Running Script : " . $script_path;
    if (!in_array($outputFileExt, $allowed_output_file_extensions)) {
        throw new Exception("Output file extension type is invalid.");
    }
    splitByLetter(extractWords($file_path), $outputFileFolder, $outputFileExt);
    echo "Script Ran Successfully ********" . PHP_EOL;
} catch (\Throwable $th) {
    echo "Error Running Script : " . $script_path;
    echo "Error Message: " . $th->getMessage() . PHP_EOL;
}

function extractWords(string $file_path): array
{

    $allWords = [];

    if (pathinfo($file_path, PATHINFO_EXTENSION) === "json") {
        $allWords = array_keys((array) json_decode(file_get_contents($file_path)));
    } else {
        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $allWords = array_map('trim', $lines);
    }

    $filtered_words = [];

    foreach ($allWords as $word) {
        if (str_contains($word, "-") || str_contains($word, ".") || str_contains($word, "'") || str_contains($word, "/") || strlen($word) != 5) {
            continue;
        }
        array_push($filtered_words, strtolower($word));
    }
    sort($filtered_words, SORT_STRING);
    return $filtered_words;
}

function splitByLetter(array $words, string $outputFileFolder, string $extension): void
{
    $splitByLetterArray = array();
    $previousLetter = 'a';
    foreach ($words as $word) {
        $currentLetter = $word[0];
        if ($previousLetter !== $currentLetter) {
            writeToFile($splitByLetterArray[$previousLetter], $previousLetter, $outputFileFolder, $extension);
            $previousLetter = $currentLetter;
            unset($splitByLetterArray[$previousLetter]);
        }
        if (!array_key_exists($currentLetter, $splitByLetterArray)) {
            $splitByLetterArray[$currentLetter] = [];
        }
        $splitByLetterArray[$currentLetter][] = $word;
    }
    writeToFile($splitByLetterArray[$previousLetter], $previousLetter, $outputFileFolder, $extension);
}

function writeToFile(array $words, string $letter, string $outputFileFolder, string $extension): void
{
    $outputFilePath = $outputFileFolder . '/' . strtolower($letter) . "." . $extension;
    echo "Wrting " . $letter . " alphabet words to file : " . $outputFilePath . PHP_EOL;
    if ($extension === "json") {
        file_put_contents($outputFilePath, json_encode($words));
    } else {
        file_put_contents($outputFilePath, implode(PHP_EOL, $words) . PHP_EOL);
    }
}
