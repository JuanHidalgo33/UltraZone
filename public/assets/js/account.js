document.addEventListener('DOMContentLoaded', function(){
  const fields = ['fullname','username','email'];
  const fileInput = document.getElementById('profile_image');
  const btnEdit = document.getElementById('btn-edit');
  const btnCancel = document.getElementById('btn-cancel');
  const btnSave = document.getElementById('btn-save');

  function setDisabled(disabled){
    fields.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.disabled = disabled;
    });
    if (fileInput) fileInput.disabled = disabled;
    if (btnSave) btnSave.disabled = disabled;
    if (btnCancel) btnCancel.disabled = disabled;
  }

  // Asegurar deshabilitado al cargar
  setDisabled(true);

  if (btnEdit){
    btnEdit.addEventListener('click', function(e){
      e.preventDefault();
      setDisabled(false);
      const first = document.getElementById('fullname');
      if (first) first.focus();
    });
  }

  if (btnCancel){
    btnCancel.addEventListener('click', function(e){
      e.preventDefault();
      // Restablecer estado inicial recargando (restaura valores del servidor)
      window.location.href = 'MyAccount.php';
    });
  }

  // Popup de datos actualizados: se activa si la URL trae update=ok
  const popupUpdate = document.getElementById('popup-update');
  const btnAccept = document.getElementById('popup-update-accept');
  const params = new URLSearchParams(window.location.search);
  if (popupUpdate && params.get('update') === 'ok'){
    popupUpdate.style.display = 'flex';
  }
  if (btnAccept){
    btnAccept.addEventListener('click', function(){
      // Quitar el parámetro update=ok al cerrar
      window.location.href = 'MyAccount.php';
    });
  }
});
