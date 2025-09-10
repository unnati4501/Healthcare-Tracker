<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Course;
use App\Models\Feed;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\Webinar;
use App\Models\Podcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;

class CategoryTags extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'category_tags';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['category_id', 'name', 'short_name'];

    /**
     * "BelongsTo" relation to `categories` table
     * via `category_id` field.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * "HasMany" relation to `courses` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function masterclassTag(): HasMany
    {
        return $this->hasMany(Course::class, 'tag_id');
    }

    /**
     * "HasMany" relation to `meditation_tracks` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function meditationTrackTag(): HasMany
    {
        return $this->hasMany(MeditationTrack::class, 'tag_id');
    }

    /**
     * "HasMany" relation to `recipe` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function recipeTag(): HasMany
    {
        return $this->hasMany(Recipe::class, 'tag_id');
    }

    /**
     * "HasMany" relation to `feeds` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function feedTag(): HasMany
    {
        return $this->hasMany(Feed::class, 'tag_id');
    }

    /**
     * "HasMany" relation to `webinars` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function webinarTag(): HasMany
    {
        return $this->hasMany(Webinar::class, 'tag_id');
    }

    /**
     * "HasMany" relation to `podcasts` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function podcastTag(): HasMany
    {
        return $this->hasMany(Podcast::class, 'tag_id');
    }

    /**
     * "HasMany" relation to `shorts` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function shortsTag(): HasMany
    {
        return $this->hasMany(Shorts::class, 'tag_id');
    }

    /**
     * get count of mapped content for respective tag
     *
     * @param payload
     * @return dataTable
     */
    public function getMappedContentCount()
    {
        switch ($this->category_id) {
            case 1:
                return $this->masterclassTag->count('id');
                break;
            case 2:
                return $this->feedTag->count('id');
                break;
            case 4:
                return $this->meditationTrackTag->count('id');
                break;
            case 5:
                return $this->recipeTag->count('id');
                break;
            case 7:
                return $this->webinarTag->count('id');
                break;
            case 9:
                return $this->podcastTag->count('id');
                break;
            case 10:
                return $this->shortsTag->count('id');
                break;
            default:
                return 0;
                break;
        }
    }

    /**
     * To get pre defined tags categories
     *
     * @param payload
     * @return dataTable
     */
    public function getTagsTableData($payload)
    {
        $list = $this->getTagsList($payload);
        return DataTables::of($list)
            ->addColumn('total_tags', function ($record) {
                return $record->tags->count('id');
            })
            ->addColumn('actions', function ($record) {
                return view('admin.categories.tags.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /* To get pre defined tags categories
     *
     * @param payload
     * @return array
     */
    public function getTagsList($payload)
    {
        return Category::select('id', 'name')
            ->where('has_tags', true)
            ->get();
    }

    /**
     * store category tag
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload)
    {
        $tag = $this->create([
            'category_id' => $payload['category'],
            'name'        => $payload['name'],
            'short_name'  => str_replace(' ', '_', strtolower($payload['name'])),
        ]);

        if ($tag) {
            return $tag;
        }

        return false;
    }

    /**
     * get tags of respected category
     *
     * @param Category $category
     * @param array payload
     * @return dataTable
     */
    public function getTableData(Category $category, $payload)
    {
        $list = $this->getCategoryTagsList($category->id, $payload);
        return DataTables::of($list)
            ->addColumn('mapped_content', function ($category) {
                return $category->getMappedContentCount();
            })
            ->addColumn('actions', function ($record) {
                return view('admin.categories.tags.view-listaction', compact('record'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get tags of respected category
     *
     * @param number $category
     * @param array payload
     * @return array
     */
    public function getCategoryTagsList($category, $payload)
    {
        return $this
            ->select('id', 'name', 'category_id')
            ->where('category_id', $category)
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * update category tag.
     *
     * @param array payload
     * @return boolean
     */
    public function updateEntity($payload)
    {
        $updated = $this->update([
            'name'       => $payload['name'],
            'short_name' => str_replace(' ', '_', strtolower($payload['name'])),
        ]);

        if ($updated) {
            return true;
        }
        return false;
    }

    /**
     * delete category tag
     *
     * @return array
     */
    public function deleteEntity()
    {
        $mappedContentCount = $this->getMappedContentCount();
        if ($mappedContentCount > 0) {
            return array('deleted' => 'use');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }

        return array('deleted' => 'error');
    }
}
