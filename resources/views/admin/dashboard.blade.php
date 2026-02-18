@extends('layouts.admin_layout')
@section('page_title') Admin Dashboard @endsection
@section('content')

    <x-admin.dashboard 
        :active-admins="$activeAdmins" 
        :inactive-admins="$inactiveAdmins" 
    />
   @can('view-online-admins')
    <script>
        function renderList(containerId, admins, isActive) {

            let html = '';

            admins.forEach(admin => {

                html += `
                    <div class="card mb-2 p-2">
                        <strong>${admin.surname}</strong>
                        ${isActive ? '<span style="color:green;">● Online</span>' 
                                   : '<span style="color:gray;">● Offline</span>'}
                    </div>
                `;
            });

            document.getElementById(containerId).innerHTML = html;
        }

        function fetchLiveActivity() {

            fetch("{{ route('admin.activity.live') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {

                // Update count
                document.getElementById('active-count').innerText = data.active_count;

                // Update lists
                renderList('active-admins-container', data.active, true);
                renderList('inactive-admins-container', data.inactive, false);

            })
            .catch(error => console.error('Live update error:', error));
        }

        // Run every 20 seconds
        setInterval(fetchLiveActivity, 20000);
        </script>
    @endcan

@endsection