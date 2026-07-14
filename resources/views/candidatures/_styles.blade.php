<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
	/* Utilitaire attendu par le script des documents dynamiques (auparavant fourni par Bootstrap) */
	.d-none { display: none !important; }

	:root {
		/* Palette alignée sur la charte du logo IAI-Togo (vert institutionnel + doré/beige) */
		--paper: #f7f5ec;
		--paper-soft: #fefcf5;
		--surface: #ffffff;
		--ink: #21261f;
		--navy: #0D7A37;
		--navy-deep: #0a5c2a;
		--navy-soft: #3f7a52;
		--gold: #b09d72;
		--gold-soft: #f0e9d8;
		--gold-deep: #8a7350;
		--line: #e2dcc8;
		--line-soft: #ede8d8;
		--muted: #6f6a5c;
		--danger: #a3403a;
		--danger-soft: #f5e6e3;
		--success: #2f6b45;
		--success-soft: #e3efe4;
		--radius: 16px;
		--shadow-card: 0 1px 2px rgba(13,122,55,0.05), 0 20px 45px -20px rgba(13,122,55,0.28);
		--font-display: 'Fraunces', 'Iowan Old Style', Georgia, serif;
		--font-body: 'Work Sans', 'Segoe UI', sans-serif;
	}

	.depot-body {
		background: var(--paper-soft);
		color: var(--ink);
		font-family: var(--font-body);
		font-size: 16px;
		line-height: 1.6;
		margin: 0;
		min-height: 100vh;
		overflow-x: hidden;
	}

	/* Split Layout */
	.split-layout {
		display: flex;
		min-height: 100vh;
		width: 100%;
	}

	/* Left Column (Image & Hero) */
	.split-left {
		flex: 0 0 35%;
		position: relative;
		background: url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
		color: #fff;
		display: flex;
		flex-direction: column;
		padding: 40px;
	}

	.left-overlay {
		position: absolute;
		inset: 0;
		background: linear-gradient(135deg, rgba(10,92,42,0.92) 0%, rgba(13,122,55,0.7) 100%);
		z-index: 1;
	}
	
	.left-content {
		position: relative;
		z-index: 2;
		display: flex;
		flex-direction: column;
		height: 100%;
		justify-content: space-between;
	}

	.hero-text-container {
		margin-top: auto;
		margin-bottom: auto;
	}

	.left-footer {
		font-size: 12px;
		color: rgba(255,255,255,0.6);
		margin-top: 40px;
	}

	/* Right Column (Form) */
	.split-right {
		flex: 1;
		background: var(--paper-soft);
		display: flex;
		flex-direction: column;
		padding: 40px 60px;
		overflow-y: auto;
		max-height: 100vh;
	}

	.right-content {
		max-width: 760px;
		width: 100%;
		margin: 0 auto;
	}

	@media (max-width: 1024px) {
		.split-layout { flex-direction: column; }
		.split-left { flex: none; min-height: 50vh; padding: 30px; }
		.split-right { flex: none; max-height: none; padding: 30px 20px; }
	}

	.depot-masthead {
		display: flex;
		align-items: center;
		gap: 14px;
		margin-bottom: 40px;
	}
	.logo-link {
		background: #fff;
		padding: 8px 12px;
		border-radius: 8px;
		display: inline-block;
	}
	.depot-logo {
		max-height: 38px;
		max-width: 90px;
		object-fit: contain;
	}
	.depot-eyebrow {
		font-family: var(--font-body);
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.16em;
		color: rgba(255,255,255,0.8);
	}

	.depot-hero-kicker {
		font-size: 12px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.18em;
		color: var(--gold);
		margin: 0 0 16px;
	}
	.depot-hero-title {
		font-family: var(--font-display);
		font-weight: 600;
		font-size: clamp(36px, 4vw, 54px);
		line-height: 1.1;
		color: #ffffff;
		margin: 0 0 24px;
		text-wrap: balance;
	}
	.depot-hero-title span {
		color: var(--gold);
		font-style: italic;
	}
	.depot-hero-lede {
		font-size: 18px;
		line-height: 1.6;
		color: rgba(255,255,255,0.85);
		max-width: 42ch;
		margin: 0;
	}
	.depot-hero-note {
		display: flex;
		gap: 14px;
		align-items: flex-start;
		border-left: 4px solid var(--gold);
		background: #fdfaf3;
		padding: 18px 22px;
		border-radius: 8px;
		border: 1px solid #eaddc0;
		font-size: 15px;
		color: #5a4420;
		margin-bottom: 30px;
		box-shadow: 0 4px 12px rgba(176,157,114,0.1);
	}
	.note-icon {
		color: var(--gold);
		margin-top: 2px;
	}

	.depot-alert {
		border-radius: 10px;
		padding: 14px 18px;
		font-size: 14.5px;
		margin-bottom: 20px;
		max-width: 56ch;
	}
	.depot-alert--error {
		background: var(--danger-soft);
		color: #7a2b26;
		border-left: 3px solid var(--danger);
	}
	.depot-alert--success {
		background: var(--success-soft);
		color: #1f4a2d;
		border-left: 3px solid var(--success);
	}

	.btn-refined {
		font-family: var(--font-body);
		font-weight: 600;
		font-size: 14.5px;
		letter-spacing: 0.01em;
		border-radius: 999px;
		padding: 13px 26px;
		border: 1px solid transparent;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
	}
	.btn-refined:active { transform: translateY(1px); }
	.btn-refined--primary {
		background: var(--navy-deep);
		color: var(--paper-soft);
		box-shadow: 0 10px 24px -10px rgba(10,92,42,0.55);
	}
	.btn-refined--primary:hover { background: var(--navy); box-shadow: 0 14px 28px -10px rgba(10,92,42,0.6); }
	.btn-refined--gold {
		background: var(--gold-deep);
		color: #fff8ea;
		box-shadow: 0 10px 24px -10px rgba(138,115,80,0.55);
	}
	.btn-refined--gold:hover { background: #6f5a3f; }
	.btn-refined--ghost {
		background: transparent;
		color: var(--navy-soft);
		border-color: var(--line);
	}
	.btn-refined--ghost:hover { background: var(--paper-soft); }
	.btn-refined:disabled { opacity: 0.55; cursor: not-allowed; }

	/* Stepper */
	.stepper {
		position: relative;
		z-index: 1;
		display: grid;
		grid-template-columns: repeat(5, 1fr);
		gap: 4px;
		margin: 34px 0 26px;
	}
	.stepper-track {
		grid-column: 1 / -1;
		height: 3px;
		background: var(--line);
		border-radius: 3px;
		margin-bottom: 14px;
		overflow: hidden;
	}
	.stepper-progress {
		height: 100%;
		width: 0%;
		background: linear-gradient(90deg, var(--gold-deep), var(--gold));
		transition: width 0.35s ease;
	}
	.stepper-item {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 8px;
		cursor: pointer;
		background: none;
		border: none;
		font-family: var(--font-body);
		padding: 0;
	}
	.stepper-dot {
		width: 30px;
		height: 30px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 13px;
		font-weight: 600;
		background: var(--surface);
		border: 1.5px solid var(--line);
		color: var(--muted);
		transition: all 0.2s ease;
	}
	.stepper-label {
		font-size: 11.5px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		color: var(--muted);
		text-align: center;
	}
	.stepper-item.is-current .stepper-dot {
		background: var(--navy-deep);
		border-color: var(--navy-deep);
		color: #fff;
		box-shadow: 0 0 0 4px rgba(10,92,42,0.12);
	}
	.stepper-item.is-current .stepper-label { color: var(--navy-deep); }
	.stepper-item.is-done .stepper-dot {
		background: var(--gold-soft);
		border-color: var(--gold);
		color: var(--gold-deep);
	}
	.stepper-item.is-done .stepper-label { color: var(--gold-deep); }

	@media (max-width: 640px) {
		.stepper { grid-template-columns: repeat(5, 1fr); gap: 2px; }
		.stepper-label { display: none; }
	}

	/* Panels */
	.depot-panels { position: relative; z-index: 1; }
	.step-panel { display: none; }
	.step-panel.is-active {
		display: block;
		animation: depotFadeIn 0.4s ease both;
	}
	@keyframes depotFadeIn {
		from { opacity: 0; transform: translateY(8px); }
		to { opacity: 1; transform: translateY(0); }
	}
	@media (prefers-reduced-motion: reduce) {
		.step-panel.is-active { animation: none; }
	}

	.panel-card {
		background: var(--surface);
		border: 1px solid var(--line-soft);
		border-radius: var(--radius);
		box-shadow: var(--shadow-card);
		padding: 34px clamp(20px, 4vw, 46px) 30px;
	}
	.panel-head { margin-bottom: 26px; }
	.panel-kicker {
		font-size: 11.5px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.14em;
		color: var(--gold-deep);
		margin: 0 0 6px;
	}
	.panel-title {
		font-family: var(--font-display);
		font-size: 26px;
		font-weight: 600;
		color: var(--navy-deep);
		margin: 0;
	}
	.panel-sub {
		font-size: 14px;
		color: var(--muted);
		margin: 6px 0 0;
	}

	.field-grid {
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
		align-items: start;
		gap: 20px 24px;
	}
	.field { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
	.field--full { grid-column: 1 / -1; }
	@media (max-width: 640px) {
		.field-grid { grid-template-columns: 1fr; }
	}

	.field label {
		font-size: 12.5px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: var(--navy-soft);
	}
	.depot-form span.text-danger { color: var(--gold-deep); font-style: normal; margin-left: 2px; }
	.depot-form small.text-danger,
	.depot-form small.form-text.text-danger {
		display: block;
		color: var(--danger);
		font-size: 12.5px;
		margin-top: 2px;
	}

	.input-refined,
	.select-refined,
	.textarea-refined {
		font-family: var(--font-body);
		font-size: 15px;
		color: var(--ink);
		background: var(--paper-soft);
		border: 1.5px solid var(--line);
		border-radius: 10px;
		padding: 11px 14px;
		width: 100%;
		box-sizing: border-box;
		transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
	}
	.textarea-refined { resize: vertical; min-height: 90px; }
	.input-refined:focus,
	.select-refined:focus,
	.textarea-refined:focus {
		outline: none;
		border-color: var(--gold);
		background: #fff;
		box-shadow: 0 0 0 4px rgba(176,157,114,0.2);
	}
	.select-refined { appearance: none; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='9'><path d='M1 1l6 6 6-6' stroke='%230a5c2a' stroke-width='1.6' fill='none' stroke-linecap='round' stroke-linejoin='round'/></svg>"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }

	/* Choices.js Overrides */
	.choices, .choices * {
		box-sizing: border-box;
	}
	.choices {
		margin-bottom: 0;
	}
	.choices.select-refined {
		padding: 0; /* Remove padding from top level injected by .select-refined */
		border: none;
		background: transparent;
	}
	.choices__inner {
		font-family: var(--font-body);
		font-size: 15px;
		color: var(--ink);
		background: var(--paper-soft);
		border: 1.5px solid var(--line);
		border-radius: 10px;
		padding: 4px 18px; /* Plus de padding à gauche/droite */
		min-height: 48px;
		display: flex;
		align-items: center;
		width: 100%;
		transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
	}
	.choices.is-focused .choices__inner, .choices.is-open .choices__inner {
		border-color: var(--gold);
		background: #fff;
		box-shadow: 0 0 0 4px rgba(176,157,114,0.2);
	}
	.choices__list--single {
		padding: 0;
		width: 100%;
	}
	.choices__item {
		display: flex;
		align-items: center;
	}
	.choices__list--dropdown .choices__item {
		padding: 10px 20px; /* Plus de marge à gauche (20px au lieu de 14px) */
		font-size: 15px;
	}
	.choices[data-type*="select-one"] .choices__button {
		margin-right: 8px;
	}
	.choices[data-type*="select-one"]::after {
		right: 14px;
		border: none;
		width: 14px;
		height: 9px;
		background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='9'><path d='M1 1l6 6 6-6' stroke='%230a5c2a' stroke-width='1.6' fill='none' stroke-linecap='round' stroke-linejoin='round'/></svg>");
		background-repeat: no-repeat;
		margin-top: -4.5px;
	}
	.choices[data-type*="select-one"].is-open::after {
		transform: rotate(180deg);
		margin-top: -4.5px;
	}

	.help-text { font-size: 12.5px; color: var(--muted); margin-top: -2px; }

	/* File fields — zone de glisser-déposer */
	.file-dropzone {
		position: relative;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 5px;
		min-height: 112px;
		padding: 18px 16px;
		border: 1.5px dashed var(--line);
		border-radius: 14px;
		background: var(--paper-soft);
		text-align: center;
		transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
	}
	.file-dropzone:hover { border-color: var(--gold); background: #fff; }
	.file-dropzone.is-dragover {
		border-color: var(--gold-deep);
		background: var(--gold-soft);
		box-shadow: 0 0 0 4px rgba(176,157,114,0.2);
	}
	.file-dropzone.has-files { border-style: solid; border-color: var(--navy-soft); background: #fff; }
	.file-dropzone-input {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		opacity: 0;
		cursor: pointer;
		z-index: 2;
	}
	.file-dropzone-visual {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 6px;
		pointer-events: none;
	}
	.file-dropzone-icon { color: var(--gold-deep); display: flex; }
	.file-dropzone-text {
		font-family: var(--font-body);
		font-size: 13.5px;
		color: var(--ink);
	}
	.file-dropzone-text strong { color: var(--navy-deep); }
	.file-dropzone-hint { font-size: 11.5px; color: var(--muted); }

	.file-chip-list {
		display: flex;
		flex-direction: column;
		gap: 6px;
		margin-top: 8px;
	}
	.file-chip {
		display: flex;
		align-items: center;
		gap: 8px;
		max-width: 100%;
		background: #fff;
		border: 1px solid var(--line);
		border-radius: 10px;
		padding: 7px 8px 7px 10px;
		font-size: 12.5px;
		color: var(--ink);
		box-sizing: border-box;
	}
	.file-chip-icon { color: var(--navy-soft); flex-shrink: 0; display: flex; }
	.file-chip-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.file-chip-remove {
		border: none;
		background: transparent;
		color: var(--danger);
		font-size: 16px;
		line-height: 1;
		width: 20px;
		height: 20px;
		border-radius: 999px;
		cursor: pointer;
		flex-shrink: 0;
		transition: background 0.15s ease, color 0.15s ease;
	}
	.file-chip-remove:hover { color: #fff; background: var(--danger); }

	.depot-info-box {
		display: flex;
		gap: 12px;
		align-items: flex-start;
		background: #eef5ee;
		border: 1px solid #d7e6d9;
		border-radius: 12px;
		padding: 16px 18px;
		margin-bottom: 26px;
		font-size: 14px;
		color: var(--navy-soft);
	}
	.depot-info-box strong { color: var(--navy-deep); }
	.depot-info-box ul { margin: 8px 0 0; padding-left: 18px; }
	.depot-info-box li { margin-bottom: 3px; }

	.section-divider {
		display: flex;
		align-items: center;
		gap: 10px;
		margin: 30px 0 18px;
		font-size: 11.5px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.1em;
		color: var(--gold-deep);
	}
	.section-divider::after { content: ''; flex: 1; height: 1px; background: var(--line); }

	.step-actions {
		display: flex;
		justify-content: space-between;
		gap: 12px;
		margin-top: 30px;
		padding-top: 22px;
		border-top: 1px solid var(--line-soft);
	}
	@media (max-width: 480px) {
		.step-actions { flex-direction: column-reverse; }
		.step-actions .btn-refined { width: 100%; justify-content: center; }
	}


	.replicate-btn {
		font-family: var(--font-body);
		font-size: 12.5px;
		font-weight: 600;
		color: var(--gold-deep);
		background: var(--gold-soft);
		border: none;
		border-radius: 999px;
		padding: 8px 16px;
		cursor: pointer;
	}
	.replicate-btn:hover { background: var(--gold); color: #fff; }

	/* Fiches tuteur/parent répétables */
	#tuteurs-container {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}
	.tuteur-card {
		border: 1.5px solid var(--line);
		border-radius: 14px;
		padding: 22px clamp(16px, 3vw, 26px);
		background: var(--paper-soft);
	}
	.tuteur-card-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 18px;
		padding-bottom: 14px;
		border-bottom: 1px solid var(--line-soft);
	}
	.tuteur-card-title {
		font-family: var(--font-display);
		font-size: 16px;
		font-weight: 600;
		color: var(--navy-deep);
		margin: 0;
	}
	.remove-tuteur-btn {
		font-family: var(--font-body);
		font-size: 12px;
		font-weight: 600;
		color: var(--danger);
		background: var(--danger-soft);
		border: none;
		border-radius: 999px;
		padding: 7px 15px;
		cursor: pointer;
		transition: background 0.15s ease, color 0.15s ease;
		flex-shrink: 0;
	}
	.remove-tuteur-btn:hover { background: var(--danger); color: #fff; }

	#add-tuteur-btn { margin-top: 22px; }

	.tuteur-responsable-check {
		display: flex;
		align-items: center;
		gap: 10px;
		background: var(--gold-soft);
		border: 1px solid var(--gold);
		border-radius: 10px;
		padding: 12px 16px;
		font-size: 13.5px;
		font-weight: 600;
		color: var(--gold-deep);
		text-transform: none;
		letter-spacing: normal;
		cursor: pointer;
	}
	.tuteur-responsable-check input { width: 16px; height: 16px; accent-color: var(--gold-deep); cursor: pointer; }

	.consent-row {
		display: flex;
		gap: 10px;
		align-items: flex-start;
		background: var(--paper-soft);
		border: 1px solid var(--line);
		border-radius: 10px;
		padding: 14px 16px;
		margin: 8px 0 4px;
		font-size: 13.5px;
		color: var(--muted);
	}
	.consent-row input { margin-top: 3px; }

	/* Choices.js (nationalité) restyle */
	.choices { font-family: var(--font-body); font-size: 15px; }
	.choices__inner {
		background: var(--paper-soft) !important;
		border: 1.5px solid var(--line) !important;
		border-radius: 10px !important;
		min-height: 46px;
		padding: 8px 14px !important;
	}
	.choices.is-focused .choices__inner { border-color: var(--gold) !important; box-shadow: 0 0 0 4px rgba(176,157,114,0.2); }
	.choices__list--dropdown, .choices__list[aria-expanded] { border-color: var(--line) !important; border-radius: 10px !important; }
	.choices__list--dropdown .choices__item--selectable.is-highlighted { background: var(--gold-soft) !important; }

	/* Confirmation page — premium */
	.confirm-body {
		background:
			radial-gradient(680px 420px at 50% -8%, rgba(13,122,55,0.10), transparent 70%),
			radial-gradient(520px 360px at 92% 8%, rgba(176,157,114,0.14), transparent 65%),
			var(--paper-soft);
	}
	.confirm-page {
		position: relative;
		min-height: 100vh;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 56px 20px;
		box-sizing: border-box;
	}
	.confirm-masthead {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 14px;
		margin-bottom: 40px;
	}
	.confirm-masthead .logo-link {
		box-shadow: 0 8px 22px -10px rgba(13,122,55,0.35);
	}
	.confirm-eyebrow {
		font-family: var(--font-body);
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.22em;
		color: var(--navy-soft);
	}

	.confirm-card {
		position: relative;
		text-align: center;
		background: var(--surface);
		border: 1px solid var(--line-soft);
		border-radius: 22px;
		box-shadow: 0 1px 2px rgba(13,122,55,0.06), 0 40px 70px -30px rgba(13,122,55,0.32);
		padding: 56px clamp(24px, 6vw, 68px) 46px;
		max-width: 600px;
		width: 100%;
		overflow: hidden;
	}
	.confirm-card::before {
		content: '';
		position: absolute;
		top: 0; left: 0; right: 0;
		height: 5px;
		background: linear-gradient(90deg, var(--navy-deep) 0%, var(--gold) 50%, var(--navy-deep) 100%);
	}

	.confirm-badge-ring {
		width: 92px;
		height: 92px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 0 auto 26px;
		background: conic-gradient(from 180deg, var(--gold-soft), #fff, var(--gold-soft));
		box-shadow: 0 0 0 1px var(--line-soft), 0 18px 30px -14px rgba(176,157,114,0.5);
		animation: confirmRingIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
	}
	.confirm-badge {
		width: 72px; height: 72px; border-radius: 50%;
		background: linear-gradient(150deg, var(--navy) 0%, var(--navy-deep) 100%);
		color: #fff;
		display: flex; align-items: center; justify-content: center;
		box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
	}
	.confirm-badge svg { width: 30px; height: 30px; }
	@keyframes confirmRingIn {
		from { opacity: 0; transform: scale(0.7); }
		to { opacity: 1; transform: scale(1); }
	}
	@media (prefers-reduced-motion: reduce) {
		.confirm-badge-ring { animation: none; }
	}

	.confirm-kicker {
		display: inline-flex;
		align-items: center;
		gap: 8px;
	}
	.confirm-kicker::before,
	.confirm-kicker::after {
		content: '';
		width: 18px;
		height: 1px;
		background: var(--gold);
	}

	.confirm-title {
		font-family: var(--font-display);
		font-weight: 600;
		font-size: clamp(28px, 3.6vw, 40px);
		line-height: 1.22;
		color: var(--navy-deep);
		margin: 14px 0 16px;
		text-wrap: balance;
	}
	.confirm-title span { color: var(--gold-deep); font-style: italic; }
	.confirm-lede {
		font-size: 16px;
		line-height: 1.65;
		color: var(--muted);
		max-width: 46ch;
		margin: 0 auto 34px;
	}
	.confirm-lede strong { color: var(--ink); }

	.confirm-divider {
		display: flex;
		align-items: center;
		gap: 12px;
		margin: 0 0 26px;
	}
	.confirm-divider::before,
	.confirm-divider::after {
		content: '';
		flex: 1;
		height: 1px;
		background: var(--line);
	}
	.confirm-divider span {
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.14em;
		color: var(--gold-deep);
		white-space: nowrap;
	}

	.confirm-steps {
		list-style: none;
		margin: 0 0 38px;
		padding: 0;
		display: flex;
		flex-direction: column;
		gap: 20px;
		text-align: left;
	}
	.confirm-steps li {
		display: flex;
		gap: 16px;
		align-items: flex-start;
	}
	.confirm-step-num {
		flex-shrink: 0;
		width: 30px;
		height: 30px;
		border-radius: 50%;
		background: var(--gold-soft);
		color: var(--gold-deep);
		font-family: var(--font-display);
		font-weight: 600;
		font-size: 14px;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.confirm-step-body strong {
		display: block;
		font-size: 14.5px;
		font-weight: 600;
		color: var(--navy-deep);
		margin-bottom: 2px;
	}
	.confirm-step-body p {
		margin: 0;
		font-size: 13.5px;
		line-height: 1.55;
		color: var(--muted);
	}

	.confirm-cta { padding: 14px 30px; }
	.confirm-cta svg { width: 16px; height: 16px; }

	.confirm-footer {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-top: 36px;
		font-size: 13.5px;
		color: var(--muted);
		text-align: center;
	}
	.confirm-footer svg { width: 15px; height: 15px; color: var(--gold-deep); flex-shrink: 0; }

	@media (max-width: 480px) {
		.confirm-card { padding: 44px 22px 36px; border-radius: 18px; }
		.confirm-steps li { gap: 12px; }
	}
</style>
