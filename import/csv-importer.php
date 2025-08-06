<?php
declare(strict_types=1);

function importMiniJobCards() {
    echo "\n\n--MINIJOB CARDS--\n\n";
    $file = file(__DIR__ . "/Minijobs-Table 1.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", $tableHeaderItems[0]), 0, 3); //three table header items

    foreach ($fileContent as $item) {
        $itemArray = explode(";", substr(trim($item), 0, -2));
        $itemArrayWithKeys = array_combine($keys, $itemArray);

        echo "\"" . $itemArrayWithKeys["id"] . "\" => new MinijobCardDefinition(\n";
        echo "\tid: new CardId('" . $itemArrayWithKeys["id"] . "'),\n";
        echo "\ttitle: '" . $itemArrayWithKeys["title"] . "',\n";
        echo "\tdescription: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',\n";
        echo "\tresourceChanges: new ResourceChanges(\n";
        echo "\t\tguthabenChange: new MoneyAmount(+" . $itemArrayWithKeys["moneyChange"] . "),\n";
        echo "\t),\n),\n";
    }
}

importMiniJobCards();







