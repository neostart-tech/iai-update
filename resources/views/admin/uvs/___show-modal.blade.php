<div class="modal fade" id="exampleModalToggle2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2"
    tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Suppression</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               Voulez-vous vraiment supprimer cette unité de valeur ? Veuillez noter que cette action est irréversible. Continuer ?
            </div>
            <div class="modal-footer">
                <form action="{{route('admin.uvs.delete')}}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id='iduv' name="iduv">
                  
                    <button class="btn btn-warning" type="submit">Continuer la suppression</button>
                </form>
                <button class="btn btn-secondary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Retour</button>
            </div>
        </div>
    </div>
</div>
