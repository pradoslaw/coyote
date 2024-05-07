@extends('errors.503')

@section('title')
    You are banned
@endsection

@section('content')
    <style>
        #panel {
            border: 1px solid #B0BEC5;
            padding: 10px;
            text-align: left;
            color: #666;
            font-family: Arial, sans-serif;
            border-radius: 5px;
            margin: auto;
            max-width: 80%;
        }

        #panel > ul {
            list-style-type: none;
        }

        #panel > ul li {
            margin: 10px 0;
        }

        #panel > ul li strong {
            display: inline-block;
            width: 30%;
        }

        #contact {
            margin-top: 20px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #B0BEC5;
        }

    </style>

    <div id="panel">
        <ul>
            <li>
                <strong>Ban ID:</strong>
                <span>{{ $id }}</span>
            </li>
            <li>
                <strong>Reason:</strong>
                <span>{{ $reason ?? '--' }}</span>
            </li>
            <li>
                <strong>Expiration date:</strong>
                <span>{{ $expire_at ?? '--' }}</span>
            </li>
        </ul>
    </div>

    <div id="contact">
        You can contact us by e-mail: <a href="mailto:support@4programmers.net?subject=Ban ID:{{ $id }}">support@4programmers.net</a>
    </div>
@endsection
