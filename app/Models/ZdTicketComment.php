<?php

namespace App\Models;

use App\Models\ZdTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use DB;

class ZdTicketComment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_ticket_comments';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment_id',
        'type',
        'comment',
    ];

    /**
     * "BelongsTo" relation to `eap_tickets` table
     * via `ticket_id` field.
     *
     * @return BelongsTo
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ZdTicket::class, 'ticket_id');
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
            $deleted = DB::table('eap_ticket_comments')->where('id', $id)->delete();
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

    /**
     * Update session notes data.
     * @param array $payload
     * @return boolean
     */
    public function updateSessionNotes($payload)
    {
        $updated = DB::table('eap_calendly')->where('id', $payload['commentId'])->update(['notes'=> $payload['notes']]);
        if ($updated) {
            return true;
        }
        return false;
    }
}
