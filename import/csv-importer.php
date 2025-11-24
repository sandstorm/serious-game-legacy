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

/**
 * @param string $type
 * @param string $maxKompetenzsteine
 * @return void
 */
function printKompetenzbereichDefinition(string $type, string $maxKompetenzsteine):void
{
    echo "\t\t" . "new KompetenzbereichDefinition(\n";
    echo "\t\t\t" . "name: CategoryId::" . $type . ",\n";
    echo "\t\t\t" . "zeitslots: new Zeitslots([\n";
    echo "\t\t\t\t" . "new ZeitslotsPerPlayer(2, " . $maxKompetenzsteine-1 . "),\n"; //for two players the max Kompetenzsteine are reduced by 1
    echo "\t\t\t\t" . "new ZeitslotsPerPlayer(3, " . $maxKompetenzsteine . "),\n";
    echo "\t\t\t\t" . "new ZeitslotsPerPlayer(4, " . $maxKompetenzsteine . "),\n";
    echo "\t\t\t" . "])\n";
    echo "\t\t" . "),\n";
}

/**
 * @param string $prerequisites
 * @param string $resourceChange
 * @param string $value
 * @return void
 */
function printConditionalResourceChanges(string $prerequisites, string $resourceChange, string $value):void
{
    echo "\t\t" . "new ConditionalResourceChange(\n";
    echo "\t\t\t" . "prerequisite: EreignisPrerequisitesId::" . $prerequisites . ",\n";
    if ($resourceChange === "Lohnsonderzahlung") {
        echo "\t\t\t" . "resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(0)),\n";
        echo "\t\t\t" . "lohnsonderzahlungPercent: " . $value . ",\n";
    } elseif ($resourceChange === "Extrazins") {
        echo "\t\t\t" . "resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(" . $value . ")),\n";
        echo "\t\t\t" . "isExtraZins: true,\n";
    } elseif ($resourceChange === "Grundsteuer") {
        echo "\t\t\t" . "resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(" . $value . ")),\n";
        echo "\t\t\t" . "isGrundsteuer: true,\n";
    } elseif ($resourceChange === "guthabenChange") {
        echo "\t\t\t" . "resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(" . $value . ")),\n";
    } else {
        echo "\t\t\t" . "resourceChanges: new ResourceChanges(" . $resourceChange . ": " . $value . "),\n";
    }
    echo "\t\t" . "),\n";
}

/**
 * @param string $type
 * @param string $modifierValue
 * @return void
 */
function printAuswirkungen(string $type, string $modifierValue):void
{
    echo "\t\t" . "new AuswirkungDefinition(\n";
    echo "\t\t\t" . "scope: AuswirkungScopeEnum::" . $type . ",\n";
    echo "\t\t\t" . "value: " . $modifierValue . "\n";
    echo "\t\t" . "),\n";
}

/**
 * @param array $modifierArrayWithIdValuePairs
 * @return void
 */
