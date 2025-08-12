<?php
declare(strict_types=1);

class ModifierMapping {
    public function __construct(public string $modifierId, public string $parameterName)
    {
    }
}



/* PRINT FUNCTIONS */
/**
 * @param array $lineArrayWithKeys - array containing the data for one card (one line in the csv)
 * @return void
 */
function printResourceChanges(array $lineArrayWithKeys): void
{
    echo "\t" . "resourceChanges: new ResourceChanges(\n";
    if (!empty($lineArrayWithKeys["moneyChange"])) {
        //moneyChange can be positive or negative (in contrast to Jobs/MiniJobs)
        echo "\t\t" . "guthabenChange: new MoneyAmount(" . $lineArrayWithKeys["moneyChange"] . "),\n";
    }
    if (!empty($lineArrayWithKeys["zeitsteinChange"])) {
        echo "\t\t" . "zeitsteineChange: " . $lineArrayWithKeys["zeitsteinChange"] . ",\n";
    }
    if (!empty($lineArrayWithKeys["bildungUndKarriereChange"])) {
        echo "\t\t" . "bildungKompetenzsteinChange: +" . $lineArrayWithKeys["bildungUndKarriereChange"] . ",\n";
    }
    if (!empty($lineArrayWithKeys["sozialesUndFreizeitChange"])) {
        echo "\t\t" . "freizeitKompetenzsteinChange: +" . $lineArrayWithKeys["sozialesUndFreizeitChange"] . ",\n";
    }
    echo "\t),\n";
}

/**
 * @param string $phase
 * @param string $year
 * @return void
 */
function printPhaseAndYear(string $phase, string $year): void
{
    echo "\t" . "phaseId: LebenszielPhaseId::PHASE_" . $phase . ",\n";

    if (!empty($year)) {
        echo "\t" . "year: new Year(" . $year . "),\n";
    } else {
        echo "\t" . "year: new Year(3),\n";
    }
}


/* IMPORT FUNCTIONS */
function importMiniJobCards(): void
{
    echo "\n\n--MINIJOB CARDS--\n\n";
    $file = file(__DIR__ . "/Minijobs.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", $tableHeaderItems[0]), 0, 3); //three table header items

    foreach ($fileContent as $line) {
        $lineArray = explode(";", substr(trim($line), 0, -2));
        $lineArrayWithKeys = array_combine($keys, $lineArray);

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new MinijobCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: '" . $lineArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',\n";
        echo "\t" . "resourceChanges: new ResourceChanges(\n";
        echo "\t\t" . "guthabenChange: new MoneyAmount(+" . $lineArrayWithKeys["moneyChange"] . "),\n"; //always positive MoneyAmount Change
        echo "\t),\n),\n";
    }
}

