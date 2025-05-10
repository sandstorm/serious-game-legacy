# CODE STYLE: sandstorm Laravel / Filament Best Practices

[README](README.md) [CODE_STYLE](CODE_STYLE.md) [ARCHITECTURE](ARCHITECTURE.md) 

<!-- TOC -->
* [CODE STYLE: sandstorm Laravel / Filament Best Practices](#code-style-sandstorm-laravel--filament-best-practices)
* [Architecture](#architecture)
  * [Suggested Architecture of Laravel Applications: Ports & Adapters](#suggested-architecture-of-laravel-applications-ports--adapters)
  * [Only Use Dependency Injection, no stateful helpers or Laravel Facades](#only-use-dependency-injection-no-stateful-helpers-or-laravel-facades)
  * [Where to use Laravel Eloquent (ORM), and where not?](#where-to-use-laravel-eloquent-orm-and-where-not)
* [Filament](#filament)
  * [Familiarize yourself with Filament Advanced Features](#familiarize-yourself-with-filament-advanced-features)
  * [Prepared for Multiple Filament Panels](#prepared-for-multiple-filament-panels)
* [Lightweight Permissions with AppAuthorizer](#lightweight-permissions-with-appauthorizer)
  * [ULIDs as IDs](#ulids-as-ids)
  * [Laravel Strict Mode](#laravel-strict-mode)
  * [throw exceptions on error for file system storage](#throw-exceptions-on-error-for-file-system-storage)
* [Installed Packages for Laravel / Filament](#installed-packages-for-laravel--filament)
  * [archilex/filament-filter-sets (Advanced Tables - Commercial Plugin) **NOT INSTALLED IN THIS PROJECT**](#archilexfilament-filter-sets-advanced-tables---commercial-plugin-not-installed-in-this-project)
  * [awcodes/filament-tiptap-editor (custom and extensible Rich Text Editor)](#awcodesfilament-tiptap-editor-custom-and-extensible-rich-text-editor)
  * [Record Finder Pro (more advanced Record Selector) **NOT INSTALLED IN THIS PROJECT**](#record-finder-pro-more-advanced-record-selector-not-installed-in-this-project)
  * [dutchcodingcompany/filament-developer-logins (development logins)](#dutchcodingcompanyfilament-developer-logins-development-logins)
  * [spatie/laravel-ignition (beautiful error pages in dev)](#spatielaravel-ignition-beautiful-error-pages-in-dev)
  * [barryvdh/laravel-ide-helper (IDE autocompletion)](#barryvdhlaravel-ide-helper-ide-autocompletion)
  * [jeffgreco13/filament-breezy (Two Factor Authentication & My Profile Page)](#jeffgreco13filament-breezy-two-factor-authentication--my-profile-page)
  * [Further Package Suggestions](#further-package-suggestions)
<!-- TOC -->

# Architecture

## Suggested Architecture of Laravel Applications: Ports & Adapters

**Problems with the standard "Framework" approach**

A big problem with Laravel applications is that they are essentially **guiding you
into the framework** - so that means all tutorials, guides, ... show you "the laravel
way" and how to embrace this ecosystem.

While, on the one side, this leads to pretty standardized applications in terms of
where every component is located, it has the big problem that the **core domain
of the software is spread out** across many controllers, services, event handlers,
and many other framework constructs. We've teached the same with Flow applications,
and in my current (Sebastian, 2025) understanding this is one of the biggest mistakes
we made.


**Goals**

We want to focus on the business logic, and want to make code easily testable. Ports
& Adapters **guide the code towards easy testability**.


**When not to use Ports&Adapters**

- a pure CRUD application with no core business logic (i.e. only "Datenpflege")


**How to learn about Ports & Adapters**

- a book: https://gitlab.sandstorm.de/sandstorm/books/-/blob/main/Tech/00__CONCEPTS_MUST_READ/2025_Hexagonal_Architecture_Explained.epub?ref_type=heads
- a video for hexagonal architecture: https://www.youtube.com/watch?v=UwQSfyYrSrg
    - core idea around 09:40
- Talk to Sebastian, who learned this from Bastian Waidelich :)

**Our Naming Convention**

The Core Domain should reside inside `Domain\` namespace (OUTSIDE of the laravel
`App\` namespace) in a SEPARATE Library package. Our suggested structure is:

```
src/ folder (Namespace Domain\)
├── CoreDomainX/             # A bounded context of your domain
│   ├── DrivingPorts/        # "Input" ports - how external code calls your domain
│   │   └── ForXY.php        # Interface defining allowed operations
│   ├── DrivenPorts/         # "Output" ports - how your domain calls external services
│   │   ├── ForPersistence/  # f.e. Database operations, ...
│   │   └── ForLogging/      # f.e. Logging operations, ...
│   ├── Dto/                 # Data Transfer Objects (Immutable)
│   └── CoreDomainXApp.php   # Main implementation of business logic
```

For DTOs, we recommend to use https://github.com/bwaidelich/types.

The Core Domain should NEVER depend on any Laravel class.

The Adapters should reside in `\App\Adapters` in the Laravel application.

**Ports&Adapters Summary**

❌ Don't Do This

- Accessing Laravel facades or helpers in domain code
- Putting business logic in adapters
- Creating circular dependencies between ports
- Using framework-specific types in DTOs

✅ Do This Instead

- Inject all dependencies through ports
- Keep adapters thin and focused on translation
- Design ports around business concepts, not technical ones
- Use framework-agnostic value objects in DTOs

## Only Use Dependency Injection, no stateful helpers or Laravel Facades

Methods like `env()` or `app()` totally break encapsulation in Laravel; please DO NOT USE them but instead
inject the underlying object via Dependency Injection. This way, we can more easily analyze the code base
because we have less "magic connections" between classes which are invisible to the outside.

in `disallowed-calls.neon`, we test for this; so phpstan fails in case this is violated.

## Where to use Laravel Eloquent (ORM), and where not?

✅ Use Laravel Eloquent (ORM) for:

- Usage within Filament PHP

❌ Don't use Laravel Eloquent (ORM) for:

-  anything else.

# Filament

## Familiarize yourself with Filament Advanced Features

In our setup, Filament is fully set up:

- Filament with custom theme (needed for easy plugin integration)
- Database Notifications set up
- basic print.css style included
- For Asynchronous work: Laravel Queues (with Redis) set up, with a running queue worker
    - in case your application is queue heavy, install Laravel Horizon for monitoring
- Laravel Scheduler set up, based on Supercronic /crontab
- Laravel Caches (with Redis) set up
- TODO: Laravel Broadcast?

## Prepared for Multiple Filament Panels

Contrary to the default Filament kickstart, we use `App\Filament\Admin` as namespace
for Resources, Pages, and Widgets. This way, we can easily create a new Panel which
can e.g. use the `App\Filament\Frontend` namespace.


# Lightweight Permissions with AppAuthorizer

NOTE: we tried permission systems based on spatie/laravel-permission (using bezhansalleh/filament-shield),
and based on casbin.org.
For us, they were too complex on one side and too restrictive on the other side (both at the
same time). That's why we are now rolling our own (very simple) permission system in
`App\Authorization\AppAuthorizer`, which helps us centralize all permission logic at a single
point.

See `App\Authorization\AppAuthorizer` for further details.

## ULIDs as IDs

We strongly suggest to use ULIDs as IDs - they are shorter than UUIDs and thus easier
to work with visually; but still globally unique. NOTE - they are incremented roughly,
so you should NOT DEPEND on the RANDOMNESS of ULIDs (same as with UUIDs, which also
have an incremental mode).

## Laravel Strict Mode

see https://laravel-news.com/shouldbestrict - catches quite some errors. NOTE we only
enable this in dev mode, as we want our dev mode to be more restrictive, and our prod
mode more forgiving.

## throw exceptions on error for file system storage

By default, file storage errors do not throw exceptions but simply return `false`. This has the problem that
we manually need to test for successful uploads; and we don't get good error messages what is going wrong.

In our applications, we found it much better to throw on storage errors. This works easily with setting `'throw' => true`
inside `config/filesystems.php` (this is already configured here).

# Installed Packages for Laravel / Filament

## archilex/filament-filter-sets (Advanced Tables - Commercial Plugin) **NOT INSTALLED IN THIS PROJECT**

Main features: greatly extended Listing Tables by user customizable views, reorderable and hideable columns, custom filters, ...

This is a MUST HAVE for any Laravel / Filament application. We have it licensed for all
Sandstorm projects. It is well documented at https://filamentphp.com/plugins/kenneth-sese-advanced-tables .

The package is fully installed in the boilerplate. You only need to add the trait
`use AdvancedTables;` for every `ListXY` class (extending from `ListRecords`).

To update, use `dev composer-update-filament-advanced-tables`.

## awcodes/filament-tiptap-editor (custom and extensible Rich Text Editor)

based on ProseMirror, which is a really clean and extensible Rich Text Editor.

- TODO: custom extension

##  Record Finder Pro (more advanced Record Selector) **NOT INSTALLED IN THIS PROJECT**

Replace your `Select`'s with beautiful Record Finders to make searching easier.
Works everywhere out-of-the-box & integrates with resources.

This is a SUGGESTION for every NEW Laravel / Filament application.
We have it licensed for all  Sandstorm projects. It is well documented at
https://filamentphp.com/plugins/ralphjsmit-record-finder-pro .

The package is fully installed in the boilerplate.

To update, use `dev composer-update-filament-record-finder-pro`.

## dutchcodingcompany/filament-developer-logins (development logins)

one-click logins for all `...@example.com` logins in Local Development.

## spatie/laravel-ignition (beautiful error pages in dev)

beautiful error pages with lots of context and information in development mode.

It is configured with path mapping, so you can DIRECTLY click on a stack trace line and
IntelliJ opens the appropriate file.

## barryvdh/laravel-ide-helper (IDE autocompletion)

**OBSOLETE -- TODO UPDATE ME (based on Laravel IDEA)

## jeffgreco13/filament-breezy (Two Factor Authentication & My Profile Page)

Includes a User Profile Page, so the users can:

- Change Personal Information (Name, Email)
- Change Password
- Enable OTP based Two Factor Authentication
- (optionally) manage API tokens via laravel/sanctum

## Further Package Suggestions

- bezhansalleh/filament-language-switch
    - in case you need a language switcher