function printModifiers(array $modifierArrayWithIdValuePairs): void
{
    $modifierMappings = [
        "AUSSETZEN" => new ModifierMapping("AUSSETZEN", ""),
        "BERUFSUNFÄHIGKEITSVERSICHERUNG" => new ModifierMapping("BERUFSUNFAEHIGKEITSVERSICHERUNG", ""),
        "GEHALT" => new ModifierMapping("GEHALT_CHANGE", "modifyGehaltPercent"),
        "HAFTPFLICHTVERSICHERUNG" => new ModifierMapping("HAFTPFLICHTVERSICHERUNG", ""),
        "INVESTITIONSSPERRE" => new ModifierMapping("INVESTITIONSSPERRE", ""),
        "JOBVERLUST" => new ModifierMapping("JOBVERLUST", ""),
        "LEBENSHALTUNGSKOSTEN_MULTIPLIER" => new ModifierMapping("LEBENSHALTUNGSKOSTEN_KIND_INCREASE", "modifyAdditionalLebenshaltungskostenPercentage"),
        "LEBENSHALTUNGS_MINIMUM" => new ModifierMapping("LEBENSHALTUNGSKOSTEN_MIN_VALUE", "modifyLebenshaltungskostenMinValue"),
        "PRIVATE_UNFALLVERSICHERUNG" => new ModifierMapping("PRIVATE_UNFALLVERSICHERUNG", ""),
        "BildungKarriereKosten" => new ModifierMapping("BILDUNG_UND_KARRIERE_COST", "modifyKostenBildungUndKarrierePercent"),
        "FreizeitSozialesKosten" => new ModifierMapping("SOZIALES_UND_FREIZEIT_COST", "modifyKostenSozialesUndFreizeitPercent"),
        "Lebenshaltungskosten" => new ModifierMapping("LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER", "modifyLebenshaltungskostenMultiplier"),
        "KREDITSPERRE" => new ModifierMapping("KREDITSPERRE", ""),
        "INCREASED_CHANCE_FOR_REZESSION" => new ModifierMapping("INCREASED_CHANCE_FOR_REZESSION", ""),
    ];
    echo "\t" . "modifierIds: [\n";
    foreach ($modifierArrayWithIdValuePairs as $modifierId => $modifierValue) {
        echo "\t\t" . "ModifierId::" . $modifierMappings[$modifierId]->modifierId . ",\n";
    }
    echo "\t" . "],\n";
    echo "\t" . "modifierParameters: new ModifierParameters(\n";
    foreach ($modifierArrayWithIdValuePairs as $modifierId => $modifierValue) {
        if ($modifierValue !== "") { //some modifiers don't have a value/modifierParameter
            if ($modifierId === "LEBENSHALTUNGS_MINIMUM") {
                echo "\t\t" . $modifierMappings[$modifierId]->parameterName . ": new MoneyAmount(" . $modifierValue . "),\n";
            } else {
                echo "\t\t" . $modifierMappings[$modifierId]->parameterName . ":" . $modifierValue . ",\n";
            }
        }
    }
    echo "\t" . "),\n";
}


/* IMPORT FUNCTIONS */

/**
 * Function imports MiniJobCards from csv file and echoes them in the console.
 * @return void
 */
