@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="card">
            <div class="card-header">{{ __('Assets') }}</div>

            <div class="card-body">
                <div class="table-responsive table--no-card m-b-30">
                    {{ $assets->links() }}

                    <table class="table table-borderless table-striped table-earning">

                        @if (count($assets) < 1)
                            <tbody>
                            <tr>
                                <td>No Data</td>
                            </tr>
                            </tbody>
                        @else
                            <thead>
                            <tr>
                                <th>Updated</th>
                                <th>Salsify ID</th>
                                <th>Name</th>
                                <th>Format</th>
                                <th>Dimensions</th>
                                <th>Size</th>
                                <th>Preview</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($assets as $asset)
                                <tr>
                                    <td>{{ \Carbon\Carbon::instance($asset->updated_at)->diffForHumans() }}</td>
                                    <td>{{$asset->salsify_id}}</td>
                                    <td>{{$asset->name}}</td>
                                    <td>{{$asset->format}}</td>
                                    <td>{{$asset->width}}x{{$asset->height}}</td>
                                    <td>{{number_format($asset->bytes/1024, 0)}}kb</td>
                                    <td>
                                        <img src="{{$asset->getCdnUrl()}}" width="100" height="100" data-url="{{$asset->url}}" />
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
