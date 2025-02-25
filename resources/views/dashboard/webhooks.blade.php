@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row ">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Received Web Hooks') }}</div>

                    <div class="card-body">
                        <div class="table-responsive table--no-card m-b-30">
                            <table class="table table-borderless table-striped table-earning">
                                @if (count($webhooks) < 1)
                                    <tbody>
                                    <tr>
                                        <td>No Data</td>
                                    </tr>
                                    </tbody>
                                @else
                                    <thead>
                                    <tr>
                                        <th>Export File</th>
                                        <th>ID</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Downloaded File</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($webhooks as $webhook)
                                        <tr>
                                            <td>{{$webhook->product_feed_export_url}}</td>
                                            <td>{{$webhook->id}}</td>
                                            <td>{{$webhook->getStatusString()}}</td>
                                            <td>{{$webhook->created_at->format('M d, Y H:i')}}</td>
                                            <td>{{ \Carbon\Carbon::instance($webhook->updated_at)->diffForHumans() }}</td>
                                            <td>{{$webhook->downloaded_payload_filename}}</td>
                                            <td>
                                                <a href="#row_{{$webhook->id}}" class="toggle">View</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="7">
                                                <pre id="row_{{$webhook->id}}" style="display:none">{{$webhook->request_body}}</pre>
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
        </div>
    </div>
@endsection
