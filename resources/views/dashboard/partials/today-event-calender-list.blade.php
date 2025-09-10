<div class="item">
    <div class="event-calendar-card">
        <div class="event-card-title">
            <p class="event-calendar-title">{{ $eventName }}</p>
            <p class="grey-text">{{ $day }}</p>
        </div>
        <div class="d-flex flex-wrap flex-row-reverse">
            <div class="text-sm-right text-center flex-grow-1">
                <span class="event-calendar-icon">
                    <img src="{{ $eventImageName }}" alt="">
                </span>
            </div>
            <div class="flex-grow-1">
                <ul>
                    @if($roleName == null)
                        <li class="company-name"><i class="far fa-building"></i>{{ $companyName }}</li>
                    @endif
                    <li><i class="far fa-calendar"></i>{{ $bookingDate }}</li>
                    <li><i class="far fa-clock"></i>{{ $bookingStarttime }}</li>
                    <li><i class="far fa-users"></i>{{ $participantsUsers }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>