function importMiniJobCards(): void
{
    $file = file(__DIR__ . "/Minijobs.csv");
    $tableContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", $tableHeaderItems[0]), 0, 3); //three table header items

    foreach ($tableContent as $line) {
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

/**
 * Function imports JobCards from csv file and echoes them in the console.
 * @return void
 */
function importJobCards(): void
{
    $file = file(__DIR__ . "/Jobs.csv");
    $tableContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 8); //eight table header items

    foreach ($tableContent as $line) {
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

/**
 * Function imports WeiterbildungsCards from csv file and echoes them in the console.
 * @return void
 */
function importWeiterbildungCards(): void
{
    $file = file(__DIR__ . "/Weiterbildungen.csv");
    $tableContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 3); //first three table header items
    $answerIds = ["a", "b", "c", "d"];

    foreach ($tableContent as $line) {
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

/**
 * Function imports KategorieCards from csv file and echoes them in the console.
 * @return void
 */
function importKategorieCards(): void
{
    $file = file(__DIR__ . "/Kategorie_Karten.csv");
    $tableContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0); //table header items

    foreach ($tableContent as $line) {
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

/**
 * Function imports EreignisCards from csv file and echoes them in the console.
 * @return void
 */
function importEreignisCards(): void
{
    $file = file(__DIR__ . "/Ereignisse.csv");
    $tableContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $tableHeaderArray = explode(";", trim($tableHeaderItems[0]));
    $keys = [];
    foreach ($tableHeaderArray as $key) {
        $keys[] = str_replace(["\"", " "], "", trim($key)); //removes spaces and " from table headers
    }
    $propertyKeys = array_slice($keys, 0, 11); //11 first table header items
    $modifierKeys = array_slice($keys, 11, 6); //6 modifier table header items
    $prerequisiteKeys = array_slice($keys, 17, 3); //3 prerequisite table header items

    foreach ($tableContent as $line) {
        $lineArray = explode(";", trim($line));
        $lineArrayWithoutModifiersAndPrerequisites = array_slice($lineArray, 0, 11); //removes modifiers and prerequisites
        $lineArrayWithKeys = array_combine($propertyKeys, $lineArrayWithoutModifiersAndPrerequisites);
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
        printModifiers($modifierArrayWithIdValuePairs);
        echo "\t" . "ereignisRequirementIds: [\n";
        for ($i = 1; $i<=2; $i++) {
            if (!empty($prerequisiteArray[$i])) {
                echo "\t\t" . "EreignisPrerequisitesId::" . $prerequisiteArray[$i] . ",\n";
            }
        }
        //all cards that have a requiredCardId need the Prerequisite HAS_SPECIFIC_CARD for validation
        if ($prerequisiteArrayWithKeys["prerequisiteCardId"] !== "") {
            echo "\t\t" . "EreignisPrerequisitesId::HAS_SPECIFIC_CARD,\n";
        }
        echo "\t" . "],\n";
        if ($prerequisiteArrayWithKeys["prerequisiteCardId"] !== "") {
            echo "\t" . "requiredCardId: new CardId('" . $prerequisiteArrayWithKeys["prerequisiteCardId"] . "'),\n";
        }
        if ($lineArrayWithKeys["Gewichtung"] === "") {
            echo "\t" . "gewichtung: 1,\n";
        } else {
            echo "\t" . "gewichtung: " . $lineArrayWithKeys["Gewichtung"] . ",\n";
        }
        echo "),\n";
    }
}

/**
 * Function imports ImmobilienCards from csv file and echoes them in the console.
 * @return void
 */
function importImmobilienCards(): void
{
    $file = file(__DIR__ . "/Investitionen_Immobilien.csv");
    $tableContent = array_slice($file, 2); //removes the first two elements (table name and table header)
    $tableHeaderItems = array_slice($file, 1, 1); //array element containing the table headers
    $keys = array_slice(explode(";", trim($tableHeaderItems[0])), 0, 7); //seven table header items

    foreach ($tableContent as $line) {
        $lineArray = array_slice(explode(";", trim($line)), 0, 7); //array slice to remove empty "cells" at the end
        $lineArrayWithKeys = array_combine($keys, $lineArray);

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new ImmobilienCardDefinition(\n";
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

/**
 * Function imports Konjunkturphasen from csv file and echoes them in the console.
 * @return void
 */
function importKonjunkturphasen(): void
{
    $file = file(__DIR__ . "/Konjunkturen.csv");
    $tableContent = array_slice($file, 2); //removes the first two element (table header)
    $tableHeader = trim(array_slice($file, 1, 1)[0]); //table header containing keys
    $tableHeaderArray = explode(";", $tableHeader); //array containing the table headers
    $keys = [];
    foreach ($tableHeaderArray as $key) {
        $keys[] = str_replace(["\"", " "], "", trim($key)); //removes spaces and " from table headers
    }

    foreach ($tableContent as $line) {
        $lineArray = explode(";", trim($line));
        $lineArrayWithKeys = array_combine($keys, $lineArray);

        //stores modifiers as key value pair (modifierId and modifierValue) as it simplifies the iteration over the elements
        $modifierArrayWithKeys = array_slice($lineArrayWithKeys, 13, 8);
        $modifierArrayWithIdValuePairs = [];
        foreach (array_slice($modifierArrayWithKeys, 0, 4) as $key => $value) {
            if ($value !== "100") { //100 percent is the default value -> no modification needed
                $modifierArrayWithIdValuePairs[$key] = $value;
            }
        }
        if (!empty($modifierArrayWithKeys["modifierId1"])) {
            $modifierArrayWithIdValuePairs[$modifierArrayWithKeys["modifierId1"]] = $modifierArrayWithKeys["modifierValue1"];
        }
        if (!empty($modifierArrayWithKeys["modifierId2"])) {
            $modifierArrayWithIdValuePairs[$modifierArrayWithKeys["modifierId2"]] = $modifierArrayWithKeys["modifierValue2"];
        }

        //stores conditionalResourceChanges as array in array as it simplifies the iteration over the elements
        $conditionalResourceChanges = array_slice($lineArrayWithKeys, 26);
        $conditionalResourceChangesArray = [];
        for ($i = 1; $i <= 2; $i++) {
            if ($conditionalResourceChanges["resourceChange$i"] !== "") { //removes empty resourceChanges
                $conditionalResourceChangesArray[] = [
                    "description" => $conditionalResourceChanges["description$i"],
                    "prerequisite" => $conditionalResourceChanges["prerequisite$i"],
                    "resourceChange" => $conditionalResourceChanges["resourceChange$i"],
                    "value" => $conditionalResourceChanges["value$i"],
                ];
            }
        }

        echo "\$konjunkturphase" . $lineArrayWithKeys["id"] . " = new KonjunkturphaseDefinition(\n";
        echo "\t" . "id: KonjunkturphasenId::create(" . $lineArrayWithKeys["id"] . "),\n";
        echo "\t" . "type: KonjunkturphaseTypeEnum::" . $lineArrayWithKeys["type"] . ",\n";
        echo "\t" . "name: '" . $lineArrayWithKeys["title"] . "',\n";
        echo "\t" . "description: '" . $lineArrayWithKeys["description"] . "',\n";
        echo "\t" . "additionalEvents: '',\n"; //TODO remove?
        echo "\t" . "zeitsteine: new Zeitsteine([\n";
        echo "\t\t" . "new ZeitsteinePerPlayer(2, " . $lineArrayWithKeys["sumZeitsteine2Spieler"]/2 . "),\n";
        echo "\t\t" . "new ZeitsteinePerPlayer(3, " . $lineArrayWithKeys["sumZeitsteine3Spieler"]/3 . "),\n";
        echo "\t\t" . "new ZeitsteinePerPlayer(4, " . $lineArrayWithKeys["sumZeitsteine4Spieler"]/4 . "),\n";
        echo "\t" . "]),\n";
        echo "\t" . "kompetenzbereiche: [\n";
        printKompetenzbereichDefinition("BILDUNG_UND_KARRIERE", $lineArrayWithKeys["maxBildungUndKarriere"]);
        printKompetenzbereichDefinition("SOZIALES_UND_FREIZEIT", $lineArrayWithKeys["maxFreizeitUndSoziales"]);
        printKompetenzbereichDefinition("INVESTITIONEN", $lineArrayWithKeys["maxInvestitionen"]);
        printKompetenzbereichDefinition("JOBS", $lineArrayWithKeys["maxJobs"]);
        echo "\t" . "],\n";
        printModifiers($modifierArrayWithIdValuePairs);
        echo "\t" . "auswirkungen: [\n";
        printAuswirkungen("LOANS_INTEREST_RATE", $lineArrayWithKeys["Kreditzins"]);
        printAuswirkungen("STOCKS_BONUS", $lineArrayWithKeys["AktienKursbonus"]);
        printAuswirkungen("CRYPTO", $lineArrayWithKeys["CryptoKursbonus"]);
        printAuswirkungen("DIVIDEND", $lineArrayWithKeys["Dividende"]);
        printAuswirkungen("REAL_ESTATE", $lineArrayWithKeys["Immobilien"]);
        echo "\t" . "],\n";
        echo "\t" . "conditionalResourceChanges: [\n";
        foreach ($conditionalResourceChangesArray as $conditionalResourceChange) {
            printConditionalResourceChanges(
                $conditionalResourceChange["prerequisite"]==="" ? "NO_PREREQUISITES" : $conditionalResourceChange["prerequisite"],
                $conditionalResourceChange["resourceChange"],
                $conditionalResourceChange["value"]
            );
        }
        echo "\t" . "],\n";
        echo ");\n\n";

    }
}


//importMiniJobCards();
//importJobCards();
//importWeiterbildungCards();
//importKategorieCards();
importEreignisCards();
//importInvestitionenCards();
//importKonjunkturphasen();





