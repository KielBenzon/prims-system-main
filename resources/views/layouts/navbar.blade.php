<div class="navbar bg-white flex justify-between items-center rounded-2xl p-4">
     
    @if (Auth::user()->role == 'Admin')
        @if (request()->routeIs('admin_dashboard'))
            <a href="{{ route('admin_dashboard') }}" class="btn btn-ghost text-xl text-black">{{ __('Dashboard') }}</a>
        @endif
        @if (request()->routeIs('scan'))
            <a href="{{ route('scan') }}" class="btn btn-ghost text-xl">{{ __('Scan') }}</a>
        @endif
        @if (request()->routeIs('documents'))
            <a href="{{ route('documents') }}" class="btn btn-ghost text-xl text-black">{{ __('Documents') }}</a>
        @endif
        @if (request()->routeIs('priests'))
            <a href="{{ route('priests') }}" class="btn btn-ghost text-xl text-black">{{ __('Priests') }}</a>
        @endif
        @if (request()->routeIs('donations'))
            <a href="{{ route('donations') }}" class="btn btn-ghost text-xl text-black">{{ __('Donations') }}</a>
        @endif
        @if (request()->routeIs('mails'))
            <a href="{{ route('mails') }}" class="btn btn-ghost text-xl text-black">{{ __('Mails') }}</a>
        @endif
        @if (request()->routeIs('approval_request'))
            <a href="{{ route('approval_request') }}" class="btn btn-ghost text-xl text-black">{{ __('Approval Requests') }}</a>
        @endif
        @if (request()->routeIs('payment'))
            <a href="{{ route('payment') }}" class="btn btn-ghost text-xl text-black">{{ __('Payments') }}</a>
        @endif
         @if (request()->routeIs('transactions'))
            <a href="{{ route('transactions') }}" class="btn btn-ghost text-xl text-black">{{ __('Transactions') }}</a>
        @endif
        @if (request()->routeIs('transactions.index'))
    <a href="{{ route('transactions.index') }}" class="btn btn-ghost text-xl text-black">
        {{ __('Report Generator') }}
    </a>
@elseif (request()->routeIs('transactions.generate'))
    <a href="{{ route('transactions.generate') }}" class="btn btn-ghost text-xl text-black">
        {{ __('Transactions Report') }}
    </a>
@endif
        @if (request()->routeIs('announcement'))
            <a href="{{ route('announcement') }}" class="btn btn-ghost text-xl text-black">{{ __('Announcements') }}</a>
        @endif
        

        @if (request()->routeIs('profile.edit'))
            <a href="{{ route('profile.edit') }}" class="btn btn-ghost text-xl">{{ __('Profile') }}</a>
        @endif
    @endif
    @if (Auth::user()->role == 'Parishioner')
        @if (request()->routeIs('parishioner_dashboard'))
            <a href="{{ route('parishioner_dashboard') }}" class="btn btn-ghost text-xl text-black">{{ __('Dashboard') }}</a>
        @endif
        @if (request()->routeIs('request'))
            <a href="{{ route('request') }}" class="btn btn-ghost text-xl text-black">{{ __('Request Documents') }}</a>
        @endif
        @if (request()->routeIs('parishioner_donatiphp artion'))
            <a href="{{ route('parishioner_donation') }}" class="btn btn-ghost text-xl text-black">{{ __('Donation') }}</a>
        @endif
        @if (request()->routeIs('profile.edit'))
            <a href="{{ route('profile.edit') }}" class="btn btn-ghost text-xl text-black">{{ __('Profile') }}</a>
        @endif
    @endif
    <div class="ml-auto flex items-center gap-2">
     
                    <a href="{{ url('/') }}" class="menu-link relative" title="Home">
    <i class="bx bx-home-circle text-2xl text-black"></i>
</a>

           
        <div class="dropdown dropdown-end relative">
            <label tabindex="0" class="btn btn-ghost text-xl text-black" id="notificationBell" title="Notifications">
                <i class='bx bxs-bell text-black'></i>
                @if ($notifications->where('read_at', null)->count() > 0)
                    <span id="notification-dot"
                          class="absolute top-0 right-0 inline-block w-3 h-3 bg-red-600 rounded-full"></span>
                @endif
            </label>
            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-white rounded-box w-64 -ml-12 z-50" style="background-color: white !important;">
                <li class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-black" style="color: black !important;">Notifications</h3>
                </li>
                @if (Auth::user()->role == 'Admin')
    @forelse ($notifications->where('read_at', null)->take(3) as $notification)
        <li class="border-b last:border-none">
            <a href="{{ getNotificationLink($notification) }}"
               class="block px-4 py-2 text-sm text-black hover:bg-gray-100" style="color: black !important;">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bx bx-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium" style="color: black !important;">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </a>
        </li>
    @empty
        <li>
            <span class="block px-4 py-2 text-sm text-black" style="color: black !important;">
                No new notifications
            </span>
        </li>
    @endforelse

@elseif (Auth::user()->role == 'Parishioner')
    @forelse ($notifications->where('read_at', null)->take(3) as $notification)
        <li class="border-b last:border-none">
            <a href="{{ getNotificationLink($notification) }}"
               class="block px-4 py-2 text-sm text-black hover:bg-gray-100" style="color: black !important;">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bx bx-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium" style="color: black !important;">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </a>
        </li>
    @empty
        <li>
            <span class="block px-4 py-2 text-sm text-black" style="color: black !important;">
                No new notifications
            </span>
        </li>
    @endforelse
