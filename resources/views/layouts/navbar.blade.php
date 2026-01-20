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
            <button class="btn btn-ghost text-xl text-black" tabindex="0" title="Notifications">
                <i class='bx bxs-bell text-black'></i>
                                @if ($notifications->count() > 0)
                    <span id="notification-dot"
      class="absolute top-0 right-0 inline-block w-3 h-3 bg-red-600 rounded-full"></span>

                @elseif (Auth::user()->role == 'Parishioner' &&
                        $notifications->whereIn('type', ['request', 'donation', 'announcement'])->count() > 0)
                    <span id="notification-dot"
      class="absolute top-0 right-0 inline-block w-3 h-3 bg-red-600 rounded-full"></span>

                @endif
            </button>
            <ul class="dropdown-content menu p-2 shadow bg-white rounded-box w-64 -ml-12" style="background-color: white !important;">
                <li class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-black" style="color: black !important;">Notifications</h3>
                </li>
                @if (Auth::user()->role == 'Admin')
    @forelse ($notifications as $notification)
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
    @forelse ($notifications->whereIn('type', ['Payment', 'Request', 'Donation', 'Announcement']) as $notification)
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
    <a href="javascript:void(0)"
       id="viewAllNotifications"
       class="text-sm text-blue-500 hover:underline">
        View all notifications
    </a>
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
        const viewAllBtn = document.getElementById('viewAllNotifications');
        const notificationDot = document.getElementById('notification-dot');

        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', function () {
                if (notificationDot) {
                    notificationDot.style.display = 'none';
                }
            });
        }
    });
</script>


<!-- Modal for Viewing All Notifications -->
<input type="checkbox" id="viewing_all_notifications" class="modal-toggle" />
<div class="modal">
    <div class="modal-box w-11/12 max-w-5xl">
        <h3 class="text-lg font-bold">All Notifications</h3>
        <ul class="py-4 max-h-96 overflow-y-auto">
            @forelse ($notifications as $notification)
                <li class="border-b last:border-none py-2">
                    <a href="{{ getNotificationLink($notification) }}"
                        class="block px-4 py-2 text-sm text-black hover:bg-gray-100" style="color: black !important;">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3"
                                <p class="text-sm font-medium" style="color: black !important;">{{ $notification->message }}</p>
                                <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </a>
                </li>
            @empty
                <li>
                    <span class="block px-4 py-2 text-sm text-black" style="color: black !important;">No notifications available</span>
                </li>
            @endforelse
        </ul>
        <div class="modal-action">
            <label for="viewing_all_notifications" class="btn">Close</label>
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
