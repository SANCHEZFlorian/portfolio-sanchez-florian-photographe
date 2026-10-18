@extends('errors.layout')

@section('code', '429')
@section('hud', 'BURST_BUFFER_FULL // 20 FPS • RAW')
@section('subtitle', 'Mode rafale saturé')
@section('title', 'Trop de requêtes')
@section('message', 'Vous avez envoyé trop de requêtes en peu de temps. Patientez quelques secondes avant de continuer.')
