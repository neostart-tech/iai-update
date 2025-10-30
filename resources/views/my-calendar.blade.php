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
		let date = new Date();
		let d = date.getDate();
		let m = date.getMonth();
		let y = date.getFullYear();

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
				document.getElementById('pc-e-sdate').value = sdt.getFullYear() + '-' + getRound(sdt.getMonth() + 1) + '-' + getRound(sdt.getDate());
				document.getElementById('pc-e-edate').value = edt.getFullYear() + '-' + getRound(edt.getMonth() + 1) + '-' + getRound(edt.getDate());
				calendarOffCanvas.show();
				calendar.unselect();
			},
			eventClick: function (info) {
				selectedEvent = info.event;
				const props = info.event.extendedProps;
				let e_title = selectedEvent.title === undefined ? '' : selectedEvent.title;
				document.getElementById('type-ev').innerHTML = props.type;
				let e_desc = selectedEvent.extendedProps.description === undefined ? '' : selectedEvent.extendedProps.description;
				let e_venue = selectedEvent.extendedProps.description === undefined ? '' : selectedEvent.extendedProps.venue;
				document.getElementById('charge-du-cours').innerText = props.teacher;
				document.querySelector('.calendar-modal-title').innerHTML = e_title;
				document.querySelector('.pc-event-title').innerHTML = e_title;
				document.querySelector('.pc-event-description').innerHTML = e_desc;
				document.querySelector('.pc-event-date').innerHTML = props.plageHoraire;
				document.querySelector('.pc-event-venue').innerHTML = e_venue;
				document.getElementById('show-group-id').innerText = props.group;
				if (props.isControllable) {
					let controlButton = document.getElementById('control-button');
					controlButton.style.display = 'block';
					controlButton.addEventListener('click', () => document.location.href = props.controlUrl);
				}
				(new bootstrap.Modal('#calendar-modal')).show();
			}
		});

		calendar.render();
		$(document).ready(() => {
			$.get('{{ route('load-calendar') }}').then(response => {
				showToast('Données chargées avec succès', 'success');
				if (response.data.length === 0)
					showToast('Aucune donnée à afficher', 'danger');
				else
					addEvents(response.data);
			}).catch(error => {
				showToast(error.responseJSON.message, 'danger');
			})
		})

		function addEvents(events) {
			events.forEach(event => addEvent(event));
		}

		function addEvent(newEvent) {
			calendar.addEvent(parseIntoCalendarEvent(newEvent));
		}

		function parseIntoCalendarEvent(event) {
			return {
				title: event.title,
				start: event.debut,
				end: event.fin,
				allDay: false,
				description: event.details,
				venue: event.salle,
				className: `event-${event.color}`,
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
					controlUrl: event.control_url
				}
			};
		}
	</script>
@endsection

