@extends('base', [
	'title' => 'Mon emploi du temps',
	'page_name' => 'Mon emploi du temps',
	'breadcrumbs' => ['Mon dashboard', 'Mon emploi du temps']
])

@section('content')
	<div class="col-12">
		<div class="card">
			<div class="card-body position-relative">
				<div id="calendar" class="calendar"></div>
			</div>
		</div>
	</div>
	@include('calendar._show-modal')
@endsection

@section('other-js')
<script src="{{ asset('admin/assets/js/plugins/index.global.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>

<script>
    function getRound(num) {
        return num < 10 ? '0' + num : num;
    }

  

    // Initialisation du calendrier
    let calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        noEventsMessage: 'Aucun événement à afficher',
        buttonText: {
            today: 'Aujourd\'hui',
            month: 'Mois',
            week: 'Semaine',
            day: 'Jour',
            list: 'liste',
        },
        weekends: false,
        locale: 'fr',
        themeSystem: 'bootstrap',
        slotDuration: '00:10:00',
        navLinks: true,
        height: 'auto',
        droppable: true,
        selectable: true,
        selectMirror: true,
        slotMinTime: '07:30:00',
        slotMaxTime: '18:00:00',
        initialView: 'timeGridWeek',
        editable: true,
        dayMaxEvents: true,
        handleWindowResize: true,
        select: function (info) {
            let sdt = new Date(info.start);
            let edt = new Date(info.end);
            // Vérifiez si ces éléments existent dans votre modal
            if (document.getElementById('pc-e-sdate')) {
                document.getElementById('pc-e-sdate').value = sdt.getFullYear() + '-' + getRound(sdt.getMonth() + 1) + '-' + getRound(sdt.getDate());
            }
            if (document.getElementById('pc-e-edate')) {
                document.getElementById('pc-e-edate').value = edt.getFullYear() + '-' + getRound(edt.getMonth() + 1) + '-' + getRound(edt.getDate());
            }
            calendar.unselect();
        },
        eventClick: function (info) {
            const selectedEvent = info.event;
            const props = info.event.extendedProps;
            
            let e_title = selectedEvent.title || '';
            let e_desc = props.description || '';
            let e_venue = props.salle || '';

            // Mise à jour du modal avec les données de l'événement
            if (document.getElementById('type-ev')) {
                document.getElementById('type-ev').innerHTML = props.type || '';
            }
            if (document.getElementById('charge-du-cours')) {
                document.getElementById('charge-du-cours').innerText = props.teacher || '';
            }
            
            const modalTitle = document.querySelector('.calendar-modal-title');
            const eventTitle = document.querySelector('.pc-event-title');
            const eventDesc = document.querySelector('.pc-event-description');
            const eventDate = document.querySelector('.pc-event-date');
            const eventVenue = document.querySelector('.pc-event-venue');
            const eventGroup = document.getElementById('show-group-id');

            if (modalTitle) modalTitle.innerHTML = e_title;
            if (eventTitle) eventTitle.innerHTML = e_title;
            if (eventDesc) eventDesc.innerHTML = e_desc;
            if (eventDate) eventDate.innerHTML = props.plageHoraire || '';
            if (eventVenue) eventVenue.innerHTML = e_venue;
            if (eventGroup) eventGroup.innerText = props.group || '';

            // Gestion du bouton de contrôle
            const controlButton = document.getElementById('control-button');
            if (controlButton) {
                if (props.isControllable) {
                    controlButton.style.display = 'block';
                    controlButton.onclick = () => { window.location.href = props.controlUrl || '#'; };
                } else {
                    controlButton.style.display = 'none';
                }
            }

            // Affichage du modal
            const calendarModal = new bootstrap.Modal(document.getElementById('calendar-modal'));
            calendarModal.show();
        }
    });

    calendar.render();

    $(document).ready(function() {
        
        $.ajax({
            url: '{{ route('load-calendar') }}',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                
                if (response.data && response.data.length > 0) {
                    showToast('Données chargées avec succès', 'success');
                    addEvents(response.data);
                } else {
                    showToast('Aucune donnée à afficher', 'warning');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors du chargement:', error);
                showToast('Erreur lors du chargement des données', 'error');
            
            }
        });
    });

    function addEvents(events) {
        console.log('Ajout des événements:', events);
        events.forEach(event => addEvent(event));
    }

    function addEvent(newEvent) {
        try {
            const calendarEvent = parseIntoCalendarEvent(newEvent);
            calendar.addEvent(calendarEvent);
            console.log('Événement ajouté:', calendarEvent);
        } catch (error) {
            console.error('Erreur lors de l\'ajout de l\'événement:', error, newEvent);
        }
    }

    function parseIntoCalendarEvent(event) {
        return {
            title: event.title || 'Sans titre',
            start: event.debut,
            end: event.fin,
            allDay: false,
            description: event.details || '',
            venue: event.salle || '',
            className: `event-${event.color || 'secondary'}`,
            extendedProps: {
                teacher: event.teacher,
                teacher_id: event.teacher_id,
                salle: event.salle,
                salle_id: event.salle_id,
                type: event.type,
                uv: event.uv,
                uv_id: event.uv_id,
                group: event.group,
                groupId: event.group_id,
                start: event.debut,
                end: event.fin,
                plageHoraire: event.plageHoraire,
                slug: event.slug,
                date: event.date,
                isControllable: event.is_controllable,
                adminMember: event.admin_member,
                controlUrl: event.control_url,
                description: event.details
            }
        };
    }
</script>
@endsection