@endif

                <li class="p-2 text-center border-t">
    <label for="viewing_all_notifications" class="text-sm text-blue-500 hover:underline cursor-pointer" onclick="document.activeElement.blur()">
        View all notifications ({{ $notifications->count() }})
    </label>
</li>

            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-ghost text-xl" tabindex="0">
                <div class="avatar flex items-center me-2">
                    <div class="w-10 rounded-full">
                        <img src="{{ asset('assets/img/avatar.jpg') }}" alt="User Avatar" class="w-24 h-24 rounded-full object-cover" />

                    </div>
                    <p class="ms-2 text-sm text-black font-medium">{{ Auth::user()->name }}</p>
                </div>
                <i class='bx bx-chevron-down ms-1 text-lg'></i>
            </button>
            <ul class="dropdown-content menu p-2 shadow bg-white rounded-box w-full" style="background-color: white !important;">
                <li>
                    <a href="{{ route('profile.edit') }}"
                        class="block w-full text-left text-sm text-black hover:bg-gray-100" style="color: black !important;">{{ __('Profile') }}</a>
                </li>
                <li >
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
           class="block w-full text-left text-sm text-black hover:bg-gray-100" style="color: black !important;">
            {{ __('Log Out') }}
        </a>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
                </li>
            </ul>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notificationBell = document.getElementById('notificationBell');
        const notificationDot = document.getElementById('notification-dot');
        let hasMarkedAsRead = false;

        // Mark all notifications as read when bell is clicked
        if (notificationBell) {
            notificationBell.addEventListener('click', function () {
                if (hasMarkedAsRead) return;
                
                hasMarkedAsRead = true;
                fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && notificationDot) {
                        notificationDot.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error marking notifications as read:', error));
            });
        }
    });
</script>


<!-- Modal for Viewing All Notifications -->
<input type="checkbox" id="viewing_all_notifications" class="modal-toggle" />
<div class="modal modal-open:bg-black/50">
    <div class="modal-box w-11/12 max-w-3xl bg-white shadow-2xl rounded-lg p-0">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class='bx bxs-bell text-white text-2xl'></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">All Notifications</h3>
                        <p class="text-blue-100 text-sm">{{ $notifications->count() }} total notifications</p>
                    </div>
                </div>
                <label for="viewing_all_notifications" class="btn btn-ghost btn-sm btn-circle text-white hover:bg-white/20">
                    <i class='bx bx-x text-2xl'></i>
                </label>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4">
            <ul class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                @forelse ($notifications->take(8) as $notification)
                    <li class="bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow {{ $notification->read_at ? 'opacity-70' : '' }}">
                        <a href="{{ getNotificationLink($notification) }}" 
                           class="block p-4 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 flex-1">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $notification->read_at ? 'bg-gray-100' : 'bg-blue-50' }}">
                                            @if($notification->type === 'Request')
                                                <i class='bx bx-file text-lg {{ $notification->read_at ? "text-gray-400" : "text-blue-600" }}'></i>
                                            @elseif($notification->type === 'Donation')
                                                <i class='bx bx-donate-heart text-lg {{ $notification->read_at ? "text-gray-400" : "text-green-600" }}'></i>
                                            @elseif($notification->type === 'Payment')
                                                <i class='bx bx-money text-lg {{ $notification->read_at ? "text-gray-400" : "text-yellow-600" }}'></i>
                                            @elseif($notification->type === 'Announcement')
                                                <i class='bx bx-megaphone text-lg {{ $notification->read_at ? "text-gray-400" : "text-purple-600" }}'></i>
                                            @else
                                                <i class='bx bx-info-circle text-lg {{ $notification->read_at ? "text-gray-400" : "text-blue-600" }}'></i>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                {{ $notification->type === 'Request' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $notification->type === 'Donation' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $notification->type === 'Payment' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $notification->type === 'Announcement' ? 'bg-purple-100 text-purple-700' : '' }}">
                                                {{ $notification->type }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900 mb-1">{{ $notification->message }}</p>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <i class='bx bx-time-five'></i>
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Unread Indicator -->
                                @if(!$notification->read_at)
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                @endif
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                            <i class='bx bx-bell-off text-3xl text-gray-400'></i>
                        </div>
                        <p class="text-gray-500 font-medium">No notifications available</p>
                        <p class="text-gray-400 text-sm mt-1">You're all caught up!</p>
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg border-t border-gray-200">
            <div class="flex items-center justify-end">
                <label for="viewing_all_notifications" class="btn bg-blue-600 hover:bg-blue-700 text-white border-none">
                    <i class='bx bx-check mr-2'></i>
                    Close
                </label>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Individual Notification Details -->
<input type="checkbox" id="notification_details_modal" class="modal-toggle" />
<div class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
        <h3 class="text-lg font-bold">Notification Details</h3>
        <div class="py-4">
            <p id="notificationMessage" class="text-sm text-black" style="color: black !important;"></p>
            <p id="notificationTime" class="text-xs text-gray-500"></p>
        </div>
        <div class="modal-action">
            <label for="notification_details_modal" class="btn">Close</label>
        </div>
    </div>
</div>
