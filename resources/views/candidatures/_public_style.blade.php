<style>
	:root {
		--iai-brand-950: #022c22;
		--iai-brand-900: #064e3b;
		--iai-brand-800: #065f46;
		--iai-brand-700: #047857;
		--iai-brand-600: #059669;
		--iai-brand-50: #ecfdf5;
		--iai-gold-400: #facc15;
		--iai-gold-300: #fde047;
		--iai-gold-200: #fef08a;
		--iai-slate-950: #020617;
		--iai-slate-800: #1e293b;
		--iai-slate-600: #475569;
		--iai-slate-200: #e2e8f0;
		--iai-slate-100: #f1f5f9;
		--iai-white: #ffffff;
		--iai-radius-xl: 1.35rem;
		--iai-shadow-lg: 0 24px 70px rgba(15, 23, 42, 0.14);
	}

	body[data-pc-theme="light"] {
		min-height: 100vh;
		background:
			radial-gradient(circle at top left, rgba(253, 224, 71, 0.16), transparent 30rem),
			linear-gradient(135deg, #f8fafc 0%, #ecfdf5 48%, #ffffff 100%) !important;
		color: var(--iai-slate-800);
	}

	.auth-main {
		min-height: 100vh;
		padding: 1.5rem;
		background:
			linear-gradient(90deg, rgba(6, 78, 59, 0.05), rgba(250, 204, 21, 0.06)),
			transparent !important;
	}

	.auth-wrapper.v3 {
		width: min(1180px, 100%);
		min-height: calc(100vh - 3rem);
		margin: 0 auto;
		display: grid;
		grid-template-columns: minmax(0, 1.15fr) minmax(22rem, 0.85fr);
		align-items: stretch;
		gap: 1.5rem;
	}

	.auth-wrapper.v3 .auth-form {
		width: 100%;
		max-width: none;
		padding: 0 !important;
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	.iai-candidature-topbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
		margin-bottom: 1.15rem;
		padding: 0.75rem;
		border: 1px solid rgba(6, 95, 70, 0.12);
		border-radius: 1.25rem;
		background: rgba(255, 255, 255, 0.86);
		box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
		backdrop-filter: blur(18px);
	}

	.iai-candidature-brand {
		display: inline-flex;
		align-items: center;
		gap: 0.85rem;
		color: var(--iai-brand-950);
		text-decoration: none;
	}

	.iai-candidature-brand img {
		width: 3.2rem;
		height: 3.2rem;
		object-fit: contain;
		border-radius: 0.8rem;
		background: #fffbea;
		padding: 0.2rem;
		box-shadow: inset 0 0 0 1px rgba(250, 204, 21, 0.45);
	}

	.iai-candidature-brand strong {
		display: block;
		font-size: 1.1rem;
		font-weight: 900;
		letter-spacing: -0.02em;
		line-height: 1.1;
	}

	.iai-candidature-brand span {
		display: block;
		margin-top: 0.1rem;
		font-size: 0.72rem;
		font-weight: 800;
		letter-spacing: 0.12em;
		text-transform: uppercase;
		color: var(--iai-slate-600);
	}

	.iai-candidature-progress {
		display: inline-flex;
		align-items: center;
		gap: 0.55rem;
		padding: 0.55rem 0.8rem;
		border-radius: 999px;
		background: var(--iai-brand-50);
		color: var(--iai-brand-900);
		font-size: 0.9rem;
		font-weight: 800;
		white-space: nowrap;
	}

	.iai-candidature-progress b {
		display: grid;
		place-items: center;
		min-width: 1.85rem;
		height: 1.85rem;
		border-radius: 999px;
		background: var(--iai-brand-800);
		color: #fff;
		font-size: 0.95rem;
	}

	.card.my-5 {
		margin: 0 !important;
		border: 1px solid rgba(15, 23, 42, 0.08) !important;
		border-radius: var(--iai-radius-xl) !important;
		box-shadow: var(--iai-shadow-lg) !important;
		overflow: hidden;
		background: rgba(255, 255, 255, 0.96) !important;
	}

	.card.my-5 .card-body {
		padding: clamp(1.35rem, 3vw, 2.25rem) !important;
	}

	.tab-content h3,
	.tab-pane h3 {
		color: var(--iai-slate-950);
		font-weight: 900;
		letter-spacing: -0.03em;
	}

	.iai-welcome {
		text-align: left;
	}

	.iai-kicker {
		display: inline-flex;
		align-items: center;
		gap: 0.45rem;
		margin-bottom: 0.9rem;
		padding: 0.35rem 0.7rem;
		border-radius: 999px;
		background: #fef9c3;
		color: #854d0e;
		font-size: 0.78rem;
		font-weight: 900;
		letter-spacing: 0.13em;
		text-transform: uppercase;
	}

	.iai-welcome h1 {
		margin: 0;
		color: var(--iai-brand-950);
		font-size: clamp(2rem, 4vw, 3.25rem);
		font-weight: 950;
		letter-spacing: -0.055em;
		line-height: 1.03;
	}

	.iai-welcome p {
		max-width: 40rem;
		margin: 1rem 0 0;
		color: var(--iai-slate-600);
		font-size: 1.02rem;
		line-height: 1.75;
	}

	.alert.alert-warning {
		margin-top: 1.25rem;
		border: 1px solid rgba(250, 204, 21, 0.55) !important;
		border-radius: 1rem !important;
		background: #fffbeb !important;
		color: #713f12 !important;
	}

	.form-label {
		color: var(--iai-slate-800);
		font-weight: 800;
	}

	.form-control,
	.form-select,
	.choices__inner {
		min-height: 3rem;
		border: 1px solid #cbd5e1 !important;
		border-radius: 0.9rem !important;
		background-color: #fff !important;
		color: var(--iai-slate-950) !important;
		box-shadow: none !important;
		transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
	}

	textarea.form-control {
		min-height: 7.5rem;
	}

	.form-control:focus,
	.form-select:focus,
	.choices.is-focused .choices__inner {
		border-color: var(--iai-brand-700) !important;
		box-shadow: 0 0 0 0.22rem rgba(4, 120, 87, 0.13) !important;
	}

	.btn {
		min-height: 2.9rem;
		border-radius: 0.95rem !important;
		font-weight: 850 !important;
	}

	.btn-light,
	.btn-outline-warning,
	.btn-warning {
		border-color: transparent !important;
		background: var(--iai-gold-300) !important;
		color: var(--iai-brand-950) !important;
		box-shadow: 0 12px 24px rgba(250, 204, 21, 0.22);
	}

	.btn-light:hover,
	.btn-outline-warning:hover,
	.btn-warning:hover {
		background: var(--iai-gold-200) !important;
		color: var(--iai-brand-950) !important;
		transform: translateY(-1px);
	}

	.btn-outline-secondary {
		border-color: #cbd5e1 !important;
		color: var(--iai-slate-800) !important;
		background: #fff !important;
	}

	.btn-outline-secondary:hover {
		border-color: var(--iai-brand-700) !important;
		background: var(--iai-brand-50) !important;
		color: var(--iai-brand-900) !important;
	}

	.auth-conf,
	button[style*="#166534"] {
		border-color: var(--iai-brand-800) !important;
		background: var(--iai-brand-800) !important;
		color: #fff !important;
		box-shadow: 0 14px 30px rgba(6, 95, 70, 0.24);
	}

	.auth-conf:hover,
	button[style*="#166534"]:hover {
		background: var(--iai-brand-700) !important;
		transform: translateY(-1px);
	}

	.auth-footer {
		margin-top: 1.15rem;
		padding: 0.95rem 1.2rem;
		border-radius: 1rem;
		background: rgba(255, 255, 255, 0.78);
		color: var(--iai-slate-600);
		font-weight: 650;
	}

	.auth-sidecontent {
		min-height: 100%;
		border-radius: var(--iai-radius-xl);
		overflow: hidden;
		background:
			linear-gradient(145deg, rgba(2, 44, 34, 0.92), rgba(6, 95, 70, 0.92)),
			url("{{ asset('img/image2.png') }}") center/cover !important;
		box-shadow: var(--iai-shadow-lg);
		color: #fff;
	}

	.iai-side-panel {
		min-height: 100%;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		padding: clamp(1.5rem, 3vw, 2.5rem);
		background: linear-gradient(180deg, rgba(2, 44, 34, 0.18), rgba(2, 44, 34, 0.65));
	}

	.iai-side-pill {
		display: inline-flex;
		width: fit-content;
		align-items: center;
		gap: 0.45rem;
		padding: 0.38rem 0.75rem;
		border-radius: 999px;
		background: rgba(250, 204, 21, 0.18);
		color: var(--iai-gold-300);
		font-size: 0.78rem;
		font-weight: 900;
		letter-spacing: 0.13em;
		text-transform: uppercase;
	}

	.iai-side-panel h2 {
		margin: 1.2rem 0 0;
		color: #fff;
		font-size: clamp(2rem, 4vw, 3.35rem);
		font-weight: 950;
		line-height: 1.02;
		letter-spacing: -0.055em;
	}

	.iai-side-panel p {
		color: rgba(255, 255, 255, 0.78);
		line-height: 1.75;
	}

	.iai-side-list {
		display: grid;
		gap: 0.85rem;
		margin: 1.6rem 0;
		padding: 0;
		list-style: none;
	}

	.iai-side-list li {
		display: flex;
		gap: 0.75rem;
		align-items: flex-start;
		padding: 0.85rem;
		border: 1px solid rgba(255, 255, 255, 0.12);
		border-radius: 1rem;
		background: rgba(255, 255, 255, 0.08);
		backdrop-filter: blur(10px);
	}

	.iai-side-list span {
		display: grid;
		place-items: center;
		flex: 0 0 auto;
		width: 1.65rem;
		height: 1.65rem;
		border-radius: 999px;
		background: var(--iai-gold-300);
		color: var(--iai-brand-950);
		font-weight: 950;
	}

	.iai-side-contact {
		margin-top: 1.5rem;
		padding-top: 1.2rem;
		border-top: 1px solid rgba(255, 255, 255, 0.16);
		color: rgba(255, 255, 255, 0.82);
		font-size: 0.95rem;
	}

	.iti {
		width: 100%;
	}

	.swal2-confirm {
		background: var(--iai-brand-800) !important;
	}

	.swal2-cancel {
		background: #64748b !important;
	}

	@media (max-width: 991.98px) {
		.auth-main {
			padding: 1rem;
		}

		.auth-wrapper.v3 {
			grid-template-columns: 1fr;
			min-height: auto;
		}

		.auth-sidecontent {
			order: -1;
			min-height: auto;
		}

		.iai-side-panel {
			min-height: auto;
		}

		.iai-side-panel h2 {
			font-size: 2.1rem;
		}

		.iai-candidature-topbar {
			flex-direction: column;
			align-items: stretch;
		}

		.iai-candidature-progress {
			justify-content: center;
		}
	}

	@media (max-width: 575.98px) {
		.card.my-5 .card-body {
			padding: 1rem !important;
		}

		.iai-welcome h1 {
			font-size: 2rem;
		}

		.iai-candidature-brand span {
			display: none;
		}
	}

/* Validation frontend des étapes de candidature */
.iai-step-error-summary {
	margin: 1rem 0 1.2rem;
	padding: 0.85rem 1rem;
	border: 1px solid rgba(220, 38, 38, 0.22);
	border-radius: 0.95rem;
	background: #fef2f2;
	color: #991b1b;
	font-weight: 800;
	line-height: 1.5;
}

.iai-field-error {
	margin-top: 0.45rem;
	padding-left: 0.15rem;
	color: #dc2626;
	font-size: 0.88rem;
	font-weight: 750;
	line-height: 1.45;
}

.iai-field-invalid,
.iai-field-invalid:focus {
	border-color: #dc2626 !important;
	box-shadow: 0 0 0 0.22rem rgba(220, 38, 38, 0.12) !important;
}

.iai-has-error .form-label {
	color: #991b1b;
}

</style>
