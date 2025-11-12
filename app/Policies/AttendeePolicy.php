<?php

namespace App\Policies;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;

class AttendeePolicy
{
    /**
     * Determine whether the user can view any attendees for the given event.
     */
    public function viewAny(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event) || $this->isAttendingEvent($user, $event);
    }

    /**
     * Determine whether the user can view the attendee.
     */
    public function view(User $user, Attendee $attendee): bool
    {
        return $this->isAttendeeOwner($user, $attendee) || $this->ownsEvent($user, $attendee->event);
    }

    /**
     * Determine whether the user can create an attendee for the given event.
     */
    public function create(User $user, Event $event): bool
    {
        if ($this->ownsEvent($user, $event)) {
            return true;
        }

        return !$event->attendees()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the attendee.
     */
    public function delete(User $user, Attendee $attendee): bool
    {
        return $this->isAttendeeOwner($user, $attendee)
            || $this->ownsEvent($user, $attendee->event);
    }

    protected function ownsEvent(User $user, Event $event): bool
    {
        return $event->user_id === $user->id;
    }

    protected function isAttendingEvent(User $user, Event $event): bool
    {
        return $event->attendees()
            ->where('user_id', $user->id)
            ->exists();
    }

    protected function isAttendeeOwner(User $user, Attendee $attendee): bool
    {
        return $attendee->user_id === $user->id;
    }
}

