<?php


$words = array_keys((array) json_decode(file_get_contents("words_dictionary.json")));

$wordSplitCollection = array();


for ($i = 65; $i <= 90; $i++) {
    foreach ($words as $word) {
        if ($word[0] === strtolower(chr($i))) {
            $wordSplitCollection[chr($i)][] = $word;
        }
    }
}

foreach ($wordSplitCollection as $key => $collection) {
    file_put_contents('words_collection/' . strtolower($key) . '.json', json_encode($collection));
}
