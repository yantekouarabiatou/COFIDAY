
@extends('errors.layout-errors')

@section('title', 'Erreur interne')
@section('code', '500')
@section('message', 'Erreur interne du serveur')
@section('description')
Une erreur inattendue est survenue. Merci de réessayer plus tard.
@endsection
