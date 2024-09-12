@extends('errors.layout', ['title' => 'Zostałeś zbanowany'])

@section('head')
  <style>
      h1 {
          font-size: 52px;
      }

      ul.details {
          border: 1px solid #00a538;
          border-radius: 8px;
          padding: 16px;
          display: block;
          margin: 48px 0;
      }

      ul.details li {
          list-style: none;
          font-size: 16px;
          display: flex;
          justify-content: space-between;
          margin: 16px 0;
      }

      ul.details li strong {
          font-size: 18px;
          font-weight: 500;
      }

      ul.details li span {
          opacity: 0.5;
      }

      .light {
          opacity: 0.5;
      }

      a {
          font-size: 14px;
          color: #00a538;
      }

      .contact-us {
          line-height: 24px;
      }

      @media (min-width: 640px) {
          h1 {
              font-size: 72px;
              margin-top: 72px;
          }

          ul.details {
              display: inline-block;
              margin: 16px 0;
          }

          ul.details li span {
              text-align: right;
              min-width: 224px;
          }
      }
  </style>
@endsection

@section('content')
  <h1>
    Zostałeś<br>
    zbanowany
  </h1>

  <ul class="details">
    <li>
      <strong>Numer referencyjny:</strong>
      <span>#{{ $id }}</span>
    </li>
    <li>
      <strong>Powód:</strong>
      <span>{{ $reason ?? '--' }}</span>
    </li>
    <li>
      <strong>Data wygaśnięcia:</strong>
      <span>{{ $expire_at ?? '--' }}</span>
    </li>
  </ul>

  <p class="contact-us">
    <span class="light">Możesz się z nami skontaktować na:</span>
    <a href="mailto:support@4programmers.net?subject=Ban ID:{{ $id }}">
      support@4programmers.net
    </a>
  </p>
@endsection
