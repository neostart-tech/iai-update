@extends('base', [
'title' => 'Mon agenda',
'page_name' => 'Mon agenda',
'breadcrumbs' => ['Mon dashboard', 'Mon agenda']
])

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" id="openAgendaModalBtn">
                    <i class="bi bi-plus-circle me-1"></i> Nouvel événement
                </button>
            </div>
        </div>
        <div class="card-body position-relative">
            <div id="calendar" class="calendar"></div>
        </div>
    </div>
</div>

@include('admin.agenda._form-modal')
@endsection

@section('other-js')
<script src="{{ asset('admin/assets/js/plugins/index.global.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'fr',
            themeSystem: 'bootstrap',
            eventDisplay: 'block',
            eventColor: '#ECFAFB',
            eventTextColor: '#3EC9D6',
            nowIndicator: true,
            slotMinTime: '07:00:00',
            slotMaxTime: '21:00:00',

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            selectable: true,
            editable: true,
            navLinks: true,
            dayMaxEvents: true,

            events: function(fetchInfo, successCallback, failureCallback) {
                fetch('{{ route("admin.agenda.get") }}')
                    .then(response => response.json())
                    .then(data => {
                        const events = data.data.map(event => ({
                            id: event.id,
                            title: event.title,
                            start: event.start_time,
                            end: event.end_time,
                            description: event.description || '' // ajouter description si existante
                        }));
                        successCallback(events);
                    })
                    .catch(error => {
                        console.error(error);
                        failureCallback(error);
                    });
            },

            select: function(info) {
                document.getElementById('agendaForm').reset();
                document.getElementById('agenda_id').value = '';
                const modal = new bootstrap.Modal(document.getElementById('agendaModal'));
                modal.show();
            },

            eventClick: function(info) {
                const modal = new bootstrap.Modal(document.getElementById('agendaModal'));
                modal.show();

                document.getElementById('agenda_id').value = info.event.id;
                document.getElementById('titre').value = info.event.title;
                document.getElementById('description').value = info.event.extendedProps.description || '';
                document.getElementById('start_time').value = info.event.startStr.substring(0, 16);
                document.getElementById('end_time').value = info.event.endStr.substring(0, 16);
            }
        });

        calendar.render();

        // Ouvrir modal "Nouvel événement"
        document.getElementById('openAgendaModalBtn').addEventListener('click', function() {
            document.getElementById('agendaForm').reset();
            document.getElementById('agenda_id').value = '';
            const modal = new bootstrap.Modal(document.getElementById('agendaModal'));
            modal.show();
        });

        // Soumission formulaire
        document.getElementById('agendaForm').addEventListener('submit', async function(e) {
             const modal = bootstrap.Modal.getInstance(document.getElementById('agendaModal'));
                modal.hide();
            e.preventDefault();

            const id = document.getElementById('agenda_id').value;
            const url = id ? `/administration/agenda/${id}/modifier` : '{{ route("admin.agenda.store") }}';
            const method = id ? 'POST' : 'POST';

            const formData = new FormData(this);

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                if (!res.ok) throw new Error('Erreur lors de l\'enregistrement');

               

                calendar.refetchEvents();
                Swal.fire('Succès', 'Événement enregistré avec succès', 'success');

            } catch (error) {
                console.error(error);
                Swal.fire('Erreur', 'Impossible d\'enregistrer l\'événement', 'error');
            }
        });

        // Supprimer un événement
        document.getElementById('deleteEventBtn').addEventListener('click', async function() {
            const modal = bootstrap.Modal.getInstance(
                document.getElementById('agendaModal')
            );
            modal.hide();
            const id = document.getElementById('agenda_id').value;
            if (!id) return;

            const result = await Swal.fire({
                title: 'Confirmation',
                text: 'Voulez-vous vraiment supprimer cet événement ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`/administration/agenda/${id}/destroy`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) {
                    throw new Error('Erreur lors de la suppression');
                }




                // Rafraîchir le calendrier
                calendar.refetchEvents();

                Swal.fire({
                    icon: 'success',
                    title: 'Supprimé',
                    text: 'L’événement a été supprimé avec succès',
                    timer: 2000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de supprimer l’événement'
                });
            }
        });

    });
</script>


@endsection