@extends('errors.base', [
	'title' => 'Accès non autorisé',
	'text1' => config('messages.errors.403')['text1'],
	'text2' => config('messages.errors.403')['text2'],
])