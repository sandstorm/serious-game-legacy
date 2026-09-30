<?php
declare(strict_types=1);

/*
 * Prints the PHP code for the card/Konjunkturphasen/Lebensziel definitions from the CSV files in this directory.
 * The CSV files are generated from the xlsx files with xlsx-to-csv.py. See docs/2025_09_10_Karten_Importieren.md
 *
 * Usage: php csv-importer.php <type> | pbcopy
 */

class ModifierMapping {
    /**
     * @param string $modifierId
     * @param string $parameterName name of the parameter in ModifierParameters, empty if the modifier has no value
     * @param bool $isValueRequired false, if the value is optional (e.g. AUSSETZEN defaults to 1 turn)
     */
    public function __construct(public string $modifierId, public string $parameterName, public bool $isValueRequired = true)
    {
    }
}


/* CSV FUNCTIONS */

/**
 * Reads all rows of a csv file. The first row (table name) is skipped.
 * fgetcsv is needed (instead of reading line by line), because cells can contain line breaks.
 *
 * @param string $fileName
 * @return array<int, array<int, string>> rows including the table header row
 */
function readCsvRows(string $fileName): array
{
    $handle = fopen(__DIR__ . "/" . $fileName, "r");
    if ($handle === false) {
        throw new RuntimeException("Could not open " . $fileName);
    }
    $rows = [];
    while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) { // 0 = no limit for the length of a row
        $rows[] = array_map(fn (?string $value) => trim($value ?? ""), $row);
    }
    fclose($handle);
    return array_slice($rows, 1); // removes the table name
}

/**
 * Reads a csv file and returns one array per card (row), using the table header (second row) as keys.
 * - spaces, quotes and line breaks are removed from the keys (e.g. "Aktien Kursbonus" -> "AktienKursbonus")
 * - columns without a table header are ignored (e.g. notes next to the table)
 * - rows without an id are ignored (e.g. empty rows or notes below the table)
 *
 * @param string $fileName
 * @return array<int, array<string, string>>
 */
function readCsv(string $fileName): array
{
    $rows = readCsvRows($fileName);
    $keys = array_map(fn (string $key) => str_replace(["\"", " ", "\n", "\r"], "", $key), $rows[0]);

    $result = [];
    foreach (array_slice($rows, 1) as $row) {
        if (($row[0] ?? "") === "") {
            continue;
        }
        $rowWithKeys = [];
        foreach ($keys as $index => $key) {
            if ($key !== "") {
                $rowWithKeys[$key] = $row[$index] ?? "";
            }
        }
        $result[] = $rowWithKeys;
    }
    return $result;
}

/**
 * Returns the value as PHP string literal, so quotes in the texts don't break the generated code.
 */
function phpString(string $value): string
{
    return var_export($value, true);
}

/**
 * Money values can be plain numbers ("50000") or formatted ("50.000,00 €"), depending on how the csv was exported.
 */
