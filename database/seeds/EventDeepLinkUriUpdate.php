<?php
namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventDeepLinkUriUpdate extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Which not update deep link uri
        $getEvents = Event::where('deep_link_uri', null)->get();

        foreach ($getEvents as $value) {
            $value->deep_link_uri = "zevolife://zevo/event/" . $value->getKey();
            $value->update();
        }
    }
}
