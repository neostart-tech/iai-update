<script>
    
document.getElementById('importForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const fileInput = this.querySelector('input[type="file"]');
    const progressWrapper = document.getElementById('importProgressWrapper');
    const progressBar = document.getElementById('importProgress');
    const statusBox = document.getElementById('importStatus');

    if (!fileInput.files.length) return;

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('_token', '{{ csrf_token() }}');

    progressWrapper.classList.remove('d-none');
    statusBox.classList.add('d-none');

    fetch("{{ route('admin.etudiants.import') }}", {
        method: 'POST',
        body: formData,
        credentials: 'same-origin', 
        headers: {
            'Accept': 'application/json' 
        }
    })
    .then(async res => {
        if (!res.ok) {
            const text = await res.text();
            throw new Error(text);
        }
        return res.json();
    })
    .then(data => {
        progressBar.style.width = '100%';
        progressBar.innerText = '100%';

        statusBox.classList.remove('d-none');
        statusBox.classList.remove('alert-danger');
        statusBox.classList.add('alert-success');
        statusBox.innerText = data.message ?? 'Import lancé';

    })
    .catch(err => {
        console.error('IMPORT ERROR:', err);

        statusBox.classList.remove('d-none');
        statusBox.classList.remove('alert-success');
        statusBox.classList.add('alert-danger');
        statusBox.innerText = 'Erreur technique lors du lancement de l’import';
    });
});
</script>
