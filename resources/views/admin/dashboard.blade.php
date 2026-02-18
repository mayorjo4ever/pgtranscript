@extends('layouts.admin_layout')
@section('page_title') Admin Dashboard @endsection
@section('content')

    <x-admin.dashboard 
        :active-admins="$activeAdmins" 
        :inactive-admins="$inactiveAdmins" 
    />
   @push('script')
   
    <script>
        alert('i make request');
        
        function fetchLiveActivity() {
            fetch("{{ route('admin.activity.live') }}")
                .then(response => response.json())
                .then(data => {

                    console.log(data); // 👈 DEBUG

                    document.getElementById('active-count').innerText = data.active_count;

                    renderAdmins('active-admins-container', data.active, true);
                    renderAdmins('inactive-admins-container', data.inactive, false);

                })
                .catch(error => console.error('Error:', error));
        }
    
        setInterval(fetchLiveActivity, 20000);
    
    function renderAdmins(containerId, admins, type) {
        let html = '';

        admins.forEach(admin => {

            let badge = type === 'active'
                ? `<span class="online-dot"></span>`
                : `<span class="badge bg-secondary">Offline</span>`;

            html += `
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-start border-${type === 'active' ? 'success' : 'secondary'} border-4">
                        <div class="card-body">
                            <h6>${admin.surname} ${badge}</h6>
                            <small class="text-muted">
                                Last seen: ${admin.last_seen_at ?? 'Never'}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById(containerId).innerHTML =
            `<div class="row">${html}</div>`;
    }
 
    </script>
    @endpush
@endsection