<?php

declare(strict_types=1);

namespace App\Authorization;

use App\Models\User;
use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;

/**
 * Central authorization service that enforces role-based access control across the application.
 *
 * This class centralizes all authorization decisions - it is called for EVERY authorization check.
 *
 * For permissions, it is easiest to think in "SUBJECT PREDICATE OBJECT" sentences, i.e.
 *
 * ```
 *    UserX can _edit_     Products.
 *    ^Subject  ^Predicate ^Object
 *    =User      =Ability
 * ```
 *
 * In Laravel, predicates are called "abilities".
 *
 * All abilities and objects have to be registered in {@see self::ABILITY_GROUPS} and {@see self::OBJECT_GROUPS} - this way,
 * we can group abilities/objects together; to handle them all in the same way in an access decision.
 *
 * ## Entrypoint
 *
 * This class is called via \Gate::before() in AppServiceProvider.
 *
 * ## Logging of unknown abilities / objects
 *
 * We log unknown abilities / objects into the table unknown_permissions; and render this list in Laravel Pulse
 * at /pulse. This helps to uncover cases we have not thought about.
 *
 * @api
 */
final class AppAuthorizer
{
    // Mapping from Ability to (potentially) grouping for an access decision.
    // These are the predicates.
    private const ABILITY_GROUPS = [
        // Default Laravel / Filament abilities: https://filamentphp.com/docs/3.x/panels/resources/getting-started#authorization
        'viewAny' => 'viewAny',
        'create' => 'create',
        'update' => 'update',
        'view' => 'view',
        'delete' => 'delete',
        'deleteAny' => 'deleteAny',
        'forceDelete' => 'forceDelete',
        'forceDeleteAny' => 'forceDeleteAny',
        'restore' => 'restore',
        'reorder' => 'reorder',

        // Special abilities
        'viewPulse' => 'viewPulse',
        'viewHorizon' => 'viewHorizon',
    ];

    private const OBJECT_GROUPS = [
        User::class => User::class
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /**
     * @param  string[][]  $objectAndOtherArguments
     */
    public function authorize(?User $user, string $ability, array $objectAndOtherArguments): Response
    {
        $result = $this->authorizeInternal($user, $ability, $objectAndOtherArguments);
        // WORKAROUND: in Filament\authorize(), the GateEvaluated event is not triggered - thus we trigger it manually here
        // to ensure tracing in Telescope works properly for Filament screens..
        $this->dispatcher->dispatch(new GateEvaluated($user, $ability, $result->allowed(), $objectAndOtherArguments));

        return $result;
    }

    /**
     * @param  string[][]|object[][]  $objectAndOtherArguments
     */
    private function authorizeInternal(?User $user, string $ability, array $objectAndOtherArguments): Response
    {
        $object = $objectAndOtherArguments[0][0] ?? '';
        if (is_object($object)) {
            $object = get_class($object);
        }
        $this->logUnknownAbilitiesAndObjects($ability, $object);
        // $objectGroup = self::OBJECT_GROUPS[$object] ?? $object;

        if ($user === null) {
            // //////////////////
            // Anonymous case
            // //////////////////

            return Response::denyAsNotFound('anonymous access not allowed');
        }

        if ($user->role_superadmin) {
            // //////////////////
            // Super Admins
            // //////////////////
            return Response::allow('role_superadmin');
        }

        // //////////////////
        // Specific Roles
        // //////////////////
        // for each role, specify the permissions here.
        // if ($user->role_foo) {
        //    switch ($objectGroup) {
        //        case self::OBJECT_GROUP_STAMMDATEN:
        //            // add new cases here if necessary to allow access
        //            return Response::allow('role_foo');
        //    }
        //    // if needed, add more complex logic here
        // }

        return Response::deny('deny by default');
    }

    private function logUnknownAbilitiesAndObjects(string $ability, string $object): void
    {
        if (! array_key_exists($ability, self::ABILITY_GROUPS)) {
            $this->connection->table('unknown_permissions')->updateOrInsert(
                [
                    'ability' => $ability,
                ],
                [
                    'count' => $this->connection->raw('count + 1'),
                    'last_seen' => now(),
                ]
            );
        }
        if ($object !== '' && ! array_key_exists($object, self::OBJECT_GROUPS)) {
            $this->connection->table('unknown_permissions')->updateOrInsert(
                [
                    'object' => $object,
                ],
                [
                    'count' => $this->connection->raw('count + 1'),
                    'last_seen' => now(),
                ]
            );
        }
    }
}
