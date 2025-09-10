<?php

namespace App\Models;

use App\Jobs\SendEapPushNotifications;
use App\Models\EAPOrderPriority;
use App\Models\User;
use App\Models\DepartmentLocation;
use App\Models\TeamLocation;
use App\Models\EapDepartment;
use App\Observers\EapObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class EapCompany extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_company';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['eap_id', 'company_id'];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eapCompany()
    {
        return $this->belongsTo('App\Models\EAP', 'eap_id');
    }
}
