<?php
declare(strict_types=1);

function importMiniJobCards() {
    echo "\n\n--MINIJOB CARDS--\n\n";
    $file = file(__DIR__ . "/Minijobs-Table 1.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", $tableHeaderItems[0]), 0, 3); //three table header items

    foreach ($fileContent as $line) {
        $itemArray = explode(";", substr(trim($line), 0, -2));
        $itemArrayWithKeys = array_combine($keys, $itemArray);

        echo "\"" . $itemArrayWithKeys["id"] . "\" => new MinijobCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $itemArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: '" . $itemArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',\n";
        echo "\t" . "resourceChanges: new ResourceChanges(\n";
        echo "\t\t" . "guthabenChange: new MoneyAmount(+" . $itemArrayWithKeys["moneyChange"] . "),\n";
        echo "\t),\n),\n";
    }
}

function importJobCards() {
    echo "\n\n--JOB CARDS--\n\n";
    $file = file(__DIR__ . "/Jobs-Table 1.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 8); //eight table header items

    foreach ($fileContent as $line) {
        $itemArray = explode(";", trim($line));
        $itemArrayWithKeys = array_combine($keys, $itemArray);

        echo "\"" . $itemArrayWithKeys["id"] . "\" => new JobCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $itemArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: '" . $itemArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: '" . $itemArrayWithKeys["description"] . "',\n";
        echo "\t" . "phaseId: LebenszielPhaseId::PHASE_" . $itemArrayWithKeys["phase"] . ",\n";
        echo "\t" . "year: new Year(" . $itemArrayWithKeys["year"] . "),\n";
        echo "\t" . "gehalt: new MoneyAmount(+" . $itemArrayWithKeys["gehalt"] . "),\n";
        echo "\t" . "requirements: new JobRequirements(\n";
        echo "\t\t" . "zeitsteine: 1,\n";
        echo "\t\t" . "bildungKompetenzsteine: " . $itemArrayWithKeys["minBildungUndKarriere"] . ",\n";
        echo "\t\t" . "freizeitKompetenzsteine: " . $itemArrayWithKeys["minSozialesUndFreizeit"] . ",\n";
        echo "\t),\n),\n";
    }
}

function importWeiterbildungCards() {
    echo "\n\n--WEITERBILDUNG CARDS--\n\n";
    $file = file(__DIR__ . "/Weiterbildung-Table 1.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 3); //first three table header items
    $answerIds = ["a", "b", "c", "d"];

    foreach ($fileContent as $line) {
        //first answer Id is used for the correct answer -> randomized through the shuffle
        shuffle($answerIds);
        $itemArray = explode(";", trim($line));
        $itemArrayWithoutWrongAnswers = array_slice($itemArray, 0, 3); //remove all wrong answers
        $itemArrayWithKeys = array_combine($keys, $itemArrayWithoutWrongAnswers);
        //array_filter removes empty entries as not all questions have all three wrong answers
        $wrongAnswersArray = array_filter(array_slice($itemArray, 3));
        $itemArrayWithKeys["wrongAnswers"] = $wrongAnswersArray; //add wrong answers stored in an array

        echo "\"" . $itemArrayWithKeys["id"] . "\" => new WeiterbildungCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $itemArrayWithKeys["id"] . "'),\n";
        echo "\t" . "description: '" . $itemArrayWithKeys["description"] . "',\n";
        echo "\t" . "answerOptions: [\n";
        echo "\t\t" . "new AnswerOption(new AnswerId(\"" . $answerIds[0] . "\"), \"" . $itemArrayWithKeys["correctAnswer"] . "\", true),\n";

        foreach($itemArrayWithKeys["wrongAnswers"] as $key => $wrongAnswer) {
            echo "\t\t" . "new AnswerOption(new AnswerId(\"" . $answerIds[$key + 1] . "\"), \"" . $wrongAnswer . "\"),\n";
        }
        echo "\t],\n),\n";
    }
}


//importMiniJobCards();
//importJobCards();
importWeiterbildungCards();







