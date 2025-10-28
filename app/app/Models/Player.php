<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Player extends User
{
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    protected static function booted():void
    {
        // set name while creating
        static::creating(function (Player $player) {
            $player->name = 'Player ' . Str::random(5);
        });

        static::created(function (Player $player) {
            $player->role_player = true;
            $player->email_verified_at = now()->toString();
            $player->save();
        });

        // add global scope to only get users with role_player = true
        static::addGlobalScope('role_player', function ($query) {
            $query->where('role_player', true);
        });
    }

    /**
     * @return BelongsToMany<Game, $this>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class,  'game_user', 'user_id', 'game_id');
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class,  'course_user', 'user_id', 'course_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return false;
    }
}
