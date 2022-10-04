@extends('layouts.app')

@section('title', 'Sale Payments')



@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{__('Home')}}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sales.show', $sale) }}">{{ $sale->reference }}</a></li>
        <li class="breadcrumb-item active">{{__('Payments')}}</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @include('utils.alerts')
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

