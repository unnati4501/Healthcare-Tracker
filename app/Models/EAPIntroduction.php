<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EAPIntroduction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_introduction';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'introduction'];

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
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity(array $payload)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if ($role->group == 'zevo') {
            $eapIntroduction = self::updateOrCreate(
                ['company_id' => null],
                ['introduction' => $payload['introduction']]
            );
        } else {
            $company         = $user->company()->first();
            $eapIntroduction = self::updateOrCreate(
                ['company_id' => $company->id],
                ['introduction' => $payload['introduction']]
            );
        }
        return $eapIntroduction;
    }
}
