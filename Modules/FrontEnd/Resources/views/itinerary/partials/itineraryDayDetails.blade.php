@php
    use Modules\FrontEnd\Helpers\DateUtils;
    $listDetail = $listDetail ?? [];
@endphp
<div class="item-detail{{ !empty($expanded) ? ' expand' : '' }}">
    <div class="header">
        <span>{{__('frontend::common.day')}} {{$day->day}}</span>
        <i class="fas fa-chevron-right"></i>
    </div>
    <div class="body">
        <div class="list-wrapper">
            <div class="list">
                @foreach($listDetail as $detail)
                    <div class="item">
                        <div class="time">{{DateUtils::formatDisplayHHMMA($detail->time)}}</div>
                        <div class="title">{{$detail->title}}</div>
                        <div class="description">{{$detail->description}}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
