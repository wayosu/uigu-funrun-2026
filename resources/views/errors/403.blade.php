@extends('errors.minimal')

@section('title', __('Akses Ditolak'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Akses Ditolak'))
@section('description', 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.')
