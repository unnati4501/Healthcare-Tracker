<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CronofyAuthenticate extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronofy_authenticate_code';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_in',
        'sub_id',
        'profile_name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Cronofy user access tokens
     *
     * @return boolean
     */
    public function getTokens($userId)
    {
        $response = $this->where('user_id', $userId)->first();
        return [
            'accessToken'  => $response->access_token,
            'refreshToken' => $response->refresh_token,
            'subId'        => $response->sub_id,
        ];
    }

    /**
     * To store an cronofy authenticate details.
     *
     * @param array payload
     * @return boolean
     */
    public function storeAuthenticate(array $payload)
    {
        $user = auth()->user();

        return $this->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'profile_name'  => $payload['profileName'],
            'access_token'  => $payload['accessToken'],
            'refresh_token' => $payload['refreshToken'],
            'expires_in'    => $payload['expiresIn'],
            'sub_id'        => $payload['subId'],
        ]);
    }

    /**
     * To update an cronofy authenticate details.
     *
     * @param array payload
     * @return boolean
     */
    public function updateAuthenticate(array $payload)
    {
        return $this->updateOrCreate([
            'user_id' => $payload['userId'],
        ], [
            'access_token'  => $payload['accessToken'],
            'refresh_token' => $payload['refreshToken'],
        ]);
    }
}
