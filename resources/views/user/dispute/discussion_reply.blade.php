@foreach($disputeDiscussions as $discussion)
@if($discussion->file)
    @php
        $strArr = explode('_', $discussion->file);
        $strPosition = strlen($strArr[0]) + 1;
        $fileName = substr($discussion->file, $strPosition);
    @endphp
@endif
@if($discussion->type == 'User' && auth()->id() == $discussion->user_id)
    <div class="admin-conv-box mt-24">
        <div class="d-flex gap-3">
            <div class="dis-user-conv ml-68 w-100">
                <p class="text-end mb-0 f-16 gilroy-medium text-dark mt-1p">{{ getColumnValue($discussion->user) }}</p>
                <p class="text-end mb-0 f-12 gilroy-medium text-gray mt-6">{{ dateFormat($discussion->created_at) }}</p>
                <div class="users-conversation mt-12 bg-white-100">
                    <p class="mb-0 f-14 leading-23 gilroy-medium text-gray-100 text-end"> {!! $discussion->message !!}</p>
                </div>
                @if($discussion->file)
                    <div class="proof-btn-div d-flex justify-content-end mt-2">
                        <a href="{{ route('user.disputes.download', $discussion->file) }}" class='btn f-10 leading-12 proof-btn p-0 border-DF bg-white text-gray-100 b-none'> 
                            <span>{{ $fileName }}</span>
                            {!! svgIcons('download_icon') !!}
                        </a>
                    </div>
                @endif
            </div>
            <div class="dis-user-img">
                <img src="{{ image($discussion?->user?->picture, 'profile') }}" alt="{{ __('Profile') }}">
            </div>
        </div>
    </div>
@elseif($discussion->type == 'User')

    <div class="user-conv-box-2  mt-24 mr-68">
        <div class="d-flex gap-3">
            <div class="dis-user-img">
                <img src="{{ image($discussion?->user?->picture, 'profile') }}" alt="{{ __('Profile') }}">
            </div>
            <div class="dis-user-conv w-100">
                <p class="mb-0 f-16 gilroy-medium text-dark mt-1p">{{ getColumnValue($discussion->user) }}</p>
                <p class="mb-0 f-12 gilroy-medium text-gray mt-6">{{ dateFormat($discussion->created_at) }}</p>
                <div class="users-conversation mt-12 bg-white-50">
                    <p class="mb-0 f-14 leading-23 gilroy-medium text-gray-100"> {!! $discussion->message !!}</p>
                </div>
                @if($discussion->file)
                    <div class="proof-btn-div d-flex justify-content-start mt-2">
                        <a href="{{ route('user.disputes.download', $discussion->file) }}" class='btn f-10 leading-12 proof-btn p-0 border-DF bg-white text-gray-100 b-none'> 
                            {!! svgIcons('download_icon') !!}
                            <span>{{ $fileName }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="user-conv-box-2  mt-24 mr-68">
        <div class="d-flex gap-3">
            <div class="dis-user-img">
                <img src="{{ image($discussion?->admin?->picture, 'profile') }}" alt="{{ __('Profile') }}">
            </div>
            <div class="dis-user-conv w-100">
                <p class="mb-0 f-16 gilroy-medium text-dark mt-1p">{{ getColumnValue($discussion->admin) }}</p>
                <p class="mb-0 f-12 gilroy-medium text-gray mt-6">{{ dateFormat($discussion->created_at) }}</p>
                <div class="users-conversation mt-12 bg-white-50">
                    <p class="mb-0 f-14 leading-23 gilroy-medium text-gray-100"> {!! $discussion->message !!}</p>
                </div>
                @if($discussion->file)
                    <div class="proof-btn-div d-flex justify-content-start mt-2">
                        <a href="{{ route('user.disputes.download', $discussion->file) }}" class='btn f-10 leading-12 proof-btn p-0 border-DF bg-white text-gray-100 b-none'> 
                            {!! svgIcons('download_icon') !!}
                            <span>{{ $fileName }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
@endforeach