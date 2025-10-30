<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tabs = document.querySelectorAll('#wizardTabs .nav-link');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');
        let currentStep = 0;

        function showStep(index) {
            if (index < 0 || index >= tabs.length) return;

            // Désactiver l'étape actuelle
            tabs[currentStep].classList.remove('active');
            document.querySelector(tabs[currentStep].dataset.bsTarget).classList.remove('show', 'active');

            // Activer la nouvelle étape
            currentStep = index;
            tabs[currentStep].classList.add('active');
            document.querySelector(tabs[currentStep].dataset.bsTarget).classList.add('show', 'active');

            // Gérer l'affichage des boutons
            prevBtn.disabled = currentStep === 0;
            nextBtn.classList.toggle('d-none', currentStep === tabs.length - 1);
            submitBtn.classList.toggle('d-none', currentStep !== tabs.length - 1);
        }

        nextBtn.addEventListener('click', () => {
            if (currentStep < tabs.length - 1) {
                showStep(currentStep + 1);
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStep > 0) {
                showStep(currentStep - 1);
            }
        });

        showStep(0); // Init
    });
</script>
<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>
