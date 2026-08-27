@props(['title' => 'BookSpace'])

@php
    $title = $title;
@endphp

@include('layouts.app', ['title' => $title, 'slot' => $slot])

