# Changelog

All notable changes to `ralphjsmit/laravel-filament-record-finder` will be documented in this file.

## 1.2.15 - 2024-12-18

- Chore: compatibility release with Filament `^3.2.131`.

## 1.2.14 - 2024-12-16

- Fix: compatibility with Filament ^3.2.130 after removal of Async Alpine for tables

## 1.2.13 - 2024-12-06

- Fix: improve handling of very long labels/sentences.

## 1.2.12 - 2024-11-28

- Refactor: do not use Tailwind CSS `size-` attribute to ensure pre-v3.4 compatibility.

## 1.2.11 - 2024-11-20

- Feat: add support for s`patie/laravel-translatable` in formatted attributes.

## 1.2.10 - 2024-11-14

- Feat: add `->recordUrl()` method to let selected records link to.

## 1.2.9 - 2024-11-09

- Feat: allow deleting items without opening record finder.
- Fix: filter modal closes on every Livewire request.

## 1.2.8 - 2024-10-26

- Feat: add record finder table render hooks (before + after).

## 1.2.7 - 2024-10-26

- Fix: console error "Livewire component not found in DOM" when opening modal for second time (in SPA mode).

## 1.2.6 - 2024-10-24

- Fix: preserve `x-ignore` on record finder table component

## 1.2.5 - 2024-10-24

- Feat: add Italian translations by David.
- Feat: table actions support `->recordFinderTableLivewireComponent()` method.
- Fix: do not lose HtmlString record labels when imploding.
- Fix: table action modals not being opened/lost.

## 1.2.4 - 2024-10-03

- Feat: add support for returning `HtmlString` from `getRecordLabelFromRecordUsing()` callback.
- Feat: improve stability of plugin in modals and slide-overs.

## 1.2.3 - 2024-09-27

- Fix: associate and attach action correctly handle view only relation managers.

## 1.2.2 - 2024-09-27

- Feat: ability to override Livewire RecordFinderTable component.

## 1.2.1 - 2024-09-23

- Fix: table filters with `indicateUsing()` provided by native Filament.

## 1.2.0 - 2024-09-12

- Feat: add support for affix actions (prefix & suffix actions).

## 1.1.0 - 2024-08-30

- Feat: add support for `->afterStateUpdated()`.
- Feat: add missing recordLabelAttribute() method.
- Feat: do not deselect records when searching.
- Fix: disable record finder button if form component is disabled.

## 1.0.0 - 2024-07-11

- Public release!

## 0.2.2 - 2024-07-04

- Feat: add table reset hook when closing modal.

## 0.2.1 - 2024-07-04

-Fix: undefined JS variable when emitting event with undefined property

## 0.2.0 - 2024-07-04

- Feat: implement associate & attach action.
- Refactor: reference updated AutoTranslator.

## 0.1.0 - 2024-06-11

- Initial release.
