<?php
declare (strict_types = 1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ClearMediaOnDeleteEntity
 */
class ClearMediaOnDeleteEntity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var CommunityRecipe
     */
    protected $model;
    protected $collection;

    /**
     * ClearMediaOnDeleteEntity constructor.
     *
     * @param Challenge $challenge
     */
    public function __construct($model, String $collection)
    {
        $this->queue      = 'notifications';
        $this->model      = $model;
        $this->collection = $collection;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // get members of the creator's company
        $this->model->clearMediaCollection('logo');
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['clear-media-collection'];
    }
}
