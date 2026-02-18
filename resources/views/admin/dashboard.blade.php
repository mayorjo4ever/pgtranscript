@extends('layouts.admin_layout')
@section('page_title') Admin Dashboard @endsection
@section('content')

    <x-admin.dashboard 
        :active-admins="$activeAdmins" 
        :inactive-admins="$inactiveAdmins" 
    />
   @can('view-online-admins')
   <script>
    function renderAdmins(containerId, admins, isActive) {

        let html = '<div class="row">';

        admins.forEach(admin => {

            let badge = isActive
                ? '<span class="online-dot"></span>'
                : '<span class="badge bg-secondary">Offline</span>';

            html += `
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-start border-${isActive ? 'success' : 'secondary'} border-4">
                        <div class="card-body">
                            <h6>
                                ${admin.surname}
                                ${badge}
                            </h6>

                            <small class="text-muted d-block">
                                Last Login:
                                ${admin.last_login_at ?? 'N/A'}
                            </small>

                            <small class="text-muted d-block">
                                IP:
                                ${admin.last_login_ip ?? 'N/A'}
                            </small>

                            <small class="text-muted">
                                Last Seen:
                                ${admin.last_seen_at ?? 'Never'}
                            </small>

                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';

        document.getElementById(containerId).innerHTML = html;
    }

    function fetchLiveActivity() {

        fetch("{{ route('admin.activity.live') }}")
            .then(response => response.json())
            .then(data => {

                document.getElementById('active-count').innerText = data.active_count;

                renderAdmins('active-admins-container', data.active, true);
                renderAdmins('inactive-admins-container', data.inactive, false);

            })
            .catch(error => console.error(error));
    }

    setInterval(fetchLiveActivity, 20000);
    </script>

    @endcan

@endsection