function importJobCards(): void
{
    echo "\n\n--JOB CARDS--\n\n";
    $file = file(__DIR__ . "/Jobs.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 8); //eight table header items

    foreach ($fileContent as $line) {
        $lineArray = explode(";", trim($line));
        $lineArrayWithKeys = array_combine($keys, $lineArray);

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new JobCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: '" . $lineArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: '" . $lineArrayWithKeys["description"] . "',\n";
        printPhaseAndYear($lineArrayWithKeys["phase"], $lineArrayWithKeys["year"]);
        echo "\t" . "gehalt: new MoneyAmount(+" . $lineArrayWithKeys["gehalt"] . "),\n"; //always positive MoneyAmount change
        echo "\t" . "requirements: new JobRequirements(\n";
        echo "\t\t" . "zeitsteine: 1,\n";
        echo "\t\t" . "bildungKompetenzsteine: " . $lineArrayWithKeys["minBildungUndKarriere"] . ",\n";
        echo "\t\t" . "freizeitKompetenzsteine: " . $lineArrayWithKeys["minSozialesUndFreizeit"] . ",\n";
        echo "\t),\n),\n";
    }
}

function importWeiterbildungCards(): void
{
    echo "\n\n--WEITERBILDUNG CARDS--\n\n";
    $file = file(__DIR__ . "/Weiterbildungen.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 3); //first three table header items
    $answerIds = ["a", "b", "c", "d"];

    foreach ($fileContent as $line) {
        shuffle($answerIds); //first answer Id is used for the correct answer -> randomized through the shuffle
        $lineArray = explode(";", trim($line));
        $lineArrayWithoutWrongAnswers = array_slice($lineArray, 0, 3); //remove all wrong answers
        $lineArrayWithKeys = array_combine($keys, $lineArrayWithoutWrongAnswers);
        $wrongAnswersArray = array_filter(array_slice($lineArray, 3)); //array_filter removes empty entries as not all questions have all three wrong answers

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new WeiterbildungCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "description: '" . $lineArrayWithKeys["description"] . "',\n";
        echo "\t" . "answerOptions: [\n";
        echo "\t\t" . "new AnswerOption(new AnswerId(\"" . $answerIds[0] . "\"), \"" . $lineArrayWithKeys["correctAnswer"] . "\", true),\n";
        foreach($wrongAnswersArray as $key => $wrongAnswer) {
            echo "\t\t" . "new AnswerOption(new AnswerId(\"" . $answerIds[$key + 1] . "\"), \"" . $wrongAnswer . "\"),\n";
        }
        echo "\t],\n),\n";
    }
}

function importKategorieCards(): void
{
    echo "\n\n--KATEGORIE CARDS--\n\n";
    $file = file(__DIR__ . "/Kategorie_Karten.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0); //table header items

    foreach ($fileContent as $line) {
        $lineArray = explode(";", trim($line));
        $lineArrayWithKeys = array_combine($keys, $lineArray);

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new KategorieCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "categoryId: CategoryId::" . $lineArrayWithKeys["category"] . ",\n";
        echo "\t" . "title: '" . $lineArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: '" . $lineArrayWithKeys["description"] . "',\n";
        printPhaseAndYear($lineArrayWithKeys["phase"], $lineArrayWithKeys["year"]);
        printResourceChanges($lineArrayWithKeys);
        echo "),\n";
    }
}

function importEreignisCards(): void
{
    $modifierMappings = [
        "AUSSETZEN" => new ModifierMapping("AUSSETZEN", ""),
        "BERUFSUNFÄHIGKEITSVERSICHERUNG" => new ModifierMapping("BERUFSUNFAEHIGKEITSVERSICHERUNG", ""),
        "GEHALT" => new ModifierMapping("GEHALT_CHANGE", "modifyGehaltPercent"),
        "HAFTPFLICHTVERSICHERUNG" => new ModifierMapping("HAFTPFLICHTVERSICHERUNG", ""),
        "INVESTITIONSSPERRE" => new ModifierMapping("INVESTITIONSSPERRE", ""),
        "JOBVERLUST" => new ModifierMapping("JOBVERLUST", ""),
        "LEBENSHALTUNGSKOSTEN_MULTIPLIER" => new ModifierMapping("LEBENSHALTUNGSKOSTEN_MULTIPLIER", "modifyLebenshaltungskostenMultiplier"),
        "LEBENSHALTUNGS_MINIMUM" => new ModifierMapping("LEBENSHALTUNGSKOSTEN_MIN_VALUE", "modifyLebenshaltungskostenMinValue"),
        "PRIVATE_UNFALLVERSICHERUNG" => new ModifierMapping("PRIVATE_UNFALLVERSICHERUNG", ""),
    ];

    echo "\n\n--EREIGNIS CARDS--\n\n";
    $file = file(__DIR__ . "/Ereignisse.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 11); //11 first table header items
    $modifierKeys = array_slice(explode(";", trim($tableHeaderItems[0])), 11, 6); //6 modifier table header items
    $prerequisiteKeys = array_slice(explode(";", trim($tableHeaderItems[0])), 17, 3); //3 prerequisite table header items

    foreach ($fileContent as $line) {
        $lineArray = explode(";", trim($line));
        $lineArrayWithoutModifiersAndPrerequisites = array_slice($lineArray, 0, 11); //removes modifiers and prerequisites
        $lineArrayWithKeys = array_combine($keys, $lineArrayWithoutModifiersAndPrerequisites);
        $modifierArray = array_slice($lineArray, 11, 6);
        $modifierArrayWithKeys = array_combine($modifierKeys, $modifierArray);
        $prerequisiteArray = array_slice($lineArray, 17, 3);
        $prerequisiteArrayWithKeys = array_combine($prerequisiteKeys, $prerequisiteArray);

        //stores multiplier as key value pair (modifierId and modifierValue) as it simplifies the iteration over the elements
        $modifierArrayWithIdValuePairs = [];
        if (!empty($modifierArrayWithKeys["modifierId1"])) {
            $modifierArrayWithIdValuePairs[$modifierArrayWithKeys["modifierId1"]] = $modifierArrayWithKeys["modifierValue1percentage"];
        }
        if (!empty($modifierArrayWithKeys["modifierId2"])) {
            $modifierArrayWithIdValuePairs[$modifierArrayWithKeys["modifierId2"]] = $modifierArrayWithKeys["modifierValue2"];
        }
        if (!empty($modifierArrayWithKeys["modifierId3"])) {
            $modifierArrayWithIdValuePairs[$modifierArrayWithKeys["modifierId3"]] = $modifierArrayWithKeys["modifierValue3"];
        }

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new EreignisCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "categoryId: CategoryId::EREIGNIS_" . $lineArrayWithKeys["category"] . ",\n";
        echo "\t" . "title: '" . $lineArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: '" . $lineArrayWithKeys["description"] . "',\n";
        printPhaseAndYear($lineArrayWithKeys["phase"], $lineArrayWithKeys["year"]);
        printResourceChanges($lineArrayWithKeys);
        echo "\t" . "modifierIds: [\n";
        foreach ($modifierArrayWithIdValuePairs as $modifierId => $modifierValue) {
            echo "\t\t" . "ModifierId::" . $modifierMappings[$modifierId]->modifierId . ",\n";
        }
        echo "\t" . "],\n";
        echo "\t" . "modifierParameters: new ModifierParameters(\n";
        foreach ($modifierArrayWithIdValuePairs as $modifierId => $modifierValue) {
            if (!empty($modifierValue) || $modifierValue==="0") { //some modifiers don't have a value/modifierParameter
                if ($modifierId === "LEBENSHALTUNGS_MINIMUM") {
                    echo "\t\t" . $modifierMappings[$modifierId]->parameterName . ": new MoneyAmount(" . $modifierValue . "),\n";
                } else {
                    echo "\t\t" . $modifierMappings[$modifierId]->parameterName . ":" . $modifierValue . ",\n";
                }
            }
        }
        echo "\t" . "),\n";

        //TODO prerequisiteCardId -> add?!

        echo "\t" . "ereignisRequirementIds: [\n";
        for ($i = 1; $i<=2; $i++) {
            if (!empty($prerequisiteArray[$i])) {
                echo "\t\t" . "EreignisPrerequisitesId::" . $prerequisiteArray[$i] . ",\n";
            }
        }
        echo "\t],\n),\n";
    }
}

function importInvestitionenCards(): void
{
    echo "\n\n--INVESTITIONEN CARDS--\n\n";
    $file = file(__DIR__ . "/Investitionen_Immobilien.csv");
    $fileContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 8); //eight table header items

    foreach ($fileContent as $line) {
        $lineArray = explode(";", trim($line));
        $lineArrayWithKeys = array_combine($keys, $lineArray);

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new InvestitionenCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: '" . $lineArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: '" . $lineArrayWithKeys["description"] . "',\n";
        echo "\t" . "phaseId: LebenszielPhaseId::PHASE_" . $lineArrayWithKeys["phase"] . ",\n";
        printResourceChanges($lineArrayWithKeys);
        echo "\t" . "annualRent: new MoneyAmount(" . $lineArrayWithKeys["annualRent"] . "),\n";
        echo "\t" . "immobilienTyp: ImmobilienType::" . $lineArrayWithKeys["type"] . ",\n";
        echo "),\n";
    }
}


//importMiniJobCards();
//importJobCards();
//importWeiterbildungCards();
//importKategorieCards();
//importEreignisCards();
importInvestitionenCards();





