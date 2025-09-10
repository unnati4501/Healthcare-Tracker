<section class="content">
<div class="container-fluid">
    <div class="card">
    <div class="card-body p-0">
        <div class="calender-card">
            <div class="calender-filter">
                <h5 class="filter-header">Filter</h5>
                <div id="accordion" class="accordion filter-accordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Health Coach
                                </button>
                            </h5>
                        </div>
                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body searchable-block">
                          <div class="search-wrap mb-3">
                                <input type="text" class="form-control" placeholder="Search">
                                <i class="far fa-search"></i>
                          </div>
                          <ul class="filter-list {{ $presenterDivClass }}">
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border">
                                        <span class="sx-list-name"><b>Select All</b></span>
                                        <input type="checkbox" class="presenter_selectall">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                            @foreach($presenters as $key => $value)
                                @php
                                    $user = App\Models\User::find($key);
                                    $image= $user->getMediaData('logo', ['w' => 320, 'h' => 320])['url'];
                                @endphp
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-user-img">
                                            <img class="" src="{{$image}}" alt="">
                                        </span>
                                        <span class="sx-list-name">{{ $value }}</span>
                                        <input type="checkbox" class="presenters calFilter" name="presenters[]" value="{{$key}}" data-value="{{ $value }}">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                            @endforeach
                          </ul>
                        </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h5 class="mb-0">
                              <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Booking Status
                              </button>
                            </h5>
                        </div>
                      <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <div class="card-body">
                            <ul class="filter-list">
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border">
                                        <span class="sx-list-name"><b>Select All</b></span>
                                        <input type="checkbox" class="status_selectall">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-color teal">
                                        </span>
                                        <span class="sx-list-name">Completed</span>
                                        <input type="checkbox" name="couchstatus[]" class="couchstatus calFilter" value="5" data-status="Completed">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-color orange">
                                        </span>
                                        <span class="sx-list-name">Booked</span>
                                        <input type="checkbox" name="couchstatus[]" class="couchstatus calFilter" value="4" data-status="Booked">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-color new-grey">
                                        </span>
                                        <span class="sx-list-name">Cancelled</span>
                                        <input type="checkbox" name="couchstatus[]" class="couchstatus calFilter" value="3" data-status="Cancelled">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-color blue">
                                        </span>
                                        <span class="sx-list-name">Pending</span>
                                        <input type="checkbox" name="couchstatus[]" class="couchstatus calFilter" value="6" data-status="Paused">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-color yellow">
                                        </span>
                                        <span class="sx-list-name">Elapsed</span>
                                        <input type="checkbox" name="couchstatus[]" class="couchstatus calFilter" value="7" data-status="Elapsed">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-color red">
                                        </span>
                                        <span class="sx-list-name">Rejected</span>
                                        <input type="checkbox" name="couchstatus[]" class="couchstatus calFilter" value="8" data-status="Rejected">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                            </ul>
                            <div class="noresults">No results found</div>
                        </div>
                      </div>
                    </div>
                    <div class="card">
                      <div class="card-header" id="headingThree">
                        <h5 class="mb-0">
                          <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Expertise
                          </button>
                        </h5>
                      </div>
                      <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="card-body">
                            <ul class="filter-list {{ $expertiseDivClass }}">
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border">
                                        <span class="sx-list-name"><b>Select All</b></span>
                                        <input type="checkbox" class="expertise_selectall">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                @foreach($expertise as $key => $value)
                                <li>
                                    <label class="custom-checkbox custom-checkbox-r-border" >
                                        <span class="sx-list-name">{{$value}}  </span>
                                        <input type="checkbox" class="expertise calFilter" name="expertise[]" value="{{$key}}" data-status="{{$value}}">
                                        <span class="checkmark"></span>
                                    </label>
                                </li>
                                @endforeach
                              </ul>
                        </div>
                      </div>
                    </div>
                    {{-- <div class="card">
                        <div class="card-header" id="headingFour">
                          <h5 class="mb-0">
                            <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Time Range
                            </button>
                          </h5>
                        </div>
                        <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
                          <div class="card-body">
                            Anim pariatur cliche reprehenderit, enim eiusmod
                          </div>
                        </div>
                      </div> --}}
                  </div>
            </div>
            <div class="calender-wrap">
                <div id='calendar'></div>
            </div>
        </div>
    </div>
    </div>
</div>
    <!-- /.container-fluid -->
</section>