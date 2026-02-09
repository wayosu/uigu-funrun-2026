@extends('errors.minimal')

@section('title', __('Terlalu Banyak Permintaan'))
@section('code', '429')
@section('message', __('Terlalu Banyak Permintaan'))
@section('description', 'Maaf, Anda mengirim terlalu banyak permintaan ke server kami. Silakan tunggu beberapa saat.')
