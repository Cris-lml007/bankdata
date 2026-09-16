<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>{!! $title !!}</div>
                <div class="w-25 input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Buscar..." wire:model.blur.enter.live="search">
                    <button class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        @foreach ($heads as $item => $value)
                            <th
                                @if ($value != null) style="cursor: pointer;" wire:click='sortBy("{{ $value }}")' @endif>
                                {{ $item }} @if ($value != null)
                                    <i @class([
                                        'fa',
                                        'fa-sort',
                                        'fa-sort-up' => $sort_by == $value && $sort_direction == 'asc',
                                        'fa-sort-down' => $sort_by == $value && $sort_direction == 'desc',
                                    ])></i>
                                @endif
                            </th>
                        @endforeach
                    </thead>
                    <tbody>
                        {{ $slot }}
                    </tbody>
                    <tfoot>
                        {{ $slot['footer'] }}
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $slot['card-footer'] }}
        </div>
    </div>
</div>

