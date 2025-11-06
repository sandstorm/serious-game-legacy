<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var list<string>
     */
    protected $with = [
        'players',
        'course',
        'creator',
        'creatorPlayer'
    ];

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * If a player created the game, this relation points to the player.
     * Creator is empty in this case.
     *
     * @return BelongsTo<Player, $this>
     */
    public function creatorPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'creator_id');
    }

    /**
     * @return BelongsToMany<Player, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class,  'game_user', 'game_id', 'user_id');
    }

    public function getCreatorName(): string
    {
        if ($this->creator !== null) {
            return $this->creator->name;
        } elseif ($this->creatorPlayer !== null) {
            return $this->creatorPlayer->name;
        } else {
            return 'Unbekannt';
        }
    }

    public function isCreatedByPlayer(): bool
    {
        return $this->creatorPlayer !== null;
    }
}
