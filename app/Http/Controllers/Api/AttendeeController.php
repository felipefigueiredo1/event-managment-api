<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use Illuminate\Support\Facades\Gate;

class AttendeeController extends Controller
{
    use CanLoadRelationships;

    protected array $relationships = ['user', 'event'];

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        Gate::authorize('viewAny', [Attendee::class, $event]);

        $attendees = $this->loadRelationships($event->attendees()->getQuery()->latest());

        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        Gate::authorize('create', [Attendee::class, $event]);

        $attendee = $event->attendees()->create([
            'user_id' => $request->user()->id
        ]);

        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        $this->assertEventMatch($event, $attendee);

        Gate::authorize('view', $attendee);

        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        $this->assertEventMatch($event, $attendee);

        Gate::authorize('delete', $attendee);

        $attendee->delete();

        return response(status: 204);
    }

    protected function assertEventMatch(Event $event, Attendee $attendee): void
    {
        abort_unless($attendee->event_id === $event->id, 404);
    }
}
