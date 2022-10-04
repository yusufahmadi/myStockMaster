@extends('layouts.app')

@section('title', 'Products')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{__('Home')}}</a></li>
        <li class="breadcrumb-item active">{{__('Products')}}</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <livewire:products.product-page />
                      
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection