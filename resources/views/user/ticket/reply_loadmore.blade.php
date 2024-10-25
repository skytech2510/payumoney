@foreach($ticketReplies as $ticketReply)
    @if($ticketReply->user_type == 'user' )
        <div class="admin-conv-box mt-24">
            <div class="d-flex gap-3">
                <div class="dis-user-conv ml-68 w-100">
                    <p class="text-end mb-0 f-16 gilroy-medium text-dark mt-1p">{{ ucfirst(getColumnValue($ticketReply->user)) }}</p>
                    <p class="text-end mb-0 f-12 gilroy-medium text-gray mt-6">{{ dateFormat($ticketReply->created_at) }}</p>
                    <div class="users-conversation mt-12 bg-white-100 mb-2">
                        <p class="mb-0 f-14 leading-23 gilroy-medium text-gray-100 text-end"> {!! $ticketReply->message !!}
                        </p>
                    </div>
                    @if($ticketReply->file)
                    <div class="proof-btn-div d-flex justify-content-end">
                        <a href="{{ route('user.tickets.download', $ticketReply->file?->filename) }}" class='btn f-10 leading-12 proof-btn p-0 border-DF bg-white text-gray-100'><span>{{ $ticketReply->file?->originalname }}</span> 
                            {!! svgIcons('download_icon') !!}
                        </a>
                    </div>
                    @endif
                </div>
                <div class="dis-user-img">
                    <img src="{{ image($ticketReply->user?->picture, 'profile') }}" alt="{{ __('Profile') }}">
                </div>
            </div>
        </div>
    @else
        <div class="user-conv-box-2 mt-24 mr-68">
            <div class="d-flex gap-3">
                <div class="dis-user-img">
                    <img src="{{ image($ticketReply->admin?->picture, 'profile') }}" alt="{{ __('Profile') }}">
                </div>
                <div class="dis-user-conv w-100">
                    <p class="mb-0 f-16 gilroy-medium text-dark mt-1p">{{ ucfirst(getColumnValue($ticketReply->admin)) }}</p>
                    <p class="mb-0 f-12 gilroy-medium text-gray mt-6">{{ dateFormat($ticketReply->created_at) }}</p>
                    <div class="users-conversation mt-12 bg-white-50 mb-2">
                        <p class="mb-0 f-14 leading-23 gilroy-medium text-gray-100"> {!! $ticketReply->message !!}</p>
                    </div>
                    @if($ticketReply->file)
                        <div class="proof-btn-div d-flex justify-content-start">
                            <a href="{{ route('user.tickets.download', $ticketReply->file?->filename) }}" class='btn f-10 leading-12 proof-btn p-0 border-DF bg-white text-gray-100'>
                                {!! svgIcons('download_icon') !!}
                                <span>{{ $ticketReply->file?->originalname }}</span> 
                                
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endforeach 