<script>
	const url = "{{ $resourceUrl  }}";
	const updateDatesUrl = "{{ route('admin.edt.update-dates') }}";
	const checkAvailabilityUrl = "{{ route('admin.edt.check-availability') }}";
	const updateEdtUrl = "{{ route('admin.edt.update') }}";
	const token = document.getElementsByName('_token')[0].value;

	// const calendarOffCanvas = new bootstrap.Offcanvas('#calendar-add_edit_event');
	// const calendarCreteOffCanvas = new bootstrap.Offcanvas('#calendar-create_event');
	const calendarModal = new bootstrap.Modal('#calendar-modal');
	let selectedEvent = '';

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
		buttonText: {
			today:    'Aujourd\'hui',
			month:    'Mois',
			week:     'Semaine',
			day:      'Jour',
			list:     'liste',
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
		// defaultView: 'timeGridWeek',
		// views: {
		// 	timeGrid: {
		// 		slotLabelFormat: [
		// 			{month: 'short', day: 'numeric', weekday: 'short'},
		// 		]
		// 	},
		// 	dayGrid: {
		// 		slotLabelFormat: [
		// 			{hour: 'numeric', minute: '2-digit', omitZeroMinute: false} // Format de l'heure
		// 		]
		// 	}
		// },
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
			// calendarOffCanvas.show();
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

			calendarModal.show();
		},
		eventDrop: function (info) {
			let droppedElement = info.event;
			$.post(updateDatesUrl, {
				_token: token,
				_method: 'PUT',
				debut: droppedElement.start.toISOString(),
				fin: droppedElement.end.toISOString(),
				slug: droppedElement.extendedProps.slug,
			}).then(response => {
				console.log(response);
				showToast(response.message, 'success');
			}).catch((error) => {
				console.log(error)
			});
		}
	});

	calendar.render();
	document.addEventListener('DOMContentLoaded', function () {
		let calbtn = document.querySelectorAll('.fc-toolbar-chunk');
		for (let t = 0; t < calbtn.length; t++) {
			let c = calbtn[t];
			c.children[0].classList.remove('btn-group');
			c.children[0].classList.add('d-inline-flex');
		}
	});

	// Suppression
	let pc_event_remove = document.querySelector('#pc_event_remove');
	if (pc_event_remove) {
		pc_event_remove.addEventListener('click', function () {
			const swalWithBootstrapButtons = Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-light-success',
					cancelButton: 'btn btn-light-danger'
				},
				buttonsStyling: false
			});

			$('#calendar-modal').modal('hide')
			swalWithBootstrapButtons
				.fire({
					title: 'Voulez-vous supprimer cet élément?',
					text: 'Cette action sera irréversible',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Oui, supprimer!',
					cancelButtonText: 'Non, annuler!',
					reverseButtons: true
				})
				.then((result) => result.isConfirmed && handleEventDeleting());
		});
	}

	// Édition
	let pc_event_edit = document.querySelector('#pc_event_edit');
	if (pc_event_edit) {
		pc_event_edit.addEventListener('click', function () {
			const props = selectedEvent.extendedProps;
			console.log(props);
			document.getElementById('create-debut').setAttribute("value", `${props.start.slice(11)}`)
			document.getElementById('create-fin').setAttribute("value", `${props.end.slice(11)}`)
			document.getElementById('create-grade-id').value = (props.groupId ?? props.grade_id);
			let t = props.date.slice(0, 11).toString();
			document.getElementById('create-date').value = `${t}`;
			console.log(document.getElementById('create-date').value)
			document.getElementById('create-uv-id').value = props.uv_id
			document.getElementById('create-type').value = props.type;
			document.getElementById('create-salle-id').value = props.salle_id;
			document.getElementById('create-teacher-id').value = props.teacher;
			// document.getElementById('create-details').value = selectedEvent.
		});
	}

	//  get round value
	function getRound(vale) {
		let tmp;
		if (vale < 10) {
			tmp = '0' + vale;
		} else {
			tmp = vale;
		}
		return tmp;
	}

	//  get time
	function getTime(temp) {
		temp = new Date(temp);
		if (temp.getHours() != null) {
			let hour = temp.getHours();
			let minute = temp.getMinutes() ? temp.getMinutes() : 0o0;
			return hour + ':' + minute;
		}
	}

	//  get date
	function dateformat(dt) {
		let mn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		let d = new Date(dt),
			month = '' + mn[d.getMonth()],
			day = '' + d.getDate(),
			year = d.getFullYear();
		if (month.length < 2) month = '0' + month;
		if (day.length < 2) day = '0' + day;
		return [day + ' ' + month, year].join(',');
	}

	//  get full date
	function timeformat(time) {
		let temp = time.split(':');
		let hours = temp[0];
		let minutes = temp[1];
		let newformat = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12;
		hours = hours ? hours : 12;
		minutes = minutes < 10 ? '0' + minutes : minutes;
		return hours + ':' + minutes + ' ' + newformat;
	}

	const salleId = document.getElementById('create-salle-id').value;

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
			}
		};
	}

	function addEvents(events) {
		console.log(events);
		events.forEach(event => addEvent(event));
	}

	const handleEventCreateOrUpdateSubmitForm = () => {
		let action = "{{ route('admin.edt.store') }}";

		let event = {
			debut: document.getElementById('create-debut').value,
			grade: document.getElementById('create-grade-id').value,
			fin: document.getElementById('create-fin').value,
			date: document.getElementById('create-date').value,
			uv_id: document.getElementById('create-uv-id').value,
			type: document.getElementById('create-type').value,
			salle: (document.getElementById('create-salle-id')?.value || salleId),
			teacher: document.getElementById('create-teacher-id').value,
			details: document.getElementById('create-details').value,
			// eventId: document.getElementById('create-ev'),
			_token: token,
		};

		// If an edtId exists, perform an update instead of create
		const edtIdInput = document.getElementById('edtId');
		const isUpdate = !!(edtIdInput && edtIdInput.value);
		const slug = (selectedEvent?.extendedProps?.slug) || (edtIdInput?.value);

		const ensureAvailability = () => $.get(checkAvailabilityUrl, { salle: event.salle, date: event.date, debut: event.debut, fin: event.fin });

		if (isUpdate && slug) {
			const payload = {
				_token: token,
				_method: 'PUT',
				slug: slug,
				salle: event.salle,
				date: event.date,
				debut: event.debut,
				fin: event.fin,
				uv_id: event.uv_id,
				type: event.type,
				grade: event.grade,
				teacher: event.teacher,
				details: event.details,
			};

			ensureAvailability()
				.then(av => {
					if (!av.available) {
						showToast(av.message || 'Salle occupée', 'danger');
						return Promise.reject('occupied');
					}
					return $.ajax({ url: updateEdtUrl, method: 'POST', data: payload });
				})
				.then(resp => {
					showToast('Programmation mise à jour', 'success');
					// Replace existing event on calendar
					if (selectedEvent) selectedEvent.remove();
					addEvent(resp.data);
				})
				.catch(error => {
					const body = error?.responseJSON;
					if (body?.message) {
						showToast(body.message, 'danger');
					}
				});
			return;
		}

		// Create flow with availability check
		ensureAvailability()
			.then(av => {
				if (!av.available) {
					showToast(av.message || 'Salle occupée', 'danger');
					return Promise.reject('occupied');
				}
				return $.post(action, event)
			})
			.then(response => {
					console.log(response)
					showToast('Planification ajoutée avec succès', 'success');
					addEvent(response.data)
					$('#calendar-create_event-close_button').modal('hide');
				}
			).catch(error => {
				const body = error?.responseJSON;
				if (body && body.code === 422) {
					showToast(body.message, 'danger');
				}
			});
	}

	const handleEventsLoading = () => {
		$.get(url).then(response => {
			showToast('Données chargées avec succès', 'success');
			addEvents(response.data);
		}).catch(error => {
			console.log(error)
			const body = error.responseJSON;
			// if (body.code === 422) {
			showToast(body.message, 'danger');
		})
	}

	const handleEventDeleting = () => {
		let slug = selectedEvent.extendedProps.slug;
		$.post("{{ route('admin.edt.delete') }}", {
			_token: token,
			_method: 'delete',
			slug: slug
		}).then(response => {
			selectedEvent.remove();
			showToast(response.message, 'success');
		}).catch(error => {
			showToast(error.responseJSON.message, 'danger');
		})
	}

	$('#handle-event-create-submit-form').click(handleEventCreateOrUpdateSubmitForm)
	$(document).ready(handleEventsLoading)

</script>