function parseMoney(string $value): float
{
    $value = str_replace(["€", " ", "\t"], "", $value);
    if (str_contains($value, ",")) {
        $value = str_replace([".", ","], ["", "."], $value);
    }
    return floatval($value);
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
 * @param string $description
 * @return void
 */
function printConditionalResourceChanges(string $prerequisites, string $resourceChange, string $value, string $description):void
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
    echo "\t\t\t" . "description: " . phpString($description) . ",\n";
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
 * @param string $id id of the card/Konjunkturphase, used for error messages
 * @param array $modifierArrayWithIdValuePairs
 * @return void
 */
function printModifiers(string $id, array $modifierArrayWithIdValuePairs): void
{
    $modifierMappings = [
        "AUSSETZEN" => new ModifierMapping("AUSSETZEN", "numberOfTurns", isValueRequired: false),
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
    // a wrong modifier would crash every game in which the card/Konjunkturphase is used -> fail during the import
    foreach ($modifierArrayWithIdValuePairs as $modifierId => $modifierValue) {
        if (!array_key_exists($modifierId, $modifierMappings)) {
            throw new RuntimeException($id . ": unknown modifier " . $modifierId);
        }
        $modifierMapping = $modifierMappings[$modifierId];
        if ($modifierMapping->parameterName === "" && $modifierValue !== "") {
            throw new RuntimeException($id . ": modifier " . $modifierId . " has no value, but '" . $modifierValue . "' was specified");
        }
        if ($modifierMapping->parameterName !== "" && $modifierMapping->isValueRequired && $modifierValue === "") {
            throw new RuntimeException($id . ": modifier " . $modifierId . " needs a value (" . $modifierMapping->parameterName . ")");
        }
        if ($modifierValue !== "" && !is_numeric($modifierValue)) {
            throw new RuntimeException($id . ": value '" . $modifierValue . "' of modifier " . $modifierId . " is not a number");
        }
    }
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
    foreach (readCsv("Minijobs.csv") as $lineArrayWithKeys) {
        echo "\"" . $lineArrayWithKeys["id"] . "\" => new MinijobCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: " . phpString($lineArrayWithKeys["title"]) . ",\n";
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
    foreach (readCsv("Jobs.csv") as $lineArrayWithKeys) {
        echo "\"" . $lineArrayWithKeys["id"] . "\" => new JobCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: " . phpString($lineArrayWithKeys["title"]) . ",\n";
        echo "\t" . "description: " . phpString($lineArrayWithKeys["description"]) . ",\n";
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
    foreach (readCsv("Weiterbildungen.csv") as $lineArrayWithKeys) {
        //first answer Id is used for the correct answer -> randomized through the shuffle. The shuffle is seeded with
        //the card id, so the answer ids stay the same when the cards are imported again.
        $randomizer = new \Random\Randomizer(new \Random\Engine\Mt19937(crc32($lineArrayWithKeys["id"])));
        $answerIds = $randomizer->shuffleArray(["a", "b", "c", "d"]);
        //array_filter removes empty entries as not all questions have all three wrong answers
        $wrongAnswersArray = array_values(array_filter([
            $lineArrayWithKeys["wrongAnswer1"],
            $lineArrayWithKeys["wrongAnswer2"],
            $lineArrayWithKeys["wrongAnswer3"],
        ]));

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new WeiterbildungCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "description: " . phpString($lineArrayWithKeys["description"]) . ",\n";
        echo "\t" . "answerOptions: [\n";
        echo "\t\t" . "new AnswerOption(new AnswerId(\"" . $answerIds[0] . "\"), " . phpString($lineArrayWithKeys["correctAnswer"]) . ", true),\n";
        foreach($wrongAnswersArray as $key => $wrongAnswer) {
            echo "\t\t" . "new AnswerOption(new AnswerId(\"" . $answerIds[$key + 1] . "\"), " . phpString($wrongAnswer) . "),\n";
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
    foreach (readCsv("Kategorie_Karten.csv") as $lineArrayWithKeys) {
        echo "\"" . $lineArrayWithKeys["id"] . "\" => new KategorieCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "categoryId: CategoryId::" . $lineArrayWithKeys["category"] . ",\n";
        echo "\t" . "title: " . phpString($lineArrayWithKeys["title"]) . ",\n";
        echo "\t" . "description: " . phpString($lineArrayWithKeys["description"]) . ",\n";
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
    foreach (readCsv("Ereignisse.csv") as $lineArrayWithKeys) {
        //stores multiplier as key value pair (modifierId and modifierValue) as it simplifies the iteration over the elements
        $modifierArrayWithIdValuePairs = [];
        if (!empty($lineArrayWithKeys["modifierId1"])) {
            $modifierArrayWithIdValuePairs[$lineArrayWithKeys["modifierId1"]] = $lineArrayWithKeys["modifierValue1percentage"];
        }
        if (!empty($lineArrayWithKeys["modifierId2"])) {
            $modifierArrayWithIdValuePairs[$lineArrayWithKeys["modifierId2"]] = $lineArrayWithKeys["modifierValue2"];
        }
        if (!empty($lineArrayWithKeys["modifierId3"])) {
            $modifierArrayWithIdValuePairs[$lineArrayWithKeys["modifierId3"]] = $lineArrayWithKeys["modifierValue3"];
        }

        echo "\"" . $lineArrayWithKeys["id"] . "\" => new EreignisCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "categoryId: CategoryId::EREIGNIS_" . $lineArrayWithKeys["category"] . ",\n";
        echo "\t" . "title: " . phpString($lineArrayWithKeys["title"]) . ",\n";
        echo "\t" . "description: " . phpString($lineArrayWithKeys["description"]) . ",\n";
        printPhaseAndYear($lineArrayWithKeys["phase"], $lineArrayWithKeys["year"]);
        printResourceChanges($lineArrayWithKeys);
        printModifiers($lineArrayWithKeys["id"], $modifierArrayWithIdValuePairs);
        echo "\t" . "ereignisRequirementIds: [\n";
        foreach (["prerequisiteStatusId1", "prerequisiteStatusId2"] as $prerequisiteKey) {
            if (!empty($lineArrayWithKeys[$prerequisiteKey])) {
                echo "\t\t" . "EreignisPrerequisitesId::" . $lineArrayWithKeys[$prerequisiteKey] . ",\n";
            }
        }
        //all cards that have a requiredCardId need the Prerequisite HAS_SPECIFIC_CARD for validation
        if ($lineArrayWithKeys["prerequisiteCardId"] !== "") {
            echo "\t\t" . "EreignisPrerequisitesId::HAS_SPECIFIC_CARD,\n";
        }
        echo "\t" . "],\n";
        if ($lineArrayWithKeys["prerequisiteCardId"] !== "") {
            echo "\t" . "requiredCardId: new CardId('" . $lineArrayWithKeys["prerequisiteCardId"] . "'),\n";
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
    foreach (readCsv("Investitionen_Immobilien.csv") as $lineArrayWithKeys) {
        echo "\"" . $lineArrayWithKeys["id"] . "\" => new ImmobilienCardDefinition(\n";
        echo "\t" . "id: new CardId('" . $lineArrayWithKeys["id"] . "'),\n";
        echo "\t" . "title: " . phpString($lineArrayWithKeys["title"]) . ",\n";
        echo "\t" . "description: " . phpString($lineArrayWithKeys["description"]) . ",\n";
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
    foreach (readCsv("Konjunkturen.csv") as $lineArrayWithKeys) {
        //stores modifiers as key value pair (modifierId and modifierValue) as it simplifies the iteration over the elements
        $modifierArrayWithIdValuePairs = [];
        foreach (["GEHALT", "BildungKarriereKosten", "FreizeitSozialesKosten", "Lebenshaltungskosten"] as $key) {
            //100 percent is the default value (also used, if the cell is empty) -> no modification needed
            if ($lineArrayWithKeys[$key] !== "100" && $lineArrayWithKeys[$key] !== "") {
                $modifierArrayWithIdValuePairs[$key] = $lineArrayWithKeys[$key];
            }
        }
        if (!empty($lineArrayWithKeys["modifierId1"])) {
            $modifierArrayWithIdValuePairs[$lineArrayWithKeys["modifierId1"]] = $lineArrayWithKeys["modifierValue1"];
        }
        if (!empty($lineArrayWithKeys["modifierId2"])) {
            $modifierArrayWithIdValuePairs[$lineArrayWithKeys["modifierId2"]] = $lineArrayWithKeys["modifierValue2"];
        }

        //stores conditionalResourceChanges as array in array as it simplifies the iteration over the elements
        $conditionalResourceChangesArray = [];
        for ($i = 1; $i <= 2; $i++) {
            if ($lineArrayWithKeys["resourceChange$i"] !== "") { //removes empty resourceChanges
                $conditionalResourceChangesArray[] = [
                    "description" => $lineArrayWithKeys["description$i"],
                    "prerequisite" => $lineArrayWithKeys["prerequisite$i"],
                    "resourceChange" => $lineArrayWithKeys["resourceChange$i"],
                    "value" => $lineArrayWithKeys["value$i"],
                ];
            }
        }

        // the table header of this column is a whole sentence ("Zeitsteine, anzeigen als Verständnis, ..."), so we
        // look it up by its beginning
        $zeitsteineDescription = "";
        foreach ($lineArrayWithKeys as $key => $value) {
            if (str_starts_with($key, "Zeitsteine,")) {
                $zeitsteineDescription = $value;
            }
        }

        echo "\$konjunkturphase" . $lineArrayWithKeys["id"] . " = new KonjunkturphaseDefinition(\n";
        echo "\t" . "id: KonjunkturphasenId::create(" . $lineArrayWithKeys["id"] . "),\n";
        echo "\t" . "type: KonjunkturphaseTypeEnum::" . $lineArrayWithKeys["type"] . ",\n";
        echo "\t" . "name: " . phpString($lineArrayWithKeys["title"]) . ",\n";
        echo "\t" . "description: " . phpString($lineArrayWithKeys["description"]) . ",\n";
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
        printModifiers("Konjunkturphase " . $lineArrayWithKeys["id"], $modifierArrayWithIdValuePairs);
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
                $conditionalResourceChange["value"],
                $conditionalResourceChange["description"],
            );
        }
        echo "\t" . "],\n";
        echo "\t" . "zeitsteineDescription: " . phpString($zeitsteineDescription) . ",\n";
        echo ");\n\n";
    }
}

/**
 * Function imports Lebensziele from csv file and echoes them in the console.
 *
 * The table has two header rows and the columns of the three phases have the same names, so the columns are
 * accessed by their position: title, description, then 4 columns per phase (description, Investitionen,
 * Bildung & Karriere, Freizeit & Soziales).
 *
 * @return void
 */
function importLebensziele(): void
{
    $rows = array_slice(readCsvRows("Lebensziele.csv"), 1); //removes the second table header row (the first is removed by readCsvRows)
    $rows = array_values(array_filter($rows, fn (array $row) => ($row[0] ?? "") !== ""));

    foreach ($rows as $index => $row) {
        echo "\$lebensziel" . $index + 1 . " = new LebenszielDefinition(\n";
        echo "\t" . "id: LebenszielId::create(" . $index + 1 . "),\n";
        echo "\t" . "name: " . phpString($row[0]) . ",\n";
        echo "\t" . "description: " . phpString($row[1]) . ",\n";
        echo "\t" . "phaseDefinitions: [\n";
        for ($phase = 1; $phase <= 3; $phase++) {
            $firstColumn = 2 + ($phase - 1) * 4;
            echo "\t\t" . "new LebenszielPhaseDefinition(\n";
            echo "\t\t\t" . "lebenszielPhaseId: LebenszielPhaseId::PHASE_" . $phase . ",\n";
            echo "\t\t\t" . "description: " . phpString($row[$firstColumn]) . ",\n";
            echo "\t\t\t" . "investitionen: new MoneyAmount(" . parseMoney($row[$firstColumn + 1]) . "),\n";
            // intval, because the cells contain texts like "2 Kompetenzsteine"
            echo "\t\t\t" . "bildungsKompetenzSlots: " . intval($row[$firstColumn + 2]) . ",\n";
            echo "\t\t\t" . "freizeitKompetenzSlots: " . intval($row[$firstColumn + 3]) . ",\n";
            echo "\t\t" . "),\n";
        }
        echo "\t" . "],\n";
        echo ");\n\n";
    }
}


$importFunctions = [
    "minijobs" => importMiniJobCards(...),
    "jobs" => importJobCards(...),
    "weiterbildungen" => importWeiterbildungCards(...),
    "kategorie" => importKategorieCards(...),
    "ereignisse" => importEreignisCards(...),
    "immobilien" => importImmobilienCards(...),
    "konjunkturphasen" => importKonjunkturphasen(...),
    "lebensziele" => importLebensziele(...),
];

$type = $argv[1] ?? "";
if (!array_key_exists($type, $importFunctions)) {
    fwrite(STDERR, "Usage: php csv-importer.php <" . implode("|", array_keys($importFunctions)) . ">\n");
    exit(1);
}
// the output is buffered, so no partially generated code ends up in the clipboard if the import fails
ob_start();
try {
    $importFunctions[$type]();
} catch (RuntimeException $exception) {
    ob_end_clean();
    fwrite(STDERR, "Import failed: " . $exception->getMessage() . "\n");
    exit(1);
}
ob_end_flush();

// written to STDERR, so it is not part of the generated code (e.g. when piped to pbcopy)
preg_match(
    '/DEFINITIONS_VERSION = (\d+);/',
    (string) file_get_contents(__DIR__ . "/../app/src/Definitions/Configuration/Configuration.php"),
    $matches
);
fwrite(STDERR, "\nReminder:\n"
    . "- increase Configuration::DEFINITIONS_VERSION (currently " . ($matches[1] ?? "unknown") . ") once per import,"
    . " so games created with the old definitions cannot be continued\n"
    . "- move cards that are missing in the new files to CardFinder::getLegacyCards() instead of deleting them\n");
