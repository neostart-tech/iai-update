<div class="modal fade modal-animate" id="animateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier une note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
      </div>
      <div class="modal-body">
        <form method="POST" action='{{route('admin.evaluations.notes.change-notes',$evaluation)}}'>
          @csrf
          @method('POST')

          <div class="form-group">
            <x-forms.label content="Note actuelle" for="current-note" required="0"/>
            <input type="text" class="form-control" id="current-note" readonly>
          </div>

          <div class="form-group">
            <x-forms.label content="Nouvelle note" for="new-note" required="1"/>
            <input type="number" name="newnote" class="form-control" min="0" max="20"  id="new-note" step=".25" required>
          </div>

          <div class="form-group">
            <x-forms.label content="Motif" for="new-note" required="1"/>
            <textarea name="motif" id="motif" class="form-control" cols="30" rows="3"></textarea>
          </div>
          <input type="text" id="note-id" name="noteid" hidden>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Non, annuler</button>
            <button type="submit"  class="btn btn-primary shadow-2">Oui, changer la note suivante</button>
          </div>
        </form>
      </div>
     
    </div>
  </div>
</div>
