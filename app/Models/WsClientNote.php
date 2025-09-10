<?php

namespace App\Models;

use App\Models\CronofySchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use DB;

class WsClientNote extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ws_client_notes';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cronofy_schedule_id',
        'user_id',
        'comment',
    ];

    /**
     * "BelongsTo" relation to `eap_tickets` table
     * via `ticket_id` field.
     *
     * @return BelongsTo
     */
    public function cronofySchedule(): BelongsTo
    {
        return $this->belongsTo(CronofySchedule::class, 'cronofy_schedule_id');
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord($id)
    {
        if (!empty($id)) {
            $deleted = DB::table('ws_client_notes')->where('id', $id)->delete();
            if ($deleted) {
                return array('deleted' => 'true');
            } else {
                return array('deleted' => 'error');
            }
        } else {
            return array('deleted' => 'error');
        }
    }

    /**
     * Update session notes data.
     * @param array $payload
     * @return boolean
     */
    public function updateNotes($payload)
    {
        $updated = $this->where('id', $payload['commentId'])->update(['comment'=> $payload['notes']]);
        if ($updated) {
            return true;
        }
        return false;
    }
}
