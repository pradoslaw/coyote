@extends('errors.layout', ['title' => 'Nieoczekiwany błąd'])

@section('head')
  <style>
      .subtitle {
          font-size: 24px;
          font-weight: 500;
          color: #7e7f7e;
      }

      .subtitle p {
          margin: 0;
      }

      .desktop {
          display: none;
      }

      a.button {
          display: inline-block;
          font-size: 14px;
          color: white;
          border-radius: 4px;
          padding: 16px 32px;
          text-decoration: none;
          background: #00a538;
      }

      @media (min-width: 640px) {
          .desktop {
              display: block;
          }

          .mobile {
              display: none;
          }
      }
  </style>
@endsection

@section('content')
  <h2 class="subtitle mobile">
    <p>HTTP 1.1/500 Server Error</p>
    <p>Content-Type: text/html;</p>
  </h2>
  <h2 class="subtitle desktop">
    <p>HTTP 1.1/500 Internal Server Error</p>
    <p>Content-Type: text/html; charset=UTF-8</p>
    <p>Date: {{date("D, j M Y H:i:s T")}}</p>
  </h2>
  <h1>Ups, coś poszło <br> nie tak!</h1>
  <a href="/" class="button">
    Strona Główna
  </a>
@endsection
