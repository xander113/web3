<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('topic.{topicId}', function ($user) {
    return true;
});

Broadcast::channel('presence', function ($user) {
    return true;
});
