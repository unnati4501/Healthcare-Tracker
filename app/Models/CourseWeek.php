<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;

class CourseWeek extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'course_weeks';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['course_id', 'title', 'is_default','status'];

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
    protected $casts = ['is_default' => 'boolean','status' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course');
    }

    /**
     * @return HasMany
     */
    public function courseLessions(): HasMany
    {
        return $this->hasMany('App\Models\CourseLession');
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

    public function updateEntity($payload)
    {
        $record = $this->update([
            'title' => $payload['title'],
        ]);

        if ($record) {
            return true;
        }

        return false;
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeLessionEntity($payload)
    {
        $lesstion = $this->courseLessions()->create([
            'course_id'      => $this->course_id,
            'course_week_id' => $this->getKey(),
            'title'          => $payload['title'],
            'description'    => $payload['description'],
            'duration'       => decimalToTime($payload['duration']),
            'is_default'     => false,
            'status'         => (!empty($this->status) && $this->status),
        ]);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $lesstion->id . '_' . \time();
            $lesstion->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->preservingOriginal()
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['video']) && !empty($payload['video'])) {
            $name = $lesstion->id . '_' . \time();
            $lesstion->clearMediaCollection('video')->addMediaFromRequest('video')
                ->usingName($payload['video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['video']->extension())
                ->toMediaCollection('video', config('medialibrary.disk_name'));
        }

        if (isset($payload['youtube']) && !empty($payload['youtube'])) {
            $videoId = \getYoutubeVideoId($payload['youtube']);

            $name = $lesstion->id . '_' . \time();
            foreach (['hqdefault', '0'] as $resolution) {
                $customProperties = ['title' => $payload['title'], 'link' => $payload['youtube'], 'ytid' => $videoId];
                $lesstion->addMediaFromUrl(
                    \getYoutubeVideoCover($videoId, $resolution),
                    $lesstion->getAllowedMediaMimeTypes('image')
                )
                    ->preservingOriginal()
                    ->withCustomProperties($customProperties)
                    ->usingName($payload['youtube'])
                    ->usingFileName($name . '.jpg')
                    ->toMediaCollection('youtube', config('medialibrary.disk_name'));
                break;
            }
        }

        if ($lesstion) {
            return true;
        }

        return false;
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getLessionTableData($payload)
    {
        $list = $this->getLessionRecordList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.course.lession.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getLessionRecordList($payload)
    {
        $query = \DB::table('course_lessions')
            ->select('course_lessions.*', \DB::raw("TIME_FORMAT(course_lessions.duration,'%H:%i') as duration"))
            ->where('course_lessions.course_week_id', $this->getKey())
            ->orderBy('course_lessions.updated_at', 'DESC')
            ->groupBy('course_lessions.id');

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $query->where('course_lessions.title', 'like', '%' . $payload['recordName'] . '%');
        }

        return $query->get();
    }

    public function publishCourseModule()
    {
        $data = array();
        $coursePreviousModule = self::where("course_id", $this->course_id)
                                ->where("id", "<", $this->getKey())
                                ->where("status", 0)
                                ->orderBy("id", "ASC")
                                ->limit(1)
                                ->first();

        if (!empty($coursePreviousModule)) {
            $data['published'] = false;
            $data['message'] = "Please publish the ".$coursePreviousModule->title." first.";
        } else {
            if ($this->courseLessions->count() > 0) {
                $updated = $this->update(['status' => 1]);

                if ($updated) {
                    $this->courseLessions()->update(['status' => 1]);
                    $data['published'] = true;
                    $data['message'] = "Course module published successfully";
                } else {
                    $data['published'] = false;
                    $data['message'] = "Something wrong to Publish course module";
                }
            } else {
                $data['published'] = false;
                $data['message'] = "Please add atleast one lesson under the module";
            }
        }
        return $data;
    }
}
