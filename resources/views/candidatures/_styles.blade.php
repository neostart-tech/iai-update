<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&display=swap" rel="stylesheet">

<style>
	.d-none { display: none !important; }

	:root {
		--brand-950: #052e1d;
		--brand-900: #064e3b;
		--brand-800: #065f46;
		--brand-700: #0f6f3d;
		--brand-100: #dcfce7;
		--brand-50: #f0fdf4;
		--gold: #f2c94c;
		--gold-soft: #fff7d6;
		--paper: #f8fafc;
		--surface: #ffffff;
		--ink: #0f172a;
		--muted: #64748b;
		--muted-2: #475569;
		--line: #dbe4ef;
		--line-soft: #edf2f7;
		--danger: #b91c1c;
		--danger-soft: #fef2f2;
		--success: #15803d;
		--success-soft: #f0fdf4;
		--radius: 1.25rem;
		--shadow-card: 0 24px 80px rgba(15, 23, 42, 0.12);
		--shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.08);
		--font-display: 'Fraunces', Georgia, serif;
		--font-body: 'Inter', 'Segoe UI', sans-serif;
	}

	* { box-sizing: border-box; }

	html { scroll-behavior: smooth; }

	body.depot-body {
		margin: 0;
		min-height: 100vh;
		overflow-x: hidden;
		background:
			radial-gradient(circle at top left, rgba(15,111,61,0.16), transparent 34rem),
			linear-gradient(180deg, #ffffff 0%, var(--paper) 100%);
		color: var(--ink);
		font-family: var(--font-body);
		font-size: 16px;
		line-height: 1.6;
	}

	a { color: inherit; }

	.depot-page-shell {
		min-height: 100vh;
	}

	.depot-topbar {
		position: sticky;
		top: 0;
		z-index: 20;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
		min-height: 5.25rem;
		padding: 0.85rem clamp(1rem, 4vw, 4rem);
		border-bottom: 1px solid rgba(219, 228, 239, 0.86);
		background: rgba(255, 255, 255, 0.94);
		backdrop-filter: blur(14px);
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
	}

	.depot-brand {
		display: inline-flex;
		align-items: center;
		gap: 0.9rem;
		text-decoration: none;
	}

	.depot-logo {
		width: 4.2rem;
		height: 3.3rem;
		object-fit: contain;
		border-radius: 0.75rem;
		background: var(--gold-soft);
		padding: 0.15rem;
	}

	.depot-brand strong {
		display: block;
		font-size: 1.2rem;
		line-height: 1.1;
		font-weight: 900;
		color: var(--brand-900);
		letter-spacing: -0.02em;
	}

	.depot-brand small {
		display: block;
		margin-top: 0.2rem;
		font-size: 0.72rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.12em;
		color: var(--muted);
	}

	.depot-topbar-actions {
		display: flex;
		align-items: center;
		gap: 0.75rem;
	}

	.depot-topbar-link,
	.depot-topbar-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 999px;
		padding: 0.65rem 1rem;
		font-size: 0.9rem;
		font-weight: 800;
		text-decoration: none;
		white-space: nowrap;
	}

	.depot-topbar-link {
		color: var(--brand-900);
		background: var(--brand-50);
		border: 1px solid var(--brand-100);
	}

	.depot-topbar-badge {
		color: var(--brand-950);
		background: var(--gold);
		border: 1px solid rgba(242, 201, 76, 0.7);
	}

	.split-layout {
		display: grid;
		grid-template-columns: minmax(20rem, 0.85fr) minmax(0, 1.55fr);
		gap: clamp(1.5rem, 3vw, 3rem);
		width: min(92rem, calc(100vw - 2rem));
		margin: 0 auto;
		padding: clamp(1.5rem, 3vw, 3rem) 0 4rem;
	}

	.split-left {
		position: sticky;
		top: 6.5rem;
		height: fit-content;
		min-height: 38rem;
		overflow: hidden;
		border-radius: 2rem;
		background:
			linear-gradient(145deg, rgba(5,46,29,0.96), rgba(6,95,70,0.92)),
			radial-gradient(circle at 20% 20%, rgba(242,201,76,0.24), transparent 18rem);
		box-shadow: var(--shadow-card);
		color: #ffffff;
	}

	.split-left::after {
		content: "";
		position: absolute;
		inset: auto -4rem -6rem auto;
		width: 18rem;
		height: 18rem;
		border-radius: 50%;
		background: rgba(242, 201, 76, 0.15);
	}

	.left-content {
		position: relative;
		z-index: 1;
		display: flex;
		flex-direction: column;
		min-height: 38rem;
		padding: clamp(1.5rem, 3vw, 2.4rem);
	}

	.depot-hero-kicker {
		margin: 0 0 1rem;
		color: var(--gold);
		font-size: 0.78rem;
		font-weight: 900;
		text-transform: uppercase;
		letter-spacing: 0.24em;
	}

	.depot-hero-title {
		margin: 0;
		color: #ffffff;
		font-family: var(--font-display);
		font-size: clamp(2.4rem, 4vw, 4.8rem);
		font-weight: 800;
		line-height: 0.96;
		letter-spacing: -0.05em;
		text-wrap: balance;
	}

	.depot-hero-title span {
		display: block;
		color: var(--gold);
	}

	.depot-hero-lede {
		margin: 1.5rem 0 0;
		max-width: 34rem;
		color: rgba(255,255,255,0.78);
		font-size: 1rem;
		line-height: 1.8;
	}

	.depot-hero-checklist {
		display: grid;
		gap: 0.8rem;
		margin-top: 2rem;
	}

	.depot-hero-checklist div {
		display: flex;
		gap: 0.75rem;
		align-items: center;
		border: 1px solid rgba(255,255,255,0.12);
		border-radius: 1rem;
		background: rgba(255,255,255,0.07);
		padding: 0.85rem;
	}

	.depot-hero-checklist span {
		display: grid;
		place-items: center;
		width: 1.9rem;
		height: 1.9rem;
		flex: 0 0 auto;
		border-radius: 999px;
		background: var(--gold);
		color: var(--brand-950);
		font-size: 0.82rem;
		font-weight: 900;
	}

	.depot-hero-checklist p {
		margin: 0;
		color: rgba(255,255,255,0.86);
		font-size: 0.9rem;
		font-weight: 700;
	}

	.left-footer {
		margin: auto 0 0;
		padding-top: 2rem;
		color: rgba(255,255,255,0.68);
		font-size: 0.84rem;
		line-height: 1.7;
	}

	.split-right {
		min-width: 0;
	}

	.right-content {
		width: 100%;
		max-width: 56rem;
		margin: 0 auto;
	}

	.depot-hero-note,
	.depot-alert {
		display: flex;
		align-items: flex-start;
		gap: 0.8rem;
		margin-bottom: 1rem;
		border-radius: 1.25rem;
		padding: 1rem 1.15rem;
		font-size: 0.94rem;
		line-height: 1.7;
		box-shadow: var(--shadow-soft);
	}

	.depot-hero-note {
		border: 1px solid rgba(242, 201, 76, 0.5);
		background: var(--gold-soft);
		color: #5c4500;
	}

	.note-icon {
		display: inline-flex;
		color: #a97900;
		margin-top: 0.15rem;
	}

	.depot-alert--error {
		border: 1px solid #fecaca;
		background: var(--danger-soft);
		color: #7f1d1d;
	}

	.depot-form {
		width: 100%;
	}

	.stepper {
		position: sticky;
		top: 6rem;
		z-index: 10;
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 0.5rem;
		margin: 1.2rem 0;
		border: 1px solid rgba(219, 228, 239, 0.9);
		border-radius: 1.4rem;
		background: rgba(255,255,255,0.94);
		padding: 1rem;
		backdrop-filter: blur(12px);
		box-shadow: var(--shadow-soft);
	}

	.stepper-track {
		grid-column: 1 / -1;
		height: 0.35rem;
		overflow: hidden;
		border-radius: 999px;
		background: var(--line-soft);
	}

	.stepper-progress {
		width: 0%;
		height: 100%;
		border-radius: inherit;
		background: linear-gradient(90deg, var(--brand-700), var(--gold));
		transition: width 0.25s ease;
	}

	.stepper-item {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 0.55rem;
		min-height: 2.9rem;
		border: 1px solid transparent;
		border-radius: 999px;
		background: transparent;
		color: var(--muted-2);
		font-family: var(--font-body);
		font-weight: 900;
		cursor: pointer;
		transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
	}

	.stepper-dot {
		display: grid;
		place-items: center;
		width: 1.75rem;
		height: 1.75rem;
		border-radius: 999px;
		background: #e2e8f0;
		color: var(--brand-900);
		font-size: 0.82rem;
		font-weight: 900;
	}

	.stepper-label {
		font-size: 0.82rem;
		white-space: nowrap;
	}

	.stepper-item.is-current {
		border-color: var(--brand-100);
		background: var(--brand-50);
		color: var(--brand-900);
	}

	.stepper-item.is-current .stepper-dot {
		background: var(--brand-700);
		color: #ffffff;
	}

	.stepper-item.is-done {
		color: var(--brand-700);
	}

	.stepper-item.is-done .stepper-dot {
		background: var(--gold);
		color: var(--brand-950);
	}

	.depot-panels {
		position: relative;
	}

	.step-panel {
		display: none;
	}

	.step-panel.is-active {
		display: block;
		animation: fadeUp 0.25s ease both;
	}

	@keyframes fadeUp {
		from { opacity: 0; transform: translateY(0.5rem); }
		to { opacity: 1; transform: translateY(0); }
	}

	.panel-card {
		border: 1px solid rgba(219, 228, 239, 0.96);
		border-radius: 1.75rem;
		background: rgba(255,255,255,0.98);
		box-shadow: var(--shadow-card);
		padding: clamp(1.25rem, 3vw, 2.4rem);
	}

	.panel-head {
		margin-bottom: 1.6rem;
		padding-bottom: 1.2rem;
		border-bottom: 1px solid var(--line-soft);
	}

	.panel-kicker {
		margin: 0 0 0.35rem;
		color: var(--gold);
		font-size: 0.78rem;
		font-weight: 900;
		text-transform: uppercase;
		letter-spacing: 0.18em;
	}

	.panel-title {
		margin: 0;
		color: var(--brand-900);
		font-size: clamp(1.6rem, 3vw, 2.25rem);
		font-weight: 900;
		line-height: 1.1;
		letter-spacing: -0.04em;
	}

	.panel-sub {
		margin: 0.6rem 0 0;
		color: var(--muted);
		font-size: 0.95rem;
		line-height: 1.7;
	}

	.field-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 1.05rem 1.2rem;
	}

	.field {
		display: flex;
		flex-direction: column;
		gap: 0.45rem;
		min-width: 0;
	}

	.field--full {
		grid-column: 1 / -1;
	}

	.field label {
		color: var(--brand-900);
		font-size: 0.78rem;
		font-weight: 900;
		text-transform: uppercase;
		letter-spacing: 0.06em;
	}

	.help-text {
		margin: 0;
		color: var(--muted);
		font-size: 0.82rem;
		line-height: 1.5;
	}

	.depot-form span.text-danger {
		color: var(--danger);
		font-weight: 900;
	}

	.depot-form small.text-danger,
	.depot-form small.form-text.text-danger {
		display: block;
		color: var(--danger);
		font-size: 0.82rem;
		font-weight: 700;
	}

	.input-refined,
	.select-refined,
	.textarea-refined {
		width: 100%;
		border: 1px solid var(--line);
		border-radius: 0.95rem;
		background: #ffffff;
		color: var(--ink);
		font-family: var(--font-body);
		font-size: 0.98rem;
		line-height: 1.4;
		padding: 0.82rem 0.95rem;
		transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
	}

	.input-refined:focus,
	.select-refined:focus,
	.textarea-refined:focus {
		outline: none;
		border-color: var(--brand-700);
		box-shadow: 0 0 0 4px rgba(15,111,61,0.14);
	}

	.textarea-refined {
		min-height: 7rem;
		resize: vertical;
	}

	.select-refined {
		appearance: none;
		background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='9'><path d='M1 1l6 6 6-6' stroke='%23064e3b' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/></svg>");
		background-repeat: no-repeat;
		background-position: right 1rem center;
		padding-right: 2.5rem;
	}

	.iti {
		width: 100%;
	}

	.choices {
		margin-bottom: 0;
	}

	.choices.select-refined {
		padding: 0;
		border: 0;
		background: transparent;
	}

	.choices__inner {
		display: flex;
		align-items: center;
		min-height: 3.1rem;
		border: 1px solid var(--line);
		border-radius: 0.95rem;
		background: #ffffff;
		padding: 0.35rem 0.95rem;
		font-family: var(--font-body);
		font-size: 0.98rem;
	}

	.choices.is-focused .choices__inner,
	.choices.is-open .choices__inner {
		border-color: var(--brand-700);
		box-shadow: 0 0 0 4px rgba(15,111,61,0.14);
	}

	.choices__list--dropdown {
		border-color: var(--line);
		border-radius: 1rem;
		box-shadow: var(--shadow-soft);
		overflow: hidden;
	}

	.choices__list--dropdown .choices__item {
		padding: 0.75rem 1rem;
		font-size: 0.95rem;
	}

	.file-dropzone {
		position: relative;
		display: grid;
		place-items: center;
		min-height: 7rem;
		border: 1.5px dashed #b6c7d8;
		border-radius: 1.1rem;
		background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
		padding: 1rem;
		text-align: center;
		transition: border-color 0.15s ease, background 0.15s ease, transform 0.15s ease;
	}

	.file-dropzone:hover,
	.file-dropzone.is-dragover {
		border-color: var(--brand-700);
		background: var(--brand-50);
		transform: translateY(-1px);
	}

	.file-dropzone.has-files {
		border-color: var(--success);
		background: var(--success-soft);
	}

	.file-dropzone-input {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		opacity: 0;
		cursor: pointer;
	}

	.file-dropzone-visual {
		display: grid;
		gap: 0.35rem;
		justify-items: center;
		color: var(--muted-2);
		pointer-events: none;
	}

	.file-dropzone-icon {
		display: grid;
		place-items: center;
		width: 2.7rem;
		height: 2.7rem;
		border-radius: 999px;
		background: var(--brand-50);
		color: var(--brand-700);
	}

	.file-dropzone-text {
		color: var(--ink);
		font-size: 0.92rem;
	}

	.file-dropzone-hint {
		color: var(--muted);
		font-size: 0.8rem;
	}

	.file-chip-list {
		display: flex;
		flex-wrap: wrap;
		gap: 0.55rem;
		margin-top: 0.7rem;
	}

	.file-chip {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		max-width: 100%;
		border: 1px solid var(--line);
		border-radius: 999px;
		background: #ffffff;
		color: var(--brand-900);
		padding: 0.4rem 0.65rem;
		font-size: 0.82rem;
		font-weight: 700;
	}

	.file-chip-name {
		max-width: 15rem;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.file-chip-remove {
		display: grid;
		place-items: center;
		width: 1.1rem;
		height: 1.1rem;
		border: 0;
		border-radius: 999px;
		background: #fee2e2;
		color: var(--danger);
		font-size: 0.9rem;
		font-weight: 900;
		cursor: pointer;
	}

	.depot-info-box {
		display: flex;
		gap: 0.8rem;
		align-items: flex-start;
		margin-bottom: 1rem;
		border: 1px solid var(--brand-100);
		border-radius: 1.1rem;
		background: var(--brand-50);
		color: var(--brand-900);
		padding: 1rem;
	}

	.depot-info-box ul {
		margin: 0.5rem 0 0;
		padding-left: 1.2rem;
	}

	.step-actions {
		display: flex;
		justify-content: space-between;
		gap: 0.75rem;
		margin-top: 1.8rem;
		padding-top: 1.2rem;
		border-top: 1px solid var(--line-soft);
	}

	.btn-refined {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 0.5rem;
		min-height: 2.9rem;
		border: 1px solid transparent;
		border-radius: 999px;
		padding: 0.78rem 1.2rem;
		font-family: var(--font-body);
		font-size: 0.92rem;
		font-weight: 900;
		text-decoration: none;
		cursor: pointer;
		transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
	}

	.btn-refined:hover {
		transform: translateY(-1px);
	}

	.btn-refined:disabled {
		opacity: 0.55;
		cursor: not-allowed;
		transform: none;
	}

	.btn-refined--primary {
		background: var(--brand-700);
		color: #ffffff;
		box-shadow: 0 12px 24px rgba(15,111,61,0.22);
	}

	.btn-refined--gold {
		background: var(--gold);
		color: var(--brand-950);
		box-shadow: 0 12px 24px rgba(242,201,76,0.24);
	}

	.btn-refined--ghost {
		border-color: var(--line);
		background: #ffffff;
		color: var(--brand-900);
	}

	.btn-refined--ghost:hover {
		background: var(--brand-50);
		border-color: var(--brand-100);
	}

	.tuteur-card {
		border: 1px solid var(--line);
		border-radius: 1.3rem;
		background: #ffffff;
		padding: 1rem;
		box-shadow: 0 10px 30px rgba(15,23,42,0.05);
	}

	.tuteur-card + .tuteur-card {
		margin-top: 1rem;
	}

	.tuteur-card-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
		margin-bottom: 1rem;
	}

	.tuteur-card-title {
		margin: 0;
		color: var(--brand-900);
		font-size: 1rem;
		font-weight: 900;
	}

	.remove-tuteur-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border: 1px solid #fecaca;
		border-radius: 999px;
		background: #fff;
		color: var(--danger);
		padding: 0.45rem 0.7rem;
		font-size: 0.78rem;
		font-weight: 900;
		cursor: pointer;
	}

	.consent-row {
		display: flex;
		align-items: flex-start;
		gap: 0.7rem;
		border: 1px solid var(--line);
		border-radius: 1.1rem;
		background: #ffffff;
		padding: 1rem;
		color: var(--muted-2);
	}

	.consent-row input {
		margin-top: 0.25rem;
	}

	.tuteur-responsable-check {
		display: flex !important;
		align-items: center;
		gap: 0.6rem;
		text-transform: none !important;
		letter-spacing: normal !important;
		color: var(--muted-2) !important;
	}

	.confirm-body {
		min-height: 100vh;
		background:
			radial-gradient(circle at top, rgba(15,111,61,0.18), transparent 32rem),
			linear-gradient(180deg, #ffffff 0%, var(--paper) 100%);
	}

	.confirm-page {
		display: grid;
		place-items: center;
		min-height: 100vh;
		padding: 2rem 1rem;
	}

	.confirm-masthead,
	.confirm-footer {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 0.75rem;
		color: var(--muted);
		text-align: center;
	}

	.confirm-card {
		width: min(42rem, 100%);
		margin: 1.5rem auto;
		border: 1px solid var(--line);
		border-radius: 2rem;
		background: #ffffff;
		box-shadow: var(--shadow-card);
		padding: clamp(1.5rem, 4vw, 3rem);
		text-align: center;
	}

	.confirm-badge-ring {
		display: grid;
		place-items: center;
		width: 5rem;
		height: 5rem;
		margin: 0 auto 1rem;
		border-radius: 999px;
		background: var(--success-soft);
	}

	.confirm-badge {
		display: grid;
		place-items: center;
		width: 3.4rem;
		height: 3.4rem;
		border-radius: 999px;
		background: var(--success);
		color: #ffffff;
	}

	.confirm-title {
		margin: 0;
		color: var(--brand-900);
		font-size: clamp(2rem, 4vw, 3rem);
		font-weight: 900;
		line-height: 1.05;
		letter-spacing: -0.05em;
	}

	.confirm-title span {
		color: var(--success);
	}

	.confirm-lede {
		margin: 1rem auto 0;
		max-width: 34rem;
		color: var(--muted-2);
	}

	.confirm-divider {
		display: flex;
		align-items: center;
		gap: 1rem;
		margin: 2rem 0 1rem;
		color: var(--gold);
		font-size: 0.8rem;
		font-weight: 900;
		text-transform: uppercase;
		letter-spacing: 0.16em;
	}

	.confirm-divider::before,
	.confirm-divider::after {
		content: "";
		flex: 1;
		height: 1px;
		background: var(--line);
	}

	.confirm-steps {
		display: grid;
		gap: 0.8rem;
		margin: 0;
		padding: 0;
		list-style: none;
		text-align: left;
	}

	.confirm-steps li {
		display: flex;
		gap: 0.9rem;
		border: 1px solid var(--line-soft);
		border-radius: 1rem;
		background: #ffffff;
		padding: 1rem;
	}

	.confirm-step-num {
		display: grid;
		place-items: center;
		width: 2rem;
		height: 2rem;
		flex: 0 0 auto;
		border-radius: 999px;
		background: var(--gold);
		color: var(--brand-950);
		font-weight: 900;
	}

	.confirm-step-body strong {
		color: var(--brand-900);
	}

	.confirm-step-body p {
		margin: 0.25rem 0 0;
		color: var(--muted);
		font-size: 0.9rem;
	}

	.confirm-cta {
		margin-top: 1.5rem;
	}

	.submit-help {
		margin: 1.2rem 0 0;
		border: 1px solid var(--line);
		border-radius: 1rem;
		background: #f8fafc;
		color: var(--muted-2);
		padding: 0.85rem 1rem;
		font-size: 0.9rem;
		font-weight: 700;
		line-height: 1.6;
	}

	.submit-help.is-ready {
		border-color: #bbf7d0;
		background: var(--success-soft);
		color: var(--success);
	}

	.btn-refined.is-disabled,
	.btn-refined[aria-disabled="true"] {
		opacity: 0.72;
		filter: grayscale(0.12);
	}

	.is-invalid-lite {
		border-color: var(--danger) !important;
		box-shadow: 0 0 0 4px rgba(185, 28, 28, 0.10) !important;
	}

	@media (max-width: 1100px) {
		.split-layout {
			grid-template-columns: 1fr;
		}

		.split-left {
			position: relative;
			top: auto;
			min-height: auto;
		}

		.left-content {
			min-height: auto;
		}

		.depot-hero-title {
			max-width: 48rem;
		}
	}

	@media (max-width: 760px) {
		.depot-topbar {
			position: relative;
			align-items: flex-start;
			flex-direction: column;
			min-height: auto;
		}

		.depot-topbar-actions {
			width: 100%;
			justify-content: space-between;
		}

		.depot-brand small {
			display: none;
		}

		.split-layout {
			width: min(100% - 1rem, 92rem);
			padding-top: 1rem;
		}

		.stepper {
			position: relative;
			top: auto;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 0.35rem;
			padding: 0.75rem;
		}

		.stepper-item {
			min-height: 2.5rem;
			padding: 0.2rem;
		}

		.stepper-label {
			display: none;
		}

		.field-grid {
			grid-template-columns: 1fr;
		}

		.step-actions {
			flex-direction: column-reverse;
		}

		.btn-refined {
			width: 100%;
		}

		.panel-card {
			border-radius: 1.35rem;
		}

		.depot-hero-checklist {
			grid-template-columns: 1fr;
		}
	}

	@media (prefers-reduced-motion: reduce) {
		* {
			scroll-behavior: auto !important;
			animation-duration: 0.01ms !important;
			animation-iteration-count: 1 !important;
			transition-duration: 0.01ms !important;
		}
	}
</style>
