<?php

declare(strict_types=1);

namespace App\Authorization;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Events\Dispatcher;

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
 * ## Entrypoint
 *
 * This class is called via \Gate::before() in AppServiceProvider.
 *
 * @api
 */
final class AppAuthorizer
{
    public function __construct(
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

        if ($user->role_lehrperson) {
            switch ($object) {
                case Course::class:
                    return $this->handleCoursePermissionsForLehrpersonRole($ability);
            }
        }

        return Response::deny('deny by default');
    }

    private function handleCoursePermissionsForLehrpersonRole(string $ability): Response
    {
        switch ($ability) {
            case 'view':
            case 'update':
            case 'viewAny':
            case 'reorder':
                return Response::allow('role_lehrperson');
        }
        return Response::deny('not allowed for role_lehrperson');
    }
}
