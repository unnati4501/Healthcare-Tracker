<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Calendly;
use App\Models\SessionUserNotes;

class AddNotesToSessionUserNotes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SessionUserNotes::truncate();

        $getRecords = Calendly::where('notes', '!=', '')->select('user_id', 'notes')->get();

        foreach ($getRecords as $value) {
            SessionUserNotes::create([
                'user_id' => $value->user_id, 'notes' => $value->notes
            ]);
        }
    }
}
