<div class="grid">
    @section('header')
        @if(isset($add_url))
            <div class="float-start">
                <a class="btn btn-secondary btn-add" href="{{ $add_url }}">
                    <i class="fa fa-plus"></i>
                    {{ $add_label ?: 'Nowy' }}
                </a>
            </div>
            <div class="float-end">{{ $pagination }}</div>
        @else
            {{ $pagination }}
        @endif
    @show

    @section('table')
        <form method="get" id="filter-form">
            <table class="table table-striped responsive">
                <thead>
                <tr>
                    @foreach($columns as $column)
                        {{ grid_column($column) }}
                    @endforeach
                </tr>
                @if($is_filterable)
                    <tr>
                        @foreach($columns as $column)
                            {{ grid_filter($column) }}
                        @endforeach
                    </tr>
                @endif
                </thead>
                <tbody>
                @if($rows)
                    @foreach($rows as $row)
                        {{ grid_row($row) }}
                    @endforeach
                @else
                    <tr>
                        {{ grid_empty($grid) }}
                    </tr>
                @endif
                </tbody>
            </table>

            <input type="submit" style="visibility: hidden; height: 1px"/>
        </form>
    @show

    @section('footer')
        @if(isset($add_url))
            <div class="float-end">{{ $pagination }}</div>
        @else
            {{ $pagination }}
        @endif
    @show
</div>
