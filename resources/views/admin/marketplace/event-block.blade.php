<div class="col-xxl-3 col-md-4 col-sm-6">
    <div class="card basic-card">
        <img class="card-img-top" src="{{ $event->logo }}"/>
        <div class="card-body">
            <div>
                <h5 class="card-title text-primary mb-2" title="{{ $event->name }}">
                    {{ $event->name }}
                </h5>
                <p class="card-text gray-900 mb-4">
                    <i class="far fa-clock me-2">
                    </i>
                    {{ $duration }}
                </p>
            </div>
            <div class="d-grid">
                <a class="btn btn-primary" href="{{ route('admin.marketplace.book-event', $event->id) }}">
                    {{ trans('marketplace.buttons.more_details') }}
                </a>
            </div>
        </div>
    </div>
</div>