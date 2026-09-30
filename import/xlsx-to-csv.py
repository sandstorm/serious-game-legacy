#!/usr/bin/env python3
"""
Converts the import xlsx files provided by the content team into the ;-separated CSV files read by csv-importer.php.

Usage (from the repository root):
    python3 import/xlsx-to-csv.py import/KartenImportVorlage_final.xlsx import/Konjunkturen_import.xlsx ...

Only the Python standard library is used, so no dependencies need to be installed.

An xlsx file is a zip archive of XML files. The parts relevant for us are:
- xl/workbook.xml             lists the sheets (name + relation id)
- xl/_rels/workbook.xml.rels  maps each relation id to the XML file containing the sheet
- xl/worksheets/sheetN.xml    the cells of one sheet
- xl/sharedStrings.xml        all texts of the workbook; text cells only store an index into this list
"""
import csv
import os
import re
import string
import sys
import zipfile
import xml.etree.ElementTree as ET

# Sheets are mapped to CSV files by the start of their name, because Excel cuts sheet names after 31 characters
# (e.g. "Kategorie Karten (Bil"). Sheets not listed here (instructions, notes, ...) are skipped.
SHEET_NAME_PREFIX_TO_CSV_FILE = {
    "Kategorie Karten": "Kategorie_Karten.csv",
    "Jobs": "Jobs.csv",
    "Ereignisse": "Ereignisse.csv",
    "Minijobs": "Minijobs.csv",
    "Weiterbildung": "Weiterbildungen.csv",
    "Investition": "Investitionen_Immobilien.csv",
    "Konjunkturen": "Konjunkturen.csv",
    "DATEN": "Lebensziele.csv",
}

MAIN_NAMESPACE = "http://schemas.openxmlformats.org/spreadsheetml/2006/main"
RELATION_ID_ATTRIBUTE = "{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id"
NS = {"m": MAIN_NAMESPACE}


def column_letters_to_index(cell_reference: str) -> int:
    """
    Returns the zero-based column index of a cell reference like "B7" or "AA12".

    Column letters are a base-26 number with the digits A=1 ... Z=26 (there is no zero digit):
    "A" -> 0, "Z" -> 25, "AA" -> 26, "AB" -> 27, ...
    """
    column_letters = re.match(r"[A-Z]+", cell_reference).group()
    column_number = 0
    for letter in column_letters:
        digit = string.ascii_uppercase.index(letter) + 1
        column_number = column_number * 26 + digit
    return column_number - 1


def format_number(raw_value: str) -> str:
    """
    Excel stores every number as a float, e.g. 5 as "5.0" and 1.1 as "1.1000000000000001".
    Whole numbers are written without decimals, all others rounded to 10 significant digits.
    """
    number = float(raw_value)
    if number.is_integer():
        return str(int(number))
    return "%.10g" % number


def read_shared_strings(archive: zipfile.ZipFile) -> list[str]:
    if "xl/sharedStrings.xml" not in archive.namelist():
        return []
    shared_strings = []
    for string_item in ET.fromstring(archive.read("xl/sharedStrings.xml")).findall("m:si", NS):
        # formatted texts are split into several <t> elements, so they need to be joined
        shared_strings.append("".join(text.text or "" for text in string_item.iter(f"{{{MAIN_NAMESPACE}}}t")))
    return shared_strings


def read_cell_value(cell: ET.Element, shared_strings: list[str]) -> str:
    """
    The cell type attribute "t" defines how the value in <v> has to be read:
    "s" = index into the shared strings, "inlineStr" = text stored in the cell itself,
    "str"/"b"/"e" = formula text, boolean, error (used as is), no type = number
    """
    cell_type = cell.get("t")
    if cell_type == "inlineStr":
        return "".join(text.text or "" for text in cell.iter(f"{{{MAIN_NAMESPACE}}}t"))

    value_element = cell.find("m:v", NS)
    if value_element is None or value_element.text is None:
        return ""
    if cell_type == "s":
        return shared_strings[int(value_element.text)]
    if cell_type in ("str", "b", "e"):
        return value_element.text
    return format_number(value_element.text)


def read_sheets(xlsx_path: str):
    """
    Yields (sheet name, rows) for every sheet. Rows are a dict of row index => {column index => value}, because
    xlsx files only contain non-empty cells.
    """
    archive = zipfile.ZipFile(xlsx_path)
    shared_strings = read_shared_strings(archive)

    workbook = ET.fromstring(archive.read("xl/workbook.xml"))
    relations = ET.fromstring(archive.read("xl/_rels/workbook.xml.rels"))
    sheet_file_by_relation_id = {relation.get("Id"): relation.get("Target") for relation in relations}

    for sheet in workbook.find("m:sheets", NS):
        # targets are relative to xl/ (e.g. "worksheets/sheet1.xml"), some tools write them absolute ("/xl/...")
        sheet_file = sheet_file_by_relation_id[sheet.get(RELATION_ID_ATTRIBUTE)].lstrip("/")
        if not sheet_file.startswith("xl/"):
            sheet_file = "xl/" + sheet_file

        rows = {}
        for row in ET.fromstring(archive.read(sheet_file)).iter(f"{{{MAIN_NAMESPACE}}}row"):
            row_index = int(row.get("r")) - 1  # row numbers in xlsx start at 1
            for cell in row.findall("m:c", NS):
                column_index = column_letters_to_index(cell.get("r"))
                rows.setdefault(row_index, {})[column_index] = read_cell_value(cell, shared_strings)
        yield sheet.get("name"), rows


def find_csv_file_name(sheet_name: str) -> str | None:
    for prefix, csv_file_name in SHEET_NAME_PREFIX_TO_CSV_FILE.items():
        if sheet_name.startswith(prefix):
            return csv_file_name
    return None


def write_csv(csv_path: str, rows: dict[int, dict[int, str]]) -> None:
    """Writes the rows as a dense table, filling cells missing in the xlsx with empty strings."""
    row_count = max(rows) + 1
    column_count = max(max(columns) for columns in rows.values()) + 1
    with open(csv_path, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file, delimiter=";", lineterminator="\n")
        for row_index in range(row_count):
            row = rows.get(row_index, {})
            writer.writerow([row.get(column_index, "") for column_index in range(column_count)])


def main(xlsx_paths: list[str]) -> None:
    target_directory = os.path.dirname(os.path.abspath(__file__))
    for xlsx_path in xlsx_paths:
        for sheet_name, rows in read_sheets(xlsx_path):
            csv_file_name = find_csv_file_name(sheet_name)
            if csv_file_name is None or not rows:
                print(f"skipped sheet '{sheet_name}' in {os.path.basename(xlsx_path)}")
                continue
            write_csv(os.path.join(target_directory, csv_file_name), rows)
            print(f"wrote {csv_file_name} from sheet '{sheet_name}' in {os.path.basename(xlsx_path)}")


if __name__ == "__main__":
    main(sys.argv[1:])
