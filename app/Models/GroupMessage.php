<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GroupMessage extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'group_messages';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'group_id',
        'group_message_id',
        'type',
        'message',
        'model_id',
        'model_name',
        'forwarded',
        'deleted',
        'is_broadcast',
        'broadcast_company_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ['forwarded' => 'boolean', 'deleted' => 'boolean', 'is_broadcast' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return HasMany
     */
    public function groupMessagesUserLog(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'group_messages_user_log', 'group_message_id', 'user_id')
            ->withPivot('group_id', 'read', 'favourited')
            ->withTimestamps();
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageAttribute()
    {
        return $this->getFirstMediaUrl('image');
    }

    /**
     * @param null|string $part
     *
     * @return array
     */
    public function getAllowedMediaMimeTypes(? string $part) : array
    {
        $mimeTypes = [
            'image' => [
                'image/jpeg',
                'image/jpg',
                'image/png',
            ],
            'video' => [
                'video/mp4',
                'video/webm',
                //'video/quicktime',
            ],
        ];

        return \in_array($part, ['image', 'video'], true)
        ? $mimeTypes[$part]
        : $mimeTypes;
    }
}
