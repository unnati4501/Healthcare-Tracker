<div class="item">
    <div class="event-calendar-card">
        <div>
            <p class="grey-text">
                {{ $day }}
            </p>
            <p class="event-calendar-title">
                {{ $eventName }}
            </p>
            <ul>
                @if($roleName == null)
                <li class="today-event-company-name">
                    <i class="far fa-building">
                    </i>
                    {{ $companyName }}
                </li>
                @endif
                <li>
                    <i class="far fa-calendar">
                    </i>
                    {{ $bookingDate }}
                </li>
                <li>
                    <i class="far fa-clock">
                    </i>
                    {{ $bookingStarttime }}
                </li>
                <li>
                    <i class="far fa-users">
                    </i>
                    {{ $participantsUsers }}
                </li>
            </ul>
        </div>
        <div class="text-end">
            <span class="event-calendar-icon">
                <img alt="" src="{{ $eventImageName}}">
                </img>
            </span>
        </div>
    </div>
